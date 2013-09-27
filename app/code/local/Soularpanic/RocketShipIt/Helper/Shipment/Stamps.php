<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Stamps
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {

  public function addCustomsData($mageShipment, $rsiShipment) {
    Mage::log('Stamps shipment helper addCustomsData - start',
	      null, 'rocketshipit_shipments.log');

    $order = $mageShipment->getOrder();

    $customs = new \RocketShipIt\Customs('stamps');
    
    $weight = $order->getWeight();
    $customs->setParameter('customsWeight', $weight);

    $qty = $order->getCustomsQty();
    $customs->setParameter('customsQuantity', $qty);
    
    $value = $order->getCustomsValue();
    $customs->setParameter('customsValue', $value);

    $desc = $order->getCustomsDesc();
    $customs->setParameter('customsDescription', $desc);
    $rsiShipment->setParameter('customsOtherDescribe', $desc);

    $customs->setParameter('customsOriginCountry', 'US');
    $rsiShipment->setParameter('customsContentType', 'Other');
    
    $rsiShipment->addCustomsLineToShipment($customs);
    return $rsiShipment;
  }

  public function needsCustomsData($destAddr) {
    $countryCode = $destAddr->getCountryId();
    $regionCode = $destAddr->getRegionCode();
    return ($countryCode !== 'US' ||
	    $regionCode === 'AP' ||
	    $regionCode === 'AE' ||
	    $regionCode === 'AA');
  }

  public function getPackage($shipment) {
    $rateHelper = Mage::helper('rocketshipit/rates');
    $dataHelper = Mage::helper('rocketshipit/data');

    $destAddr = $shipment->getShippingAddress();
    $order = $shipment->getOrder();
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());

    $stampsRate = $rateHelper->getRSIRate('stamps', $destAddr);
    if ($this->needsCustomsData($destAddr)) {
      $stampsRate->setParameter('weightPounds', $order->getWeight());
      $stampsRate->setParameter('declaredValue', $order->getCustomsValue());
    }
    
    $stampsResp = $stampsRate->getAllRates();
    $stampsRates = $stampsResp->Rates->Rate;

    $serviceArr = $this->_parseStampsShippingMethod($shippingMethod);
    $serviceType = $serviceArr['serviceType'];
    $packageType = $serviceArr['packageType'];
    
    // $addOns = $this->_getAddOns($order->getHandlingCode(),
    // 				$serviceType,
    // 				$destAddr);
    $addOns = $this->_getAddOns($shipment,
				$order->getHandlingCode(),
				$serviceType);

    foreach ($stampsRates as $stampsRate) {
      if ($stampsRate->ServiceType === $serviceType
	  && $stampsRate->PackageType === $packageType) {
	$rsiPackage = $stampsRate;
	$rsiPackage->AddOns = $addOns;
	break;
      }
    }
    return $rsiPackage;
  }

  public function extractShippingLabel($shipmentResponse) {
    $labelUrlsStr = $shipmentResponse->URL;
    $labelUrls = explode(' ', $labelUrlsStr);
    $labelImages = $this->_fetchLabelImages($labelUrls);

    $labelPdf = $this->convertImagesToPdf($labelImages);
    $pdfStr = $labelPdf->render();

    return $pdfStr;
  }

  public function extractTrackingNo($shipmentResponse) {
    return $shipmentResponse->TrackingNumber;
  }

  public function extractRocketshipitId($shipmentResponse) {
    return $shipmentResponse->StampsTxID;
  }

  function _carrierRequiresDeliveryConfirmation($carrierCode) {
    return ($carrierCode != 'US-XM' &&
	    $carrierCode != 'US-PMI' &&
	    $carrierCode != 'US-EMI');
  }
  
  function _carrierAllowsDeliverySignature($carrierCode) {
    return !($carrierCode === 'US-PMI' ||
	     $carrierCode === 'US-FCI' ||
	     $carrierCode === 'US-EMI');
  }

  function _fetchLabelImages($labelUrls) {
    $labelResources = array();
    foreach ($labelUrls as $labelUrl) {
      $curlObj = curl_init();
      curl_setopt($curlObj, CURLOPT_URL, $labelUrl);
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 0);
      $labelStr = curl_exec($curlObj);
      $resource = imagecreatefromstring($labelStr);
      $labelResources[] = $resource;
      curl_close($curlObj);
    }
    return $labelResources;
  }

  public function getServiceType($shippingMethod) {
    $method = $this->_parseStampsShippingMethod($shippingMethod);
    return $method['serviceType'];
  }

  function _parseStampsShippingMethod($shippingMethod) {
    $serviceArr = explode(':', $shippingMethod['service']);
    $serviceType = $serviceArr[0];
    $packageType = str_replace('-', ' ', $serviceArr[1]);
    return array('serviceType' => $serviceType,
		 'packageType' => $packageType);
  }

  //function _getAddOns($handlingCode, $carrierCode, $destAddr) {
  function _getAddOns($shipment, $handlingCode, $carrierCode) {
    $destAddr = $shipment->getShippingAddress();
    $needDeliveryConfirmation = $this->_carrierRequiresDeliveryConfirmation($carrierCode);
  
    $addOns = array();

    if ($destAddr->getCountryId() === 'US') {
      $hiddenPostage = new \stdClass();
      $hiddenPostage->AddOnType = 'SC-A-HP';
      $addOns[] = $hiddenPostage;
    }

    if ($handlingCode === Soularpanic_RocketShipIt_Helper_Handling::SIGN_AND_INSURE) 
    {
      if (Mage::getStoreConfig('carrier/rocketshipit_global/insurance_use_carrier')) 
      {
	$insure = new \stdClass();
	$insure->AddOnType = 'SC-A-INS';
	$addOns[] = $insure;
      }

      $addOns = $this->_handleSignatureAddOn($addOns, $carrierCode, $destAddr);
      // $sign = new \stdClass();
      // $sign->AddOnType = 'US-A-SC';
      // $addOns[] = $sign;
    }
    elseif ($handlingCode === Soularpanic_RocketShipIt_Helper_Handling::SIGN) {
      $order = $shipment->getOrder();
      $addOns = $this->_handleSignatureAddOn($addOns, $carrierCode, $order);
    }
    else {
      if ($needDeliveryConfirmation) {
	$confirm = new \stdClass();
	$confirm->AddOnType = 'US-A-DC';
	$addOns[] = $confirm;
      }
      elseif ($carrierCode == 'US-XM') {
	$noSig = new \stdClass();
	$noSig->AddOnType = 'US-A-WDS';
	$addOns[] = $noSig;
      }
    }
    
    return $addOns;
  }

  function _handleSignatureAddOn($addOns, $carrierCode, $order) {
    $allowsDeliverySignature = $this->_carrierAllowsDeliverySignature($carrierCode);
    if ($allowsDeliverySignature) {
      $sign = new \stdClass();
      $sign->AddOnType = 'US-A-SC';
      $addOns[] = $sign;
    }
    else {
      $orderId = $order->getIncrementId();
      $session = Mage::getSingleton('adminhtml/session');
      $session->addWarning("Signature service was request for order $orderId, but is not available to the destination address.  It has not been added.");
    }
    return $addOns;
  }

}
?>
