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
			    'shipping_weight'=>'weight',
			    'subtotal'=>'subtotal',
			    'quantity'=>'total_qty_ordered'));
  }

  public function changeGridJSObject($observer) {
    $transport = $observer->getTransport();
    if ($observer->getEvent()->getBlock()->getRequest()->getControllerName() == 'sales_order'
	|| $observer->getEvent()->getBlock()->getRequest()->getControllerName() == 'adminhtml_sales_order'
        ) {
      $html = $transport->getHtml();
      $html = str_replace(
	array(
	  'sales_order_grid_massactionJsObject = new varienGridMassaction',
	  'sales_order_grid_massactionJsObject = new foomanGridMassaction',
	  'sales_order_gridJsObject = new varienGrid',
	  'sales_order_gridJsObject = new foomanGrid'
	),
	array(
	  'sales_order_grid_massactionJsObject = new soularpanicGridMassaction',
	  'sales_order_grid_massactionJsObject = new soularpanicGridMassaction',
	  'sales_order_gridJsObject = new soularpanicGrid',
	  'sales_order_gridJsObject = new soularpanicGrid'
	),
	$html
      );
      $transport->setHtml($html);
    }
  }
}
