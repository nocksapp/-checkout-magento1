<?php

class Nocks_NocksPaymentGateway_Model_Litecoin extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_litecoin';
	protected $_sourceCurrency = 'LTC';

	protected function getPaymentMethodData() {
		return [
			'method' => 'litecoin',
		];
	}
}
