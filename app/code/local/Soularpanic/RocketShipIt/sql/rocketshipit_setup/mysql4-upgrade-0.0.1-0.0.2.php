<?php 
$installer = $this;
$installer->startSetup();
$installer->run("
  ALTER TABLE `{$installer->getTable('sales/quote')}`
    ADD `customer_comment` text DEFAULT NULL,
    ADD `customer_vehicle_year` varchar(255) DEFAULT NULL,
    ADD `customer_vehicle_make` varchar(255) DEFAULT NULL,
    ADD `customer_vehicle_model` varchar(255) DEFAULT NULL;

  ALTER TABLE `{$installer->getTable('sales/order')}`
    ADD `customer_vehicle_year` varchar(255) DEFAULT NULL,
    ADD `customer_vehicle_make` varchar(255) DEFAULT NULL,
    ADD `customer_vehicle_model` varchar(255) DEFAULT NULL;
");
$installer->endSetup();

