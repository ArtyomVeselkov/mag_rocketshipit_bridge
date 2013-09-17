<?php 
abstract class Soularpanic_RocketShipIt_Helper_Shipment_Abstract 
extends Mage_Core_Helper_Abstract {

  abstract function getPackage($shipment);
  abstract function getServiceType($shippingMethod);
  abstract function addCustomsData($mageShipment, $rsiShipment);

  public function prepareShipment($shipment) {
    $order = $shipment->getOrder();
    $destAddr = $shipment->getShippingAddress();

    $dataHelper = Mage::helper('rocketshipit/data');
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());
    $carrier = $shippingMethod['carrier'];
    $serviceType = $this->getServiceType($shippingMethod);

    $rsiShipment = $this->asRSIShipment($carrier, $destAddr);

    $destCountry = $destAddr->getCountryId();
    if ($destCountry !== 'US') {
      $rsiShipment = $this->addCustomsData($shipment, $rsiShipment);
    }
        
    $rsiPackage = $this->getPackage($shipment);

    $rsiShipment->setParameter('service', $serviceType);

    $rsiShipment->addPackageToShipment($rsiPackage);
    return $rsiShipment;
  }

  public function asRSIShipment($carrierCode, Mage_Sales_Model_Order_Address $address) {
    $rsiShipment = new \RocketShipIt\Shipment($carrierCode);

    $toName = $address->getName();
    $rsiShipment->setParameter('toCompany', $toName);
    
    $toPhone = $address->getTelephone();
    $rsiShipment->setParameter('toPhone', $toPhone);

    $toStreet1 = $address->getStreet1();
    $rsiShipment->setParameter('toAddr1', $toStreet1);

    $toStreet2 = $address->getStreet2();
    $rsiShipment->setParameter('toAddr2', $toStreet2);

    $toStreet3 = $address->getStreet3();
    $rsiShipment->setParameter('toAddr3', $toStreet3);

    $toCity = $address->getCity();
    $rsiShipment->setParameter('toCity', $toCity);

    $toState = $address->getRegionCode();
    $rsiShipment->setParameter('toState', $toState);

    $toZip = $address->getPostcode();
    $rsiShipment->setParameter('toCode', $toZip);

    $toCountry = $address->getCountryId();
    $rsiShipment->setParameter('toCountry', $toCountry);

    return $rsiShipment;
  }

  function convertImagesToPdf($labelImages) {
    $labelPdf = new Zend_Pdf();

    foreach ($labelImages as $labelImage) {
      $x = imagesx($labelImage);
      $y = imagesy($labelImage);
      $page = new Zend_Pdf_Page($x, $y);
      $filename = Mage::getBaseDir('tmp').'/'.rand().'.png';
      imageinterlace($labelImage, 0);
      imagepng($labelImage, $filename);
      $pdfImg = Zend_Pdf_Image::imageWithPath($filename);
      $page->drawImage($pdfImg, 0, 0, $x, $y);
      unlink($filename);
      $labelPdf->pages[] = $page;
    }
    return $labelPdf;
  }
}
?>
