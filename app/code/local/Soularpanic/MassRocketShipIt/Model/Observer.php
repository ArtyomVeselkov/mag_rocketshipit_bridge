<?php

class Soularpanic_MassRocketShipIt_Model_Observer {
  public function salesOrderGridCollectionLoadBefore($observer) {
    Mage::log('before order grid loading observer firing',
	      null,
	      'rocketshipit_shipments.log');
    $collection = $observer->getOrderGridCollection();
    $select = $collection->getSelect();
    $select->joinLeft(array('flat_order'=>$collection->getTable('sales/order')),
		      'flat_order.entity_id=main_table.entity_id',
		      array('shipping_method'=>'shipping_method',
			    'shipping_weight'=>'weight'));
  }
}
