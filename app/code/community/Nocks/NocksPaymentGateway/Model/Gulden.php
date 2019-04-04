<?php

class Nocks_NocksPaymentGateway_Model_Gulden extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_gulden';
	protected $_sourceCurrency = 'NLG';

	protected function getPaymentMethodData() {
		return [
			'method' => 'gulden',
		];
	}
}
