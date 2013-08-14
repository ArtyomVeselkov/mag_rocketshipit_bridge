<?php
class Soularpanic_RocketShipIt_Model_Observer
{
  protected $_code = 'rocketshipit';

  public function foo(Varien_Event_Observer $observer)
  {
    Mage::log('rocketshipit observer firing',
	      null,
	      'rocketshipit_shipments.log');

    $rocketShipItPath = Mage::getStoreConfig('carriers/'.$this->_code.'/path');
    require_once($rocketShipItPath.'/RocketShipIt.php');
    
    $shipment = $observer->getEvent()->getShipment();

    $destAddr = $shipment->getShippingAddress();
    $rsiShipment = new RocketShipShipment('UPS');

    $toName = $destAddr->getName();
    $rsiShipment->setParameter('toCompany', $toName);
    
    $toPhone = $destAddr->getTelephone();
    $rsiShipment->setParameter('toPhone', $toPhone);

    $toStreet1 = $destAddr->getStreet1();
    $rsiShipment->setParameter('toAddr1', $toStreet1);

    $toStreet2 = $destAddr->getStreet2();
    $rsiShipment->setParameter('toAddr2', $toStreet2);

    $toStreet3 = $destAddr->getStreet3();
    $rsiShipment->setParameter('toAddr3', $toStreet3);

    $toCity = $destAddr->getCity();
    $rsiShipment->setParameter('toCity', $toCity);

    $toState = $destAddr->getRegionCode();
    $rsiShipment->setParameter('toState', $toState);

    $toZip = $destAddr->getPostcode();
    $rsiShipment->setParameter('toCode', $toZip);

    //$rsiShipment->setParameter('residentialAddressIndicator','0');

    $rsiPackage = new RocketShipPackage('UPS');
    $rsiPackage->setParameter('length','6');
    $rsiPackage->setParameter('width','6');
    $rsiPackage->setParameter('height','6');
    
    $weight = $shipment->getOrder()->getWeight();
    $rsiPackage->setParameter('weight', $weight);
    //$rsiPackage->setParameter('signatureType','2');
    
    $rsiShipment->addPackageToShipment($rsiPackage);
    $label = $rsiShipment->submitShipment();
    
    Mage::log('rocketshipit observer generated label: '.print_r($label,true),
	      null,
	      'rocketshipit_shipments.log');

    if(is_string($label) && strpos($label, 'Error') >= 0) {
      Mage::throwException($this->__('Label generation failed: '.$label));
    }

    $rsiTrackNo = $label['trk_main'];
    $track = Mage::getModel('sales/order_shipment_track');
    $track->setTitle('Dat Track');
    $track->setNumber($rsiTrackNo);
    $track->setOrderId($shipment->getOrderId());
    $track->setShipment($shipment);
    $track->setCarrierCode('jkcustom');
    $track->save();

                 /* ->setData('title', 'Dat Track') */
                 /* ->setData('number', $rsiTrackNo) */
                 /* ->setData('carrier_code', 'custom') */
                 /* ->setData('order_id', $shipment->getData('order_id')) */
                 /* ->save(); */

    /*
    $j = $observer->getEvent();
    $j->getShipment();
    $j->getOrder()->getAllItems();
    $observer->getEvent()->getShipment()->getOrder()->getId(); // => "9"
    $observer->getEvent()->getShipment()->getOrderId(); // =>"9"
    $observer->getEvent()->getShipment()->getOrder()->getWeight();
    $observer->getEvent()->getShipment()->getShippingAddress();
    $observer->getEvent()->getShipment()->getItemsCollection();
    $observer->getEvent()->getShipment()->getOrder()->getShippingMethod();
    $observer->getEvent()->getShipment()->getOrder()->getShippingDescription();
    */
  }
}
?>