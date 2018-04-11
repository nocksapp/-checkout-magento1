<?php

class Nocks_NocksPaymentGateway_Model_Adminhtml_System_Config_Source_Merchants {

	/**
	 * @return array
	 */
	public function toOptionArray() {
		// Fetch the merchants from nocks
		$nocks = new Nocks_NocksPaymentGateway_Api(Mage::getStoreConfig('payment/nockspaymentgateway/access_token'));

		$merchants = $nocks->getMerchants();

		$options = [];
		foreach ($merchants as $merchant) {
			$merchantName = $merchant['name'];
			foreach ($merchant['merchant_profiles']['data'] as $profile) {
				$label = ($merchantName === $profile['name'] ? $merchantName : $merchantName . ' (' . $profile['name'] . ')')
				         . ' (' . $merchant['coc'] . ')';

				$options[] = [
					'value' => $profile['uuid'],
					'label' => htmlentities($label, ENT_COMPAT, 'UTF-8'),
				];
			}
		}

		return $options;
	}
}
