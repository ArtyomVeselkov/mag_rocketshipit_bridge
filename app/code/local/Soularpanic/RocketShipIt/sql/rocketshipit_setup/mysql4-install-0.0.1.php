<?php 
$installer = $this;
$installer->startSetup();
$installer->run("
  CREATE TABLE `{$installer->getTable('rocketshipit/orderExtras')}` (
    `order_id` int(10) unsigned NOT NULL,
    `customs_desc` text DEFAULT NULL,
    `customs_qty` decimal(12,4) DEFAULT NULL,
    `customs_value` decimal(12,4) DEFAULT NULL,
    PRIMARY KEY (`order_id`),
    FOREIGN KEY (`order_id`)
      REFERENCES {$installer->getTable('sales/order')}(entity_id)
      ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
");
$installer->endSetup();
?>
