<?php 
abstract class Soularpanic_RocketShipIt_Helper_Shipment_Abstract 
extends Mage_Core_Helper_Abstract {

  const THERMAL = 'THERMAL/EPL';
  const PDF = 'PDF/GIF';

  abstract function getPackage($shipment);
  abstract function getServiceType($shippingMethod);
  abstract function addCustomsData($mageShipment, $rsiShipment);
  abstract function needsCustomsData($rsiShipment);
  abstract function setLabelFormat($rsiShipment);

  abstract function extractRocketshipitId($shipmentResponse);
  abstract function extractTrackingNo($shipmentResponse);
  abstract function extractShippingLabel($shipmentResponse);

  public function prepareShipment($shipment) {
    $order = $shipment->getOrder();
    $destAddr = $shipment->getShippingAddress();

    $dataHelper = Mage::helper('rocketshipit/data');
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());
    $carrier = $shippingMethod['carrier'];
    $serviceType = $this->getServiceType($shippingMethod);

    $rsiShipment = $this->asRSIShipment($carrier, $destAddr);

    if ($this->needsCustomsData($destAddr)) {
      $rsiShipment = $this->addCustomsData($shipment, $rsiShipment);
    }
        
    $rsiPackage = $this->getPackage($shipment);

    $rsiShipment->setParameter('service', $serviceType);

    $rsiShipment = $this->setLabelFormat($rsiShipment);

    $rsiShipment->addPackageToShipment($rsiPackage);
    return $rsiShipment;
  }

  public function asRSIShipment($carrierCode, Mage_Sales_Model_Order_Address $address) {
    $rsiShipment = new \RocketShipIt\Shipment($carrierCode);

    $rsiShipment->setParameter('toName', $address->getName());
    $toCompany = $address->getCompany();
    $rsiShipment->setParameter('toCompany', $toCompany);
    $residential = empty($toCompany) ? '1' : '0';
    $rsiShipment->setParameter('residentialAddressIndicator', $residential);

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

    if ($this->shouldSetState($address)) {
      $toState = $address->getRegionCode();
      $rsiShipment->setParameter('toState', $toState);
    }

    $toZip = $address->getPostcode();
    $rsiShipment->setParameter('toCode', $toZip);

    $toCountry = $address->getCountryId();
    $rsiShipment->setParameter('toCountry', $toCountry);

    return $rsiShipment;
  }

  public function shouldSetState(Mage_Sales_Model_Order_Address $address) {
    return true;
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

  function _getLabelFormat($carrierSubCode) {
    return Mage::getStoreConfig("carriers/rocketshipit_{$carrierSubCode}/label_format");
  }
}

