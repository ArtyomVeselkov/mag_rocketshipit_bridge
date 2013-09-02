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

    $rsiHelper = Mage::helper('rocketshipit');
    $useNegotiatedRate = $useNegotiatedRate = Mage::getStoreConfig('carriers/'.'rocketshipit_ups'.'/useNegotiatedRates');
    $rates = $rsiHelper->getSimpleRates('ups',
					$shippingAddr, 
					$useNegotiatedRate,
					$shippingWeight,
					0);
    
    $rowId = $order->getId();
    $col = $this->getColumn();
    $colId = $col->getName() ? $col->getName() : $col->getId();

    $html = '<select name="'.$colId.'-'.$rowId.'" rel="'.$rowId.'" class="'.$colId.'">';
    foreach($rates->getAllRates() as $rate) {
      $selected = ($rate->getCarrier().'_'.$rate->getMethod() == $shippingMethod) ? 'selected="selected" ' : '';
      $html.= '<option '.$selected.'value="'.$rate->getCarrier().'_'.$rate->getMethod().'" data-methodName="'.$rate->getMethodTitle().'" data-methodPrice="'.$rate->getCost().'">'.$rate->getMethodTitle().' -- '.$rate->getCost().'</option>';
    }
    $html.='</select>';
    return $html;
  }

  public function getFilter() {
    return false;
  }
}
