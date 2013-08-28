<?php

class Soularpanic_MassRocketShipIt_Model_Observer {

  public function changeGridJSObjects($observer) {
    Mage::log('rocketshipit changing grid JS... idk why',
	      null,
	      'rocketshipit_shipments.log');
    $transport = $observer->getTransport();
    if ($observer->getEvent()->getBlock()->getRequest()->getControllerName() == 'sales_order'
	|| $observer->getEvent()->getBlock()->getRequest()->getControllerName() == 'adminhtml_sales_order'
        ) {
      $html = $transport->getHtml();
      $html = str_replace(
			  array(
				'sales_order_grid_massactionJsObject = new varienGridMassaction',
				'sales_order_gridJsObject = new varienGrid'
				),
			  array(
				'sales_order_grid_massactionJsObject = new foomanGridMassaction',
				'sales_order_gridJsObject = new soularpanicGrid'
				),
			  $html
			  );
      $transport->setHtml($html);
    }
  }
}
