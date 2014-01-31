<?php

class Soularpanic_RocketShipIt_Model_Carrier_UPS
  extends Soularpanic_RocketShipIt_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{

  protected $_code = 'rocketshipit_ups';
  
  public function getCarrierSubCode()
  {
    return 'ups';
  }

  public function getMethods() {
    return array(
      '01' => 'UPS Next Day Air',
      '02' => 'UPS 2nd Day Air',
      '03' => 'UPS Ground',
      '07' => 'UPS Worldwide Express',
      '08' => 'UPS Worldwide Expedited',
      '11' => 'UPS Standard',
      '12' => 'UPS 3 Day Select',
      '13' => 'UPS Next Day Air Saver',
      '14' => 'UPS Next Day Air Early A.M.',
      '54' => 'UPS Worldwide Express Plus',
      '59' => 'UPS Second Day Air A.M.',
      '65' => 'UPS Worldwide Saver'
    );
  }
}

