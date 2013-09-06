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

  function buildShippingRequest($order) {
    $req = Mage::getModel('shipping/rate_request');
    $shippingAddr = $order->getShippingAddress(); //Mage_Sales_Model_Order_Address
    $shippingWeight = $order->getShippingWeight();
    $helper = Mage::helper('rocketshipit/data');
    $addr = $helper->_extractAddrFromMageSalesModelOrderAddress($shippingAddr);
    $req->setDestPostcode($addr['zip']);
    $req->setDestRegionCode($addr['state']);
    $req->setCountryId($addr['country']);
    $req->setPackageWeight($shippingWeight);
    return $req;
  }

  function buildSelect($order) {
    $mageShipping = Mage::getModel('shipping/shipping');
    $shippingReq = $this->buildShippingRequest($order);
    $allRates = $mageShipping->collectRates($shippingReq);

    $shippingMethod = $order->getShippingMethod();

    $rowId = $order->getId();
    $col = $this->getColumn();
    $colId = $col->getName() ? $col->getName() : $col->getId();

    $html = '<select name="'.$colId.'-'.$rowId.'" rel="'.$rowId.'" class="'.$colId.'">';
    $dataHelper = Mage::helper('rocketshipit/data');
    $rateHelper = Mage::helper('rocketshipit/rates');

    foreach($allRates->getResult()->getAllRates() as $rate) {
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
