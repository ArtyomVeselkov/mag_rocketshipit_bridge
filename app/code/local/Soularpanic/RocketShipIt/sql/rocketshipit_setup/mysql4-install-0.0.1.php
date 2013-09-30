<?php 
$installer = $this;
$installer->startSetup();
$installer->run("
  ALTER TABLE `{$installer->getTable('sales/quote_address')}`
    ADD `handling_amount` decimal(12,4) DEFAULT 0.0,
    ADD `handling_code` varchar(255) DEFAULT NULL;

  ALTER TABLE `{$installer->getTable('sales/order')}`
    ADD `handling_amount` decimal(12,4) DEFAULT 0.0,
    ADD `handling_code` varchar(255) DEFAULT NULL,
    ADD `customs_desc` text DEFAULT NULL,
    ADD `customs_qty` decimal(12,4) DEFAULT NULL,
    ADD `customs_value` decimal(12,4) DEFAULT NULL;

  ALTER TABLE `{$installer->getTable('sales/shipment')}`
    ADD `rocketshipit_id` varchar(255) DEFAULT NULL;
");
$installer->endSetup();
?>
