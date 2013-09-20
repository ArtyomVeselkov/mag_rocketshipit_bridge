<?php 
class Soularpanic_RocketShipIt_Model_Sales_Quote_Address_Total_Addons
extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    protected $_code = 'rocketshipit';

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    parent::collect($address);
    $this->_setAmount(0);
    $this->_setBaseAmount(0);
    
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
    $balance = $price - $currentAmount;
    $address->setHandlingAmount($price);

    $address->setGrandTotal($address->getGrandTotal() + $price);
    $address->setBaseGrandTotal($address->getBaseGrandTotal() + $price);

  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $handling = $address->getHandlingAmount();
    //$shipping = $address->getShippingAmount();
    $address->addTotal(array(
      'code'=>$this->getCode(),
      //'code'=>'shipping_amount',
      'title'=>'IDK',
      'value'=>$handling
    ));
    return $this;
  }

}

?>
