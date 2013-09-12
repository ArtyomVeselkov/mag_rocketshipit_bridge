<?php 
class Soularpanic_RocketShipIt_Model_Resource_OrderExtras
extends Mage_Core_Model_Resource_Db_Abstract{
  protected function _construct()
  {
    $this->_init('rocketshipit/orderExtras', 'order_id');
  }
}
?>
