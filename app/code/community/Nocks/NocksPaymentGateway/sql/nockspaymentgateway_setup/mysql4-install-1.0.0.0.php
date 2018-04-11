<?php

$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE `{$installer->getTable('sales/quote_payment')}` 
	ADD `nocks_transaction_id` VARCHAR( 255 ) DEFAULT NULL;
	  
	ALTER TABLE `{$installer->getTable('sales/order_payment')}` 
	ADD `nocks_transaction_id` VARCHAR( 255 ) DEFAULT NULL;
");

$installer->endSetup();