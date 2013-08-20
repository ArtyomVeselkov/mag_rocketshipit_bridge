<?php

class Soularpanic_RocketShipIt_Model_Carrier_Abstract
  extends Mage_Shipping_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    $result = Mage::getModel('shipping/rate_result');

    $method = Mage::getModel('shipping/rate_result_method');
    $method->setCarrier(getCarrierCode());
    $method->setCarrierTitle('Title '.getCarrierCode());
    $method->setMethod(1);
    $method->setMethodTitle('Method '.getCarrierCode());
    $method->setCost(100);
    $method->setPrice(101);
    $result->append($method);

    return $result;
  }

  public function getAllowedMethods() {
    return array('rocketshipit' => $this->getConfigData('name'));
  }

  abstract public function getCarrierCode();
}
?>