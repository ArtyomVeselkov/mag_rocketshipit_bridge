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
    $req->setDestCountryId($addr['country']);
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

    $methodClass = "{$colId}_method";
    $html = "<select name=\"{$colId}-{$rowId}\" rel=\"$rowId\" class=\"$methodClass\">";
    $dataHelper = Mage::helper('rocketshipit/data');
    $rateHelper = Mage::helper('rocketshipit/rates');

    foreach($allRates->getResult()->getAllRates() as $rate) {
      $selected = ($rate->getCarrier().'_'.$rate->getMethod() == $shippingMethod) ? 'selected="selected" ' : '';
      $html.= '<option '.$selected.'value="'.$rate->getCarrier().'_'.$rate->getMethod().'" data-methodName="'.$rate->getMethodTitle().'" data-methodPrice="'.$rate->getCost().'">'.$rate->getMethodTitle().' -- '.$rate->getCost().'</option>';
    }


    $html.='</select>';

    // Signature and Insurance options
    $addOnClass = "{$colId}_addons";
    $addOnName = 'shippingAddons-'.$colId.'-'.$rowId;
    $addOnTemplate = "<input type='radio' rel='$rowId' name='{$addOnName}' value='%s'>%s</input>";
    
    $addOnModes = array(
      'none' => 'None',
      'sign' => 'Sign',
      'signAndInsure' => 'Insure &amp; Sign'
    );
    
    $html.= "<div>";
    foreach ($addOnModes as $val => $display) {
      $html.=sprintf($addOnTemplate, $val, $display);
    }
    $html.= "</div>";
    
    $destCountry = $order->getShippingAddress()->getCountryId();
    if ($destCountry !== "US") {
      $customsTemplate = "<div>";
      $customsTemplate.= "<label for=\"%s\">%s</label>";
      $customsTemplate.= "<input rel=\"$rowId\" name=\"%s\" class=\"%s\" value=\"%s\"/>";
      $customsTemplate.= "</div>";
      $customsIdBase = "{$colId}_customs_";
      $customsData = array(
	'value'       => array(
	  'id'    => "{$customsIdBase}value",
	  'label' => "Customs Value",
	  'value' => $order->getSubtotal()
	),
	'quantity'    => array(
	  'id'    => "{$customsIdBase}quantity",
	  'label' => "Customs Quantity",
	  'value' => $order->getQuantity()
	),
	'description' => array(
	  'id'    => "{$customsIdBase}description",
	  'label' => "Customs Description",
	  'value' => "Auto Lamps"
	)
      );


      foreach ($customsData as $index => $data) {
	$html.= sprintf($customsTemplate, 
			$data['id'],    // label for
			$data['label'], // label text
			$data['id'],    // input name
			$data['id'],    // input class
			$data['value']);// input value
      }
    }
    
    return $html;
  }

  public function getFilter() {
    return false;
  }
}
