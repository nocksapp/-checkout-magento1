<?php

class Nocks_NocksPaymentGateway_Model_Ethereum extends Nocks_NocksPaymentGateway_Model_Abstract
{
	protected $_code  = 'nocks_ethereum';
	protected $_sourceCurrency = 'ETH';

	protected function getPaymentMethodData() {
		return [
			'method' => 'ethereum',
		];
	}
}
