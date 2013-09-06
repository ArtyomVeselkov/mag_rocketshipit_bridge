<?php

abstract class Soularpanic_RocketShipIt_Model_Carrier_Abstract
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_superCode = 'rocketshipit';

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    if(!Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/active')) {
      return false;
    }

    $carrierCode = $this->getCarrierSubCode();
    $useNegotiatedRate = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/useNegotiatedRates');
    $handling = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/handling');

    $helper = Mage::helper('rocketshipit/rates');

    $simpleRates = $helper->getSimpleRates($carrierCode,
					   $request,
					   $useNegotiatedRate,
					   null,
					   $handling);
    
    return $simpleRates;
  }

  public function getAllowedMethods() {
    return array('rocketshipit' => $this->getConfigData('name'));
  }

  public function getFullCarrierCode()
  {
    return $this->_superCode.'_'.$this->getCarrierSubCode();
  }

  abstract public function getCarrierSubCode();
}
?>
