<?php 
$installer = $this;
$installer->startSetup();
// $installer->run("
//   CREATE TABLE `{$installer->getTable('rocketshipit/orderExtras')}` (
//     `order_id` int(10) unsigned NOT NULL,    
//     `customs_desc` text DEFAULT NULL,
//     `customs_qty` decimal(12,4) DEFAULT NULL,
//     `customs_value` decimal(12,4) DEFAULT NULL,
//     PRIMARY KEY (`order_id`),
//     FOREIGN KEY (`order_id`)
//       REFERENCES {$installer->getTable('sales/order')}(entity_id)
//       ON DELETE CASCADE
//   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
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
");
$installer->endSetup();
?>
