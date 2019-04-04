<?php

class Nocks_NocksPaymentGateway_Model_Balance extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_balance';

	protected function getPaymentMethodData() {
		return [
			'method' => 'balance',
		];
	}
}
