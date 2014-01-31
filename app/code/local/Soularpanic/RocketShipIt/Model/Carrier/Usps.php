<?php
class Soularpanic_RocketShipIt_Model_Carrier_USPS
  extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{
  public function getCarrierSubCode()
  {
    return 'usps';
  }
}

