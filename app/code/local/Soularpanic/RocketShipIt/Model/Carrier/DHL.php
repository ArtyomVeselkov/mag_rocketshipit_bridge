<?php 
class Soularpanic_RocketShipIt_Model_Carrier_DHL
extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface {

  protected $_code = 'rocketshipit_dhl';

  public function getCarrierSubCode() {
    return 'dhl';
  }
}
