<?php

class Soularpanic_RocketShipIt_Model_Carrier_Stamps
  extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{
  public function getCarrierSubCode()
  {
    return 'stamps';
  }
}
?>
