<?php

class Nocks_NocksPaymentGateway_Model_Sepa extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_sepa';
	protected $_sourceCurrency = 'EUR';

	protected function getPaymentMethodData() {
		return [
			'method' => 'sepa',
		];
	}
}
