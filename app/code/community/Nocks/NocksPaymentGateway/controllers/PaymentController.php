<?php


class Nocks_NocksPaymentGateway_PaymentController extends Mage_Core_Controller_Front_Action
{
	protected $nocks;

	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
		parent::__construct($request, $response, $invokeArgs);

		$accessToken = Mage::getStoreConfig('payment/nockspaymentgateway/access_token');
		$testMode = Mage::getStoreConfig('payment/nockspaymentgateway/testmode') === '1';

		$this->nocks = new Nocks_NocksPaymentGateway_Api($accessToken, $testMode);
	}

	/**
	 * Handle Nocks redirect
	 */
	public function redirectAction()
	{
		$params = $this->getRequest()->getParams();

		if (!isset($params['payment_id'])) {
			Mage::getSingleton('core/session')->addNotice($this->__('Invalid return from Nocks.'));
			return $this->_redirect('checkout/cart', ['_secure' => true]);
		}

		// Get the order
		/** @var Mage_Sales_Model_Quote_Payment $payment */
		$payment = Mage::getModel('sales/quote_payment')->load($params['payment_id']);

		/** @var Mage_Sales_Model_Quote $quote */
		$quote = Mage::getModel('sales/quote')->load($payment->getQuoteId());
		$order = Mage::getModel('sales/order')->load($quote->getReseveredOrderId());

		if (!$order) {
			Mage::getSingleton('core/session')->addNotice($this->__('Order not found'));
			return $this->_redirect('checkout/cart', ['_secure' => true]);
		}

		$session = Mage::getSingleton('checkout/session');
		$session->setQuoteId($quote->getId());

		// Because we can't rely on the Nocks callback is called before the redirect url,
		// we need to fetch the transaction from Nocks to check the status.
		// We don't change the order state here, this is always done in the Callback.
		$transaction = $this->nocks->getTransaction($payment->getNocksTransactionId());
		if ($transaction['status'] === 'completed' || $transaction['status'] === 'open') {
			try {
				// Redirect to success
				$quote->setIsActive(false)->save();
				$session->unsQuoteId();

				if ($transaction['status'] === 'open') {
					$msg = 'We have not received a definite payment status. Depending on the payment method, it may take a while until we receive the payment';
					Mage::getSingleton('core/session')->addNotice($this->__($msg));
				}

				return $this->_redirect('checkout/onepage/success', ['_secure' => true]);
			} catch (\Exception $e) {
				Mage::getSingleton('core/session')->addError($this->__('Something went wrong'));
				return $this->_redirect('checkout/cart', ['_secure' => true]);
			}
		}

		$quote->setIsActive(true)->save();

		if ($transaction['status'] === 'cancelled') {
			Mage::getSingleton('core/session')->addNotice($this->__('Payment cancelled, please try again.'));
		} else {
			Mage::getSingleton('core/session')->addError($this->__('Something went wrong'));
		}

		return $this->_redirect('checkout/cart', ['_secure' => true]);
	}

	/**
	 * Hanlde Nocks callback
	 */
	public function callbackAction()
	{
		// Get the transaction id from the request body
		$transactionId = file_get_contents('php://input');
		$this->getResponse()->setHeader('Content-type', 'application/json', true);

		if ($transactionId) {
			// Get the transaction
			$transaction = $this->nocks->getTransaction($transactionId);
			$metadata = $transaction['metadata'];

			if (isset($metadata['payment_id']) && !empty($metadata['payment_id'])) {
				// Get the order
				/** @var Mage_Sales_Model_Quote_Payment $payment */
				$payment = Mage::getModel('sales/quote_payment')->load($metadata['payment_id']);
				/** @var Mage_Sales_Model_Quote $quote */
				$quote = Mage::getModel('sales/quote')->load($payment->getQuoteId());
				/** @var Mage_Sales_Model_Order $order */
				$order = Mage::getModel('sales/order')->loadByIncrementId($quote->getReservedOrderId());

				if ($order) {
					// Change the order state
					// $order->setPayment($payment);

					if ($transaction['status'] === 'completed') {
						if ($order->canInvoice()) {
							// Create order invoice
							$service = Mage::getModel('sales/service_order', $order);
							$invoice = $service->prepareInvoice();
							$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::STATE_PAID);
							$invoice->register();
							$invoice->pay();
							$invoice->save();

							$transactionSave = Mage::getModel('core/resource_transaction')
							                       ->addObject($invoice)
							                       ->addObject($invoice->getOrder());
							$transactionSave->save();

							// Send invoice mail
							if ($invoice && !$invoice->getEmailSent()) {
								$invoice->setEmailSent(true)->sendEmail()->save();
							}
						}

						$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
						$order->save();
					} else if ($transaction['status'] === 'cancelled') {
						$order->cancel();
						$order->save();
					}

					return $this->getResponse()->setBody(json_encode(['success' => true]));
				}
			}

		}

		return $this->getResponse()->setBody(json_encode(['success' => false]));
	}
}