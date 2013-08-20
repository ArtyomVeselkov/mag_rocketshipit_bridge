<?php

/**
 * based on tutorial at http://www.magentocommerce.com/wiki/5_-_modules_and_development/shipping/create-shipping-method-module
 */
class Soularpanic_RocketShipIt_Model_Carrier_ShippingMethod
  extends Mage_Shipping_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{
  /**
   * unique internal shipping method id
   */
  protected $_code = 'rocketshipit';

  public function isTrackingAvailable() {
    return false;
  }

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    // skip if not enabled
    if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
      return false;
    }

    /**
     * Calculate shipping rates using combo of external service and data from request
     * Ex: Mage_Usa_Model_Shipping_Carrier_Ups::setRequest()
     */

    // get necessary config values
    /* $rocketShipItPath = Mage::getStoreConfig('carriers/'.$this->_code.'/path'); */
    /* require_once($rocketShipItPath.'/RocketShipIt.php'); */
    
    $helper = Mage::helper('rocketshipit');

    $handling = Mage::getStoreConfig('carriers/'.$this->_code.'/handling');

    // results container to be returned with all shipping rates of this module
    $result = Mage::getModel('shipping/rate_result');

    //$rate = new \RocketShipIt\Rate('UPS');
    $rate = new RocketShipRate('UPS');

    /* $rate = $helper->populateRsiAddress('UPS', $request, $rate); */
    $destZip = $request->getDestPostcode();
    $rate->setParameter('toCode', $destZip);

    $destState = $request->getDestRegionCode();
    $rate->setParameter('toState', $destState);

    $destCountry = $request->getCountryId();
    $rate->setParameter('toCountry', $destCountry);
    
    $packageWeight = $request->getPackageWeight();
    $rate->setParameter('weight', $packageWeight);

    $rate->setParameter('residentialAddressIndicator','0');

    $response = $rate->getSimpleRates();

    foreach ($response as $rMethod) {
      $method = Mage::getModel('shipping/rate_result_method');

      $method->setCarrier($this->_code);
      $method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
      
      $method->setMethod($rMethod['serviceCode']);
      $method->setMethodTitle($rMethod['desc']);

      $method->setCost($rMethod['rate']);

      $method->setPrice($rMethod['rate'] + $handling);

      $result->append($method);
    }

    return $result;
  }

  public function getAllowedMethods() {
    $allowedMethods = array($this->_code => $this->getConfigData('name'));
    return $allowedMethods;
  }
}