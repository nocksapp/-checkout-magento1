<?php

class Nocks_NocksPaymentGateway_Model_Ideal extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_ideal';
	protected $_sourceCurrency = 'EUR';
	protected $_formBlockType = 'nockspaymentgateway/payment_ideal';

	protected function getPaymentMethodData() {
		$issuer = Mage::app()->getRequest()->getParam('nocks_ideal_issuer');

		return [
			'method' => 'ideal',
			'metadata' => [
				'issuer' => $issuer,
			]
		];
	}
}
