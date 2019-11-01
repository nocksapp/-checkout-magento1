<?php

class Nocks_NocksPaymentGateway_Model_Bitcoin extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_bitcoin';
	protected $_sourceCurrency = 'BTC';

	protected function getPaymentMethodData() {
		return [
			'method' => 'bitcoin',
		];
	}
}
