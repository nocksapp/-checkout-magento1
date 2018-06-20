<?php

class Nocks_NocksPaymentGateway_Model_Adminhtml_System_Config_Source_Merchants {

	/**
	 * @return array
	 */
	public function toOptionArray() {
		// Fetch the merchants from nocks
		$accessToken = Mage::getStoreConfig('payment/nockspaymentgateway/access_token');
		$testMode = Mage::getStoreConfig('payment/nockspaymentgateway/testmode') === '1';
		$nocks = new Nocks_NocksPaymentGateway_Api($accessToken, $testMode);

		$merchants = $nocks->getMerchants();

		return Nocks_NocksPaymentGateway_Util::getMerchantsOptions($merchants);
	}
}
