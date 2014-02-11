<?php 
$installer = $this;
$installer->startSetup();
$installer->run("
  ALTER TABLE `{$installer->getTable('sales/shipment')}`
    ADD `shipping_label_format` varchar(32) DEFAULT NULL,
    ADD `shipping_label_customs` mediumblob;

  UPDATE `{$installer->getTable('sales/shipment')}`
    SET `shipping_label_format` = 'PDF/GIF'
    WHERE `shipping_label_format` is null;
");
$installer->endSetup();
