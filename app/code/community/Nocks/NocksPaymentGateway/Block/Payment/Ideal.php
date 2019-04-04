<?php

class Nocks_NocksPaymentGateway_Block_Payment_Ideal extends Mage_Payment_Block_Form
{
	/**
	 * @var Nocks_NocksPaymentGateway_Api
	 */
	private $nocks;
	private $cacheKey;

	public function _construct()
	{
		parent::_construct();
		$this->setTemplate('nocks/nockspaymentgateway/payment/ideal.phtml');

		$accessToken = Mage::getStoreConfig('payment/nockspaymentgateway/access_token');
		$testMode = Mage::getStoreConfig('payment/nockspaymentgateway/testmode') === '1';

		$this->nocks = new Nocks_NocksPaymentGateway_Api($accessToken, $testMode);
		$this->cacheKey = 'nocks_ideal_issuers_' . ($testMode ? 'test' : 'live');
	}

	/**
	 * @return array
	 */
	public function getIssuers() {
		$issuersFromCache = Mage::app()->loadCache($this->cacheKey);

		if (!$issuersFromCache) {
			$issuers = $this->nocks->getIdealIssuers();
			Mage::app()->saveCache(json_encode($issuers), $this->cacheKey, [], 60 * 60 * 24);

			return $issuers;
		}

		return json_decode($issuersFromCache, true);
	}
}
