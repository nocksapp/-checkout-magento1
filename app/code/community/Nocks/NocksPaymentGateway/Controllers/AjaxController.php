<?php


class Nocks_NocksPaymentGateway_AjaxController extends Mage_Core_Controller_Front_Action {

	private $requiredScopes = ['merchant.read', 'transaction.create', 'transaction.read'];

	/**
	 * Validate accessToken
	 *
	 * @return Zend_Controller_Response_Abstract
	 */
	public function validateAccessTokenAction() {
		$params = $this->getRequest()->getParams();

		$testMode = $params['testMode'] === '1' ? true : false;
		$nocks = new Nocks_NocksPaymentGateway_Api($params['accessToken'], $testMode);
		$scopes = $nocks->getTokenScopes();

		$requiredAccessTokenScopes = array_filter($scopes, function($scope) {
			return in_array($scope, $this->requiredScopes);
		});

		$this->getResponse()->setHeader('Content-type', 'application/json', true);
		return $this->getResponse()->setBody(json_encode(['valid' => sizeof($requiredAccessTokenScopes) === sizeof($this->requiredScopes)]));
	}

	/**
	 * Get merchants
	 *
	 * @return Zend_Controller_Response_Abstract
	 */
	public function merchantsAction() {
		$this->getResponse()->setHeader('Content-type', 'application/json', true);

		$params = $this->getRequest()->getParams();

		$testMode = $params['testMode'] === '1' ? true : false;
		$nocks = new Nocks_NocksPaymentGateway_Api($params['accessToken'], $testMode);
		$merchants = $nocks->getMerchants();

		return $this->getResponse()->setBody(json_encode(['merchants' => Nocks_NocksPaymentGateway_Util::getMerchantsOptions($merchants)]));
	}
}