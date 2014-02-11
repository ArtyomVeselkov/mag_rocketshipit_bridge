<?php 
class Soularpanic_MassRocketShipIt_Sales_ShippingPrintController 
extends Mage_Adminhtml_Controller_Action {

  const UPS_SLIPS = 'UPS_PACKING_SLIPS';
  const USPS_SLIPS = 'USPS_PACKING_SLIPS';
  const UPS_LABELS = 'UPS_SHIPPING_LABELS';
  const USPS_LABELS = 'USPS_SHIPPING_LABELS';
  const UPS_CUSTOMS = 'UPS_CUSTOMS_INVOICES';

  public function batchlabelsAction() {
    $orderIds = $this->getRequest()->getPost('order_ids');

    $documents = array();
    if (!empty($orderIds)) {
      foreach ($orderIds as $orderId) {
	$order = Mage::getModel('sales/order')->load($orderId);
	$documents = $this->_sortAllDocuments($documents, $order);
      }

      if ($documents) {
	$this->_prepareDocumentsForDownload($documents);
      } else {
	$this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
	$this->_redirect('*/*');
      }
    }
    $this->_redirect('*/*');
  }

  function _sortAllDocuments($documents, $order) {
    $shipments = $order->getShipmentsCollection();
    if (!$shipments->getSize()) {
      return $documents;
    }
    
    $shippingMethod = Mage::helper('rocketshipit')
		   ->parseShippingMethod($order->getShippingMethod());
    $carrier = strtoupper($shippingMethod['carrier']);
    
    $keys = $this->_getKeys($carrier);
    $slipKey = $keys['slip'];
    $labelKey = $keys['label'];
    
    
    $documents[$slipKey] = $this->_addPackingSlips($documents[$slipKey], $order);

    //$documents[$labelKey] = $this->_handleLabels($documents[$labelKey], $order);
    $tmpPdfLabels = array();
    $tmpEplLabels = array();
    foreach ($shipments as $shipment) {
      $shipmentFormat = $shipment->getShippingLabelFormat();
      if ($shipmentFormat === 
	  Soularpanic_RocketShipIt_Helper_Shipment_Abstract::THERMAL) {
	//$labelObj = unserialize($shipment->getShippingLabel());
	$this->_printEplLabel($shipment);

      }
      elseif ($shipmentFormat === 
	      Soularpanic_RocketShipIt_Helper_Shipment_Abstract::PDF) {
	$labels = $tmpPdfLabels[$labelKey];
	$shippingLabel = Zend_Pdf::parse($shipment->getShippingLabel());
	if (!isset($labels)) {
	  $tmpPdfLabels[$labelKey] = $shippingLabel;
	}
	else {
	  $labels->pages = array_merge($labels->pages, $shippingLabel->pages);
	}
      }
      $customsInvoiceData = $shipment->getShippingLabelCustoms();
      if ($customsInvoiceData) {
	$customsInvoice = Zend_Pdf::parse($customsInvoiceData);
	$labels = $tmpPdfLabels[$labelKey];
	if (!isset($labels)) {
	  $tmpPdfLabels[$labelKey] = $customsInvoice;
	}
	else {
	  $labels->pages = array_merge($labels->pages, $customsInvoice->pages);
	}
      }
    }
    foreach ($tmpPdfLabels as $tmpPdfLabelKey => $tmpPdfLabelValue) {
      if (!$documents[$tmpPdfLabelKey]) {
	$documents[$tmpPdfLabelKey] = new Zend_Pdf();
      }
      foreach ($tmpPdfLabelValue->pages as $page) {
	$documents[$tmpPdfLabelKey]->pages[] = new Zend_Pdf_Page(clone $page);
      }
    } 

    return $documents;
  }

  function _handleLabels($currentLabels, $order) {
    $shipments = $order->getShipmentsCollection();
    $pdfAccum = null;
    foreach ($shipments as $shipment) {
      $shipmentFormat = $shipment->getShippingLabelFormat();
      if ($shipmentFormat ===
	  Soularpanic_RocketShipIt_Helper_Shipment_Abstract::THERMAL) {
	$this->_printEplLabel($shipment);
      }
      /* elseif ($shipmentFormat ===
      Soularpanic_RocketShipIt_Helper_Shipment_Abstract::PDF) {
      $pdfAccum = $this->_addPdfLabels($pdfAccum, $shipment);
      } */
      $pdfAccum = $this->_addPdfLabels($pdfAccum, $shipment);
    }

    $mergedLabels = $this->_pdfCopyMerge($currentLabels, $pdfAccum);
    /* $mergedLabels = $currentLabels;
    if (!isset($mergedLabels)) {
    $mergedLabels = $pdfAccum;
    }
    else {
    $mergedLabels->pages = array_merge($mergedLabels->pages, $pdfAccum->pages);
    } */
    return $mergedLabels;
  }

  function _printEplLabel($shipment) {
    $label = unserialize($shipment->getShippingLabel());
    $printer = Mage::helper('rocketshipit/print');
    $printerUrl = $printer->getThermalUrl();
    foreach ($label as $page) {
      $resp = $printer->printThermal($page, $printerUrl);
      Mage::log("Thermal print response for shipment {$shipment->getId()}: $resp"
		,null, 'rsi_thermal.log');
    }
  }

  function _pdfCopyMerge($currentLabels, $addlLabels) {
    $mergedLabels = $currentLabels;
    if ($addlLabels) {
      if (!$mergedLabels) {
	$mergedLabels = new Zend_Pdf();
      }
      $extractor = new Zend_Pdf_Resource_Extractor();
      foreach ($addlLabels->pages as $page) {
	//$clonedPage = new Zend_Pdf_Page($page);
	//$clonedPage = Zend_Pdf::newPage($page);
	//$clonedPage = $extractor->clonePage($page);
	$clonedPage = clone $page;
	$mergedLabels->pages[] = $clonedPage;
      }
    }
    return $mergedLabels;
  }

  function _addPdfLabels($currentLabels, $shipment) {
    $shippingLabelData = $shipment->getShippingLabel();
    $customsInvoiceData = $shipment->getShippingLabelCustoms();
    
    $mergedLabels = $currentLabels;
    if ($shippingLabelData) {
      $label = Zend_Pdf::parse($shippingLabelData);
      if (!isset($mergedLabels)) {
	$mergedLabels = $label;
      }
      else {
	$mergedLabels->pages = array_merge($mergedLabels->pages, $label->pages);
      }
    }

    if ($customsInvoiceData) {
      $customs = Zend_Pdf::parse($customsInvoiceData);
      if (!isset($mergedLabels)) {
	$mergedLabels = $customs;
      }
      else {
	$mergedLabels->pages = array_merge($mergedLabels->pages, $customs->pages);
      }
    }
    
    return $mergedLabels;
  }

  function _addPackingSlips($currentSlips, $order) {
    $shipments = $order->getShipmentsCollection();
    $addlSlips = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);

    if (!isset($currentSlips)) {
      $mergedSlips = $addlSlips;
    }
    else {
      $mergedSlips = $currentSlips;
      $mergedSlips->pages = array_merge($mergedSlips->pages, $addlSlips->pages);
    }

    return $mergedSlips;
  }

  function _prepareDocumentsForDownload($documents) {
    $dateStr = Mage::getSingleton('core/date')->date('Y-m-d_H-i-s');
    $archiveFileName = 'shipping_docs_'.$dateStr.'.zip';
    $archivePath = Mage::getBaseDir('tmp').'/'.$archiveFileName;

    $this->_prepareArchive($archivePath, $documents);

    $this->_prepareDownloadResponse($archiveFileName,
				    array('type' => 'filename',
					  'value' => $archivePath
					  ));
  }

  function _getKeys($carrier) {
    if ($carrier === 'STAMPS') {
      $slipKey = self::USPS_SLIPS;
      $labelKey = self::USPS_LABELS;
    }
    elseif ($carrier === 'UPS') {
      $slipKey = self::UPS_SLIPS;
      $labelKey = self::UPS_LABELS;
    }
    else {
      Mage::throwException("Order {$order->getIncrementId()} had an invalid carrier code ({$carrier}) associated with it.");
    }
    return array('slip' => $slipKey,
		 'label' => $labelKey);
  }

  function _prepareArchive($path, $documents) {
    $archive = new ZipArchive;
    if ($archive->open($path, ZIPARCHIVE::CREATE) !== TRUE) {
      Mage::throwException("Failed to create zip archive on disk.");
    }

    $dateStr = Mage::getSingleton('core/date')->date('Y-m-d_H-i-s');
    if ($documents[self::USPS_SLIPS]) {
      $archive->addFromString("packslips_USPS_{$dateStr}.pdf",
			      $documents[self::USPS_SLIPS]->render());
    }
    if ($documents[self::UPS_SLIPS]) {
      $archive->addFromString("packslips_UPS_{$dateStr}.pdf",
			      $documents[self::UPS_SLIPS]->render());
    }
    if ($documents[self::USPS_LABELS]) {
      $archive->addFromString("shiplabels_USPS_{$dateStr}.pdf",
			      $documents[self::USPS_LABELS]->render());
    }
    if ($documents[self::UPS_LABELS]) {
      $archive->addFromString("shiplabels_UPS_{$dateStr}.pdf",
			      $documents[self::UPS_LABELS]->render());
    }
    $archive->close(); 
  }

}
