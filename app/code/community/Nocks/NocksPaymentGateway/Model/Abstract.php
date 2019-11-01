<?php

abstract class Nocks_NocksPaymentGateway_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{
	protected $_sourceCurrency = null;

	protected abstract function getPaymentMethodData();

	/**
	 * Check method for processing with base currency
	 *
	 * @param string $currencyCode
	 * @return boolean
	 */
	public function canUseForCurrency($currencyCode)
	{
		return in_array($currencyCode, ['EUR']);
	}

	public function getOrderPlaceRedirectUrl()
	{
		$accessToken = Mage::getStoreConfig('payment/nockspaymentgateway/access_token');
		$testMode = Mage::getStoreConfig('payment/nockspaymentgateway/testmode') === '1';
		$merchant = Mage::getStoreConfig('payment/nockspaymentgateway/merchant');

		/** @var Mage_Sales_Model_Quote_Payment $payment */
		$payment = $this->getInfoInstance();
		/** @var Mage_Sales_Model_Quote $quote */
		$quote = $payment->getQuote();
		$order = Mage::getModel('sales/order')->loadByIncrementId($quote->getReservedOrderId());

		$redirectUrl = Mage::getUrl('nockspaymentgateway/payment/redirect', ['_secure' => true, 'payment_id' => $payment->getId()]);
		$callbackUrl = Mage::getUrl('nockspaymentgateway/payment/callback', ['_secure' => true]);

		// Create the Nocks transactions
		$nocks = new Nocks_NocksPaymentGateway_Api($accessToken, $testMode);
		$transactionData = [
			'merchant_profile' => $merchant,
			'amount' => [
				'amount' => strval($quote->getGrandTotal()),
				'currency' => $quote->getQuoteCurrencyCode(),
			],
			'redirect_url' => $redirectUrl,
			'callback_url' => $callbackUrl,
			'payment_method' => $this->getPaymentMethodData(),
			'metadata' => [
				'payment_id' => $payment->getId(),
				'nocks_plugin' => 'magento1:1.3.0'
			],
			'description' => $order->getRealOrderId() . ' - ' . $order->getStore()->getFrontendName(),
		];

		if ($this->_sourceCurrency) {
			$transactionData['source_currency'] = $this->_sourceCurrency;
		}

		$response = $nocks->createTransaction($transactionData);

		if ($response) {
			// Save transaction id by order
			$payment->setNocksTransactionId($response['data']['uuid'])->save();
			$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();

			// Redirect to payment
			return $response['data']['payments']['data'][0]['metadata']['url'];
		}

		// Something went wrong
		$session = Mage::getSingleton('core/session');
		$session->addError(Mage::helper('core')->__('Something went wrong'));

		$quote->setIsActive(true)->save();

		return Mage::helper('checkout/cart')->getCartUrl();
	}
}