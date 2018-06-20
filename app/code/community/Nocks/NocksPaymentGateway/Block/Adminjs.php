<?php

class Nocks_NocksPaymentGateway_Block_Adminjs extends Mage_Adminhtml_Block_Template {

	protected function _toHtml() {
		$section = $this->getAction()->getRequest()->getParam('section', false);
		if ($section == 'payment') {
			return parent::_toHtml();
		} else {
			return '';
		}
	}
}
