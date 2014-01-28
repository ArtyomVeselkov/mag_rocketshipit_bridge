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
		      array('shipping_method' => 'shipping_method',
			    'shipping_weight' => 'weight',
			    'subtotal'        => 'subtotal',
			    'quantity'        => 'total_qty_ordered',
			    'handling_amount' => 'handling_amount',
			    'handling_code'   => 'handling_code',
			    'customs_desc'    => 'customs_desc',
			    'customs_qty'     => 'customs_qty',
			    'customs_value'   => 'customs_value'));
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

  public function addMassButtons($observer) {
    $block = $observer->getEvent()->getBlock();
    if (
      $block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction
      || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
      || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_sales_orderManager_Grid_Massaction
    ) {
      $secure = Mage::app()->getStore()->isCurrentlySecure() ? 'true' : 'false';
      $controllerName = $block->getRequest()->getControllerName();
      if($controllerName =='sales_order' ||
         $controllerName =='adminhtml_sales_order') {
        $block->addItem('massrocketshipit_batchlabels', array(
          'label'=> 'Print Packing Slips & Shipping Labels',
          'url'  => Mage::helper('adminhtml')->getUrl('adminhtml/sales_shippingPrint/batchlabels',$secure ? array('_secure'=>1) : array()),
        ));
      }
    }
  }

}
