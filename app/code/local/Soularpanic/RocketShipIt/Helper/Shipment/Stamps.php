<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Stamps
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {

  const SUB_CODE = 'stamps';

  protected $_labelMap;

  function __construct() {
    $this->_labelMap = array (
      array (
	self::LOCAL_FORMAT => 'Epl'
	,self::DB_FORMAT => self::THERMAL
	,self::EXTRACTOR => '_extractEplLabel'
      )
      ,array (
	self::LOCAL_FORMAT => 'Gif'
	,self::DB_FORMAT => self::PDF
	,self::EXTRACTOR => '_extractGifLabel'
      )
    );
  }

  public function getPackage($shipment) {
    $rateHelper = Mage::helper('rocketshipit/rates');
    $dataHelper = Mage::helper('rocketshipit/data');

    $destAddr = $shipment->getShippingAddress();
    $order = $shipment->getOrder();
    $shippingMethod = $dataHelper->parseShippingMethod($order->getShippingMethod());

    $stampsRate = $rateHelper->getRSIRate('stamps', $destAddr);
    $stampsRate->setParameter('weightPounds', $order->getWeight());

    if ($this->needsCustomsData($destAddr)) {
      $stampsRate->setParameter('declaredValue', $order->getCustomsValue());
    }
    
    $stampsResp = $stampsRate->getAllRates();
    $stampsRates = $stampsResp->Rates->Rate;

    $serviceArr = $this->_parseStampsShippingMethod($shippingMethod);
    $serviceType = $serviceArr['serviceType'];
    $packageType = $serviceArr['packageType'];
    
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


  public function getServiceType($shippingMethod) {
    $method = $this->_parseStampsShippingMethod($shippingMethod);
    return $method['serviceType'];
  }


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


  public function setLabelFormat($rsiShipment) {
    $format = $this->_getLabelFormat(self::SUB_CODE);
    $rsiShipment->setParameter('imageType', $format);
    return $rsiShipment;
  }


  public function extractRocketshipitId($shipmentResponse) {
    return $shipmentResponse->StampsTxID;
  }


  public function extractTrackingNo($shipmentResponse) {
    return $shipmentResponse->TrackingNumber;
  }


  public function extractShippingLabel($shipmentResponse) {
    $labelUrlsStr = $shipmentResponse->URL;
    $labelUrls = explode(' ', $labelUrlsStr);

    $localFormat = $this->_getLabelFormat(self::SUB_CODE);
    $dataHelper = Mage::helper('rocketshipit');
    $map = $dataHelper->fetchMapEntry(self::LOCAL_FORMAT, $localFormat, $this->_labelMap);
    

    $labelStr = call_user_func(array($this, $map[self::EXTRACTOR]), $labelUrls);

    return array(self::LABEL_FORMAT => $map[self::DB_FORMAT],
		 self::LABEL_DATA => $labelStr);
  }


  function _extractEplLabel($urlArr) {
    $data = $this->_fetchLabelData($urlArr);
    return serialize($data);
  }


  function _extractGifLabel($urlArr) {
    $data = $this->_fetchLabelData($urlArr);
    $images = array();
    foreach ($data as $gifStr) {
      $images[] = imagecreatefromstring($gifStr);
    }
    $labelPdf = $this->convertImagesToPdf($images);
    $labelStr = $labelPdf->render();
    return $labelStr;
  }


  function _carrierRequiresDeliveryConfirmation($carrierCode, $countryCode) {
    $fciDeliveryConfirmCountryCodes = array('AU' //Australia
					    ,'BE' //Belgium
					    ,'BR' //Brazil
					    ,'CA' //Canada
					    ,'HR' //Croatia
					    ,'DK' //Denmark
					    ,'FR' //France
					    ,'DE' //Germany
					    ,'GB' //UK
					    ,'IL' //Israel
					    ,'NL' //Netherlands
					    ,'NZ' //New Zealand
					    ,'ES' //Spain
					    ,'CH' //Switzerland
					    );

    if ($carrierCode === 'US-FCI') {
      return in_array($countryCode, $fciDeliveryConfirmCountryCodes);
    }

    return ($carrierCode != 'US-XM' &&
	    $carrierCode != 'US-PMI' &&
	    $carrierCode != 'US-EMI');
  }

  
  function _carrierAllowsDeliverySignature($carrierCode) {
    return !($carrierCode === 'US-PMI' ||
	     $carrierCode === 'US-FCI' ||
	     $carrierCode === 'US-EMI');
  }


  function _fetchLabelData($labelUrls) {
    $data = array();
    foreach ($labelUrls as $labelUrl) {
      $curlObj = curl_init();
      curl_setopt($curlObj, CURLOPT_URL, $labelUrl);
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 0);
      $labelStr = curl_exec($curlObj);
      $data[] = $labelStr;
      curl_close($curlObj);
    }
    return $data;
  }


  function _parseStampsShippingMethod($shippingMethod) {
    $serviceArr = explode(':', $shippingMethod['service']);
    $serviceType = $serviceArr[0];
    $packageType = str_replace('-', ' ', $serviceArr[1]);
    return array('serviceType' => $serviceType,
		 'packageType' => $packageType);
  }


  function _getAddOns($shipment, $handlingCode, $carrierCode) {
    $destAddr = $shipment->getShippingAddress();
    $needDeliveryConfirmation = $this->_carrierRequiresDeliveryConfirmation($carrierCode, $destAddr->getCountryId());
    
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
    }
    
    return $addOns;
  }


  function _handleSignatureAddOn($addOns, $carrierCode, $order) {
    $allowsDeliverySignature = $this->_carrierAllowsDeliverySignature($carrierCode);
    if ($carrierCode == 'US-XM') {
      $xmSign = new \stdClass();
      $xmSign->AddOnType = 'US-A-SR';
      $addOns[] = $xmSign;
    }
    elseif ($allowsDeliverySignature) {
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

