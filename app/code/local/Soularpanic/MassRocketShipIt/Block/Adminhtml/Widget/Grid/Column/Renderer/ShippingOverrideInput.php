<?php

class Soularpanic_MassRocketShipIt_Block_Adminhtml_Widget_Grid_Column_Renderer_ShippingOverrideInput
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select {
  
  public function render(Varien_Object $row) { 
    $html = '';
    $html.= $this->buildStatusIcons($row);
    if ($row->canShip()) {
      $html.= $this->buildInputCell($row);
    }
    else {
      $html.= $this->buildInfoCell($row);
    }
    return $html;
  }

  function buildStatusIcons($order) {
    $icons = '<div class="statusIconsContainer">';
    if ($this->_isPotentiallyFraud($order)) {
      $icons.= $this->_makeIcon('fraud');
    }
    if ($this->_isFreeShipping($order)) {
      $icons.= $this->_makeIcon('freeShipping');
    }
    if ($this->_isInternational($order)) {
      $icons.= $this->_makeIcon('international');
    }
    if ($this->_isDuplicateShippingAddresses($order)) {
      $icons.= $this->_makeIcon('duplicateAddress');
    }
    $icons.= '</div>';
    return $icons;
  }

  function _makeIcon($name) {
    return sprintf("<span class=\"%sIcon statusIcon\">&nbsp;</span>", $name);
  }

  function _isDuplicateShippingAddresses($order) {
    $addrCount = $order->getAddressCount();
    return $addrCount > 1;
  }

  function _isInternational($order) {
    $addr = $order->getShippingAddress();
    return ($addr->getCountryId() !== 'US');
  }

  function _isFreeShipping($order) {
    $amount = $order->getShippingAmount();
    if ($amount !== null && (int)$amount === 0) {
      return true;
    }
    return false;
  }

  function _isPotentiallyFraud($order) {
    if ($this->_addressesMatch($order) 
	&& $this->_paymentChecksOut($order)) {
      return false;
    }
    return true;
  }

  function _addressesMatch($order) {
    $ship = $order->getShippingAddress()->format('text');
    $bill = $order->getBillingAddress()->format('text');
    return (strcasecmp($ship, $bill) === 0);
  }

  function _paymentChecksOut($order) {
    $raw_payment_info = $order->getPaymentInfo();
    if ($raw_payment_info) {
      $payment_info = unserialize($raw_payment_info);
    }
    if (!$payment_info) {
      return true;
    }
    
    $avs = $payment_info['paypal_avs_code'];
    $okAvsCodes = array('X', 'Y', 'D', 'M');
    if ($avs && !in_array($avs, $okAvsCodes)) {
      return false;
    }

    $cvv2 = $payment_info['paypal_cvv2_match'];
    if ($cvv2 && $cvv2 !== 'M') {
      return false;
    }

    return true;
  }
	    
  function buildInfoCell($order) {
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

  function buildInputCell($order) {
    $rowId = $order->getId();
    $col = $this->getColumn();
    $colId = $col->getName() ? $col->getName() : $col->getId();

    $html = $this->_buildRateSelect($order, $colId, $rowId);

    $html.=$this->_buildServicesRadio($order, $colId, $rowId);
    
    $destCountry = $order->getShippingAddress()->getCountryId();
    if ($destCountry !== "US") {
      $html.=$this->_buildCustomsInputs($order, /*$orderExtras,*/ $colId, $rowId);
    }
    
    return $html;
  }

  function _buildRateSelect($order, $colId, $rowId) {
    $mageShipping = Mage::getModel('shipping/shipping');
    $shippingReq = $this->buildShippingRequest($order);
    $allRates = $mageShipping->collectRates($shippingReq);

    $shippingMethod = $order->getShippingMethod();


    $methodClass = "{$colId}_method";
    $html = "<select name=\"{$colId}-{$rowId}\" rel=\"$rowId\" class=\"$methodClass orderGridShippingMethodSelect\">";
    $dataHelper = Mage::helper('rocketshipit/data');
    $rateHelper = Mage::helper('rocketshipit/rates');

    foreach($allRates->getResult()->getAllRates() as $rate) {
      $selected = ($rate->getCarrier().'_'.$rate->getMethod() == $shippingMethod) ? 'selected="selected" ' : '';
      $html.= '<option '.$selected.'value="'.$rate->getCarrier().'_'.$rate->getMethod().'" data-methodName="'.$rate->getMethodTitle().'" data-methodPrice="'.$rate->getCost().'">'.$rate->getMethodTitle().' -- '.$rate->getCost().'</option>';
    }

    $html.='</select>';
    return $html;
  }

  function _buildServicesRadio($order, $colId, $rowId) {
    $html = '';

    // Signature and Insurance options
    $addOnClass = "{$colId}_addons";
    $addOnName = 'shippingAddons-'.$colId.'-'.$rowId;
    $addOnTemplate = "<input type='radio' rel='$rowId' class='{$addOnClass}' name='{$addOnName}' %s value='%s'>%s</input>";
    
    $checkedStr = 'checked="checked"';
    $addOnModes = array(
      'none'          => array('display' => 'None',
			       'checked' => $checkedStr),
      'sign'          => array('display' => 'Sign',
			       'checked' => ''),
      'signAndInsure' => array('display' => 'Insure &amp; Sign',
			       'checked' => '')
    );
    
    $carrierServices = $order->getHandlingCode();
    if (!empty($carrierServices)) {
      $addOnModes['none']['checked'] = '';
      $addOnModes[$carrierServices]['checked'] = $checkedStr;
    }

    $html.= "<div>";

    foreach ($addOnModes as $key => $vals) {
      $html.=sprintf($addOnTemplate, $vals['checked'], $key, $vals['display']);
    }
    $html.= "</div>";
    return $html;
  }

  function _buildCustomsInputs($order, /*$orderExtras,*/ $colId, $rowId) {
    $html = '';
    $customsTemplate = "<div>";
    $customsTemplate.= "<label for=\"%s\">%s</label>";
    $customsTemplate.= "<input rel=\"$rowId\" name=\"%s\" class=\"%s\" value=\"%s\"/>";
    $customsTemplate.= "</div>";
    $customsIdBase = "{$colId}_customs_";

    $customsVal = $order->getCustomsValue();
    $customsQty = $order->getCustomsQty();
    $customsDesc = $order->getCustomsDesc();

    $customsData = array(
      'value'       => array(
	'id'    => "{$customsIdBase}value",
	'label' => "Customs Value",
	'value' => empty($customsVal) ? $order->getSubtotal() : $customsVal
      ),
      'quantity'    => array(
	'id'    => "{$customsIdBase}quantity",
	'label' => "Customs Quantity",
	'value' => empty($customsQty) ? $order->getQuantity() : $customsQty 
      ),
      'description' => array(
	'id'    => "{$customsIdBase}description",
	'label' => "Customs Description",
	'value' => empty($customsDesc) ? "Auto Lamps" : $customsDesc
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
    return $html;
  }

  public function getFilter() {
    return false;
  }
}
