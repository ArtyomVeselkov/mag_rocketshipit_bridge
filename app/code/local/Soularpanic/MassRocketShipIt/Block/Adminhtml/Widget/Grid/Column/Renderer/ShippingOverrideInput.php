<?php

class Soularpanic_MassRocketShipIt_Block_Adminhtml_Widget_Grid_Column_Renderer_ShippingOverrideInput
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select {
  
  public function render(Varien_Object $row) { 
    $html = '';
    if ($row->canShip()) {
      $html = $this->buildSelect($row);
    }
    else {
      $html = $this->buildInfo($row);
    }
    return $html;
  }

  function buildInfo($order) {
    $collection = Mage::getModel('sales/order_shipment_track')
		   ->getCollection()
		   ->addAttributeToSelect('title')
		   ->setOrderFilter($order->getId());
    $carriers = array();
    foreach ($collection as $track) {
      $carriers[] = $track->getTitle();
    }
    $html = implode(' ,', $carriers);
    return $html;
  }

  function buildSelect($order) {
    $shippingMethod = $order->getShippingMethod();
    $shippingAddr = $order->getShippingAddress(); //Mage_Sales_Model_Order_Address
    $shippingWeight = $order->getShippingWeight();

    $rowId = $order->getId();
    $col = $this->getColumn();
    $colId = $col->getName() ? $col->getName() : $col->getId();

    $html = '<select name="'.$colId.'-'.$rowId.'" rel="'.$rowId.'" class="'.$colId.'">';
    $rsiHelper = Mage::helper('rocketshipit');
    $carriers = Mage::getStoreConfig('carriers');
    foreach ($carriers as $carrier => $carrierConfig) {
      if (strpos($carrier, 'rocketshipit_') === 0 
	  && Mage::getStoreConfig('carriers/'.$carrier.'/active')) {

	$useNegotiatedRate = $useNegotiatedRate = Mage::getStoreConfig('carriers/'.$carrier.'/useNegotiatedRates');
	$shippingMethod = $rsiHelper->parseShippingMethod($carrier);
	$rates = $rsiHelper->getSimpleRates($shippingMethod['carrier'],
					    $shippingAddr, 
					    $useNegotiatedRate,
					    $shippingWeight,
					    0);
	

	foreach($rates->getAllRates() as $rate) {
	  $selected = ($rate->getCarrier().'_'.$rate->getMethod() == $shippingMethod) ? 'selected="selected" ' : '';
	  $html.= '<option '.$selected.'value="'.$rate->getCarrier().'_'.$rate->getMethod().'" data-methodName="'.$rate->getMethodTitle().'" data-methodPrice="'.$rate->getCost().'">'.$rate->getMethodTitle().' -- '.$rate->getCost().'</option>';
	}
      }
    }
    $html.='</select>';
    return $html;
  }

  public function getFilter() {
    return false;
  }
}
