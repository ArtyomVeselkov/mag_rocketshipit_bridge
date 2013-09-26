<?php 
class Soularpanic_RocketShipIt_Model_Sales_Quote_Address_Total_ShippingAndHandling
extends Mage_Sales_Model_Quote_Address_Total_Shipping {

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    parent::collect($address);
    
    $items = $this->_getAddressItems($address);
    if (!count($items)) {
      return $this; //this makes only address type shipping to come through
    }


    $addOnCode = $address->getHandlingCode();
    $price = 0.0;
    if ($addOnCode === 'sign') { $price = 5.0; }
    elseif ($addOnCode === 'signAndInsure') { $price = 7.5; }

    $quote = $address->getQuote();
    
    $currentAmount = $address->getHandlingAmount();
    $this->_setAmount($address->getShippingAmount() + $price);
    $this->_setBaseAmount($address->getBaseShippingAmount() + $price);
    $address->setHandlingAmount($price);
  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $handling = $address->getHandlingAmount();

    $amount = $address->getShippingAmount();
    if ($amount != 0 || $address->getShippingDescription()) {
      $title = Mage::helper('sales')->__('Shipping & Handling');
      if ($address->getShippingDescription()) {
	$title .= ' (' . $address->getShippingDescription() . ')';
      }
      $address->addTotal(array(
	'code' => $this->getCode(),/*'shipping',*/
	'title' => $title,
	'value' => $amount + $handling
      ));
    }

    return $this;
  }

}

?>
