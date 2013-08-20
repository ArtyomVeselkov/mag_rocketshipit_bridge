<?php

class Soularpanic_RocketShipIt_Model_Carrier_USPS
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  public function getCarrierCode()
  {
    return 'usps';
  }

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    $result = Mage::getModel('shipping/rate_result');

    $method = Mage::getModel('shipping/rate_result_method');
    $method->setCarrier($this->getCarrierCode());
    $method->setCarrierTitle('Title '.$this->getCarrierCode());
    $method->setMethod(1);
    $method->setMethodTitle('Method '.$this->getCarrierCode());
    $method->setCost(100);
    $method->setPrice(101);
    $result->append($method);

    return $result;
  }

  public function getAllowedMethods() {
    return array('rocketshipit_usps' => $this->getConfigData('name'));
  }
}
?>