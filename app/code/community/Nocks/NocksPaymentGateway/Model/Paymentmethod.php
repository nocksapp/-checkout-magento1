<?php

class Nocks_NocksPaymentGateway_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract
{
	protected $_code  = 'nockspaymentgateway';

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
		$quote = $payment->getQuote();
		/** @var Mage_Sales_Model_Quote $quote */
		$order = Mage::getModel('sales/order')->loadByIncrementId($quote->getReservedOrderId());

		$redirectUrl = Mage::getUrl('nockspaymentgateway/payment/redirect', ['_secure' => true, 'payment_id' => $payment->getId()]);
		$callbackUrl = Mage::getUrl('nockspaymentgateway/payment/callback', ['_secure' => true]);

		// Create the Nocks transactions
		$nocks = new Nocks_NocksPaymentGateway_Api($accessToken, $testMode);
		$response = $nocks->createTransaction([
			'merchant_profile' => $merchant,
			'source_currency' => 'NLG',
			'amount' => [
				'amount' => strval($quote->getGrandTotal()),
				'currency' => $quote->getQuoteCurrencyCode(),
			],
			'redirect_url' => $redirectUrl,
			'callback_url' => $callbackUrl,
			'metadata' => [
				'payment_id' => $payment->getId(),
			],
		]);

		if ($response) {
			// Save transaction id by order
			$payment->setNocksTransactionId($response['data']['uuid'])->save();
//			$order->setPayment($payment)->save();

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