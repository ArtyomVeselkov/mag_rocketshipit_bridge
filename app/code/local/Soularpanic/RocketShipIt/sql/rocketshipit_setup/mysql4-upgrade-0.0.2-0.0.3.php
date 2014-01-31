<?php 
$installer = $this;
$installer->startSetup();
$installer->run("
  ALTER TABLE `{$installer->getTable('sales/order_status_history')}`
    ADD `comment_type` TINYINT UNSIGNED DEFAULT 0;

  UPDATE `{$installer->getTable('sales/order_status_history')}` as `comment`,
         `{$installer->getTable('sales/quote')}` as `quote`
    SET `comment`.`comment_type` = 2
    WHERE `comment`.`comment` = `quote`.`customer_comment`;
");
$installer->endSetup();

