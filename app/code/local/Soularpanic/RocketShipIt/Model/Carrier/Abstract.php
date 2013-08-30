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

    $helper = Mage::helper($this->_superCode);

    $simpleRates = $helper->getSimpleRates($carrierCode,
					   $request,
					   $useNegotiatedRate,
					   null,
					   $handling);
					   
    return $simpleRates;

    /* $rsiRate = $helper->getRSIRate($this->getCarrierSubCode(), */
    /* 				   $request); */
    /* $response = $rsiRate->getSimpleRates(); */

    /* $result = Mage::getModel('shipping/rate_result'); */

    /* $errorMsg = $response['error']; */
    /* if ($errorMsg != null) { */
    /*   $error = Mage::getModel('shipping/rate_result_error'); */
    /*   $error->addData(array('error_message' => $errorMsg)); */
    /*   $result->append($error); */
    /*   return $result; */
    /* } */
    
    /* $carrierCode = $this->getCarrierSubCode(); */
    /* $carrierName = Mage::getStoreConfig('carriers/'.$carrierCode.'/title'); */
    /* $useNegotiatedRate = Mage::getStoreConfig('carriers/'.$this->getFullCarrierCode().'/useNegotiatedRates'); */
    /* $rateKey = $useNegotiatedRate ? 'negotiated_rate' : 'rate'; */

    /* foreach($response as $rsiMethod) { */
    /*   if($useNegotiatedRate && $rsiMethod['negotiated_rate'] == null) { */
    /* 	continue; */
    /*   } */

    /*   $method = Mage::getModel('shipping/rate_result_method'); */

    /*   $method->setCarrier($carrierCode); */
    /*   $method->setCarrierTitle($carrierName); */

    /*   $method->setMethod($rsiMethod['service_code']); */
    /*   $method->setMethodTitle($rsiMethod['desc']); */

    /*   $method->setCost($rsiMethod[$rateKey]); */
    /*   $method->setPrice($rsiMethod[$rateKey] + $handling); */

    /*   $result->append($method); */
    /* } */

    /* return $result; */
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