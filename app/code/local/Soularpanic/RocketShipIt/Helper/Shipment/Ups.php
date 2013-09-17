<?php 
class Soularpanic_RocketShipIt_Helper_Shipment_Ups
extends Soularpanic_RocketShipIt_Helper_Shipment_Abstract {

  public function addCustomsData($mageShipment, $rsiShipment) {
    Mage::log('UPS shipment helper addCustomsData - start',
	      null, 'rocketshipit_shipments.log');
    $order = $mageShipment->getOrder();
    $orderExtras = Mage::getModel('rocketshipit/orderExtras')->load($order->getId());
    $shippingAddr = $order->getShippingAddress();
    $billingAddr = $order->getBillingAddress();

    $rsiShipment->setParameter('toAttentionName', $shippingAddr->getName());
    $rsiShipment->setParameter('invoice', $order->getIncrementId());

    $invoiceDate = $this->_formatCustomsDate($order->getCreatedAt());
    $rsiShipment->setParameter('invoiceDate', $invoiceDate);
    if ($this->_shouldAddMonetaryValue($shippingAddr)) {
      $rsiShipment->setParameter('monetaryValue', intval($orderExtras->getCustomsValue()));
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

    // $i = 1;
    // foreach ($mageShipment->getAllItems() as $orderItem) {
    //   $lineItem = new \RocketShipIt\Customs('ups');

    //   $lineItem->setParameter('invoiceLineNumber', $i);
    //   $partNo = substr($orderItem->getSku(), 0, 10);
    //   $lineItem->setParameter('invoiceLinePartNumber', $partNo);
    //   $lineItem->setParameter('invoiceLineDescription', $orderItem->getName());
    //   $value = ($order->getPrice()) * ($order->getQty());
    //   $lineItem->setParameter('invoiceLineValue', $value);
    //   $lineItem->setParameter('invoiceLineOriginCountryCode', 'CN');
      
    //   $rsiShipment->addCustomsLineToShipment($lineItem);
    //   $i++;
    // }

    $lineItem = new \RocketShipIt\Customs('ups');
    //$lineItem->setParameter('invoiceLineNumber', $orderExtras->getCustomsQty());
    $lineItem->setParameter('invoiceLineNumber', '1');
    //$partNo = substr($orderItem->getSku(), 0, 10);
    $lineItem->setParameter('invoiceLinePartNumber', '2');
    $lineItem->setParameter('invoiceLineDescription', $orderExtras->getCustomsDesc());
    //$unitVal = $orderExtras->getCustomsValue() / $orderExtras->getCustomsQty();
    $lineItem->setParameter('invoiceLineValue', $orderExtras->getCustomsValue());
    $lineItem->setParameter('invoiceLineOriginCountryCode', 'CN');
    
    $rsiShipment->addCustomsLineToShipment($lineItem);

    return $rsiShipment;
  }

  function _shouldAddMonetaryValue($shippingAddress) {
    $country = $shippingAddress->getCountryId();
    return ($country === 'CA'
	    || $country === 'PR');
  }

  function _formatCustomsDate($dateStr) {
    return date('Ymd', strtotime($dateStr));
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
    // $rsiTrackNo = $shipmentResponse['trk_main'];
    $labelImg = $shipmentResponse['pkgs'][0]['label_img'];
    $labelResources = array();
    foreach ($shipmentResponse['pkgs'] as $package) {
      $labelResources[] = imagecreatefromstring(base64_decode($package['label_img']));
    }
    $labelPdf = $this->convertImagesToPdf($labelResources);
    //$labelImgDecoded = base64_decode($labelImg);
    $customsDocs = $shipmentResponse['shipping_docs'];
    if ($customsDocs) {
      $customsPdf = Zend_Pdf::parse(base64_decode($customsDocs));
      foreach ($customsPdf->pages as $customsPage) {
	$labelPdf->pages[] = clone $customsPage;
      }
    }
      //$customsDocsDecoded = base64_decode($customsDocs);
      //return $labelImgDecoded;
      
      $pdfStr = $labelPdf->render();
      return $pdfStr;
  }

  public function getServiceType($shippingMethod) {
    return $shippingMethod['service'];
  }

}
?>
