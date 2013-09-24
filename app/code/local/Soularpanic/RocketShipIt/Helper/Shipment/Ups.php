<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Ups
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {

  public function prepareShipment($shipment) {
    $rsiShipment = parent::prepareShipment($shipment);
    $rsiShipment = $this->_handleDiacritics($rsiShipment);
    return $rsiShipment;
  }

  public function asRSIShipment($carrierCode, Mage_Sales_Model_Order_Address $address) {
    $rsiShipment = parent::asRSIShipment($carrierCode, $address);
    if (empty($rsiShipment->toCompany)) {
      $rsiShipment->setParameter('toCompany', $address->getName());
    }
    return $rsiShipment;
  }

  public function addCustomsData($mageShipment, $rsiShipment) {
    Mage::log('UPS shipment helper addCustomsData - start',
	      null, 'rocketshipit_shipments.log');
    $order = $mageShipment->getOrder();
    //$orderExtras = Mage::getModel('rocketshipit/orderExtras')->load($order->getId());
    $shippingAddr = $order->getShippingAddress();
    $billingAddr = $order->getBillingAddress();

    $rsiShipment->setParameter('toAttentionName', $shippingAddr->getName());
    $rsiShipment->setParameter('invoice', $order->getIncrementId());

    $invoiceDate = $this->_formatCustomsDate($order->getCreatedAt());
    $rsiShipment->setParameter('invoiceDate', $invoiceDate);
    if ($this->_shouldAddMonetaryValue($shippingAddr)) {
      $rsiShipment->setParameter('monetaryValue', intval($order->getCustomsValue()));
    }
    


    $rsiShipment->setParameter('soldCompany', $billingAddr->getCompany());
    $rsiShipment->setParameter('soldName', $billingAddr->getName());
    $rsiShipment->setParameter('soldTaxId', $billingAddr->getVatId());
    $rsiShipment->setParameter('soldPhone', $billingAddr->getTelephone());
    $rsiShipment->setParameter('soldAddr1', $billingAddr->getStreet(1));
    $rsiShipment->setParameter('soldAddr2', $billingAddr->getStreet(2));
    $rsiShipment->setParameter('soldCity', $billingAddr->getCity());
    $rsiShipment->setParameter('soldState', $billingAddr->getRegionCode());
    $rsiShipment->setParameter('soldCode', $billingAddr->getPostcode());
    $rsiShipment->setParameter('soldCountry', $billingAddr->getCountryId());

    $lineItem = new \RocketShipIt\Customs('ups');
    $lineItem->setParameter('invoiceLineNumber', '1');
    $lineItem->setParameter('invoiceLinePartNumber', '2');
    $lineItem->setParameter('invoiceLineDescription', $order->getCustomsDesc());
    $lineItem->setParameter('invoiceLineValue', $order->getCustomsValue());
    $lineItem->setParameter('invoiceLineOriginCountryCode', 'CN');
    
    $rsiShipment->addCustomsLineToShipment($lineItem);

    return $rsiShipment;
  }

  public function extractTrackingNo($shipmentResponse) {
    return $shipmentResponse['trk_main'];
  }

  public function extractRocketshipitId($shipmentResponse) {
    return $shipmentResponse['trk_main'];
  }

  public function getPackage($shipment) {
    $rsiPackage = new \RocketShipIt\Package('ups');
    $rsiPackage->setParameter('length','6');
    $rsiPackage->setParameter('width','6');
    $rsiPackage->setParameter('height','6');
    
    $weight = $shipment->getOrder()->getWeight();
    $rsiPackage->setParameter('weight', $weight);

    return $rsiPackage;
  }

  public function extractShippingLabel($shipmentResponse) {
    $labelImg = $shipmentResponse['pkgs'][0]['label_img'];
    $labelResources = array();
    foreach ($shipmentResponse['pkgs'] as $package) {
      $labelResources[] = imagecreatefromstring(base64_decode($package['label_img']));
    }
    $labelPdf = $this->convertImagesToPdf($labelResources);
    $customsDocs = $shipmentResponse['shipping_docs'];
    if ($customsDocs) {
      $customsPdf = Zend_Pdf::parse(base64_decode($customsDocs));
      foreach ($customsPdf->pages as $customsPage) {
	$labelPdf->pages[] = clone $customsPage;
      }
    }
    
    $pdfStr = $labelPdf->render();
    return $pdfStr;
  }

  public function shouldSetState(Mage_Sales_Model_Order_Address $address) {
    $country = $address->getCountry();
    return ($country === 'US' ||
	    $country === 'CA' ||
	    $country === 'AU');
  }

  public function getServiceType($shippingMethod) {
    return $shippingMethod['service'];
  }

  function _handleDiacritics($rsiShipment) {
    $unsupported = array('Č', 'č', 'Ř', 'ř', 'Š', 'š', 'Ž', 'ž', 'Ć', 'ć',// czech
			 'Œ', 'œ', 'Ÿ', // french
			 'İ', 'ı', 'Ğ', 'ğ', 'Ş', 'ş', // turkish
			 'Ĳ', 'ĳ' // dutch
			 );
    $workaround = array('Ch', 'ch', 'Rzh', 'rzh', 'Sh', 'sh', 'Zh', 'zh', 'C', 'c',
			'OE', 'oe', 'Y',
			'I', 'i', 'G', 'g', 'S', 's',
			'IJ', 'ij'
			);
    $maskArr = array(0x80, 0x10ffff, 0, 0xffffff);
    foreach ($rsiShipment->parameters as $key=>$val) {
      if (is_null($val) || $val === '') {
	continue;
      }
      $entityVal = str_replace($unsupported, $workaround, $val);
      $entityVal = mb_encode_numericentity($entityVal, $maskArr, 'UTF-8');
      $rsiShipment->setParameter($key, $entityVal);
    }
    return $rsiShipment;
  }

  public function needsCustomsData($destAddr) {
    return ($destAddr->getCountryId() !== 'US');
  }

  function _shouldAddMonetaryValue($shippingAddress) {
    $country = $shippingAddress->getCountryId();
    return ($country === 'CA'
	    || $country === 'PR');
  }

  function _formatCustomsDate($dateStr) {
    return date('Ymd', strtotime($dateStr));
  }


}
?>
