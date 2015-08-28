<?php
class Soularpanic_RocketShipIt_Helper_Rates
    extends Mage_Core_Helper_Abstract {

    const RATE_CACHE_NAME = 'rocketshipit';
    const RATE_CACHE_KEY = 'ROCKETSHIPIT_RATES';

    public function getRSIRate($courier, $addrObj) {
        $helper = Mage::helper('rocketshipit/data');
        $rsiRate = new \RocketShipIt\Rate($courier);
        $addr = null;

        if ($addrObj instanceof Mage_Shipping_Model_Rate_Request) {
            $addr = $helper->_extractAddrFromMageShippingModelRateRequest($addrObj);
        }
        if ($addrObj instanceof Mage_Sales_Model_Order_Address) {
            $addr = $helper->_extractAddrFromMageSalesModelOrderAddress($addrObj);
        }

        $rsiRate->setParameter('toCode', $addr['zip']);
        $rsiRate->setParameter('toState', $addr['state']);
        $rsiRate->setParameter('toCountry', $addr['country']);
        $rsiRate->setParameter('weight', $addr['weight']);
        $rsiRate->setParameter('weightPounds', $addr['weight']);

        $rsiRate->setParameter('residentialAddressIndicator','1');

        return $rsiRate;
    }

    public function getSimpleRates($carrierCode,
                                   $addrObj,
                                   $useNegotiatedRate = false,
                                   $weight = null,
                                   $handling = 0,
                                   $codeMask = null,
                                   $freeCodeMask = null) {
        $rsiRates = $this->getRSIRate($carrierCode, $addrObj);
        if ($weight != null) {
            $rsiRates->setParameter('weight', $weight);
            if (strtoupper($carrierCode) === 'STAMPS') {
                $rsiRates->setParameter('weightPounds', $weight);
            }
        }

        $cacheKey = $this->_getRateResponseCacheKey($rsiRates);
        $response = $this->_getCachedRateResponse($cacheKey);
        $result = Mage::getModel('shipping/rate_result');

        if (!$response) {
            try {
                $response = $rsiRates->getSimpleRates();
                Mage::helper('rocketshipit/data')->log("debug: ".$rsiRates->debug());
                $this->_setCachedRateResponse($cacheKey, $response);
            }
            catch (Exception $e) {
                $error = Mage::getModel('shipping/rate_result_error');
                $error->addData(array('error_message' => $e->getMessage()));
                $result->append($error);
                return $result;
            }
        }

        //Mage::helper('rocketshipit/data')->log('Simple rate fetch raw results: '.print_r($response, true));

        $errorMsg = $response['error'];
        if ($errorMsg != null) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->addData(array('error_message' => $errorMsg));
            $result->append($error);
            return $result;
        }

        $helper = Mage::helper('rocketshipit/data');
        $fullCode = $helper->getFullCarrierCode($carrierCode);
        $carrierName = Mage::getStoreConfig('carriers/'.$fullCode.'/title');
        $rateKey = $useNegotiatedRate ? 'negotiated_rate' : 'rate';

        //Mage::helper('rocketshipit/data')->log('Code mask: '.print_r($codeMask, true));

        foreach($response as $rsiMethod) {
            $serviceCode = $this->_getServiceCode($carrierCode, $rsiMethod);

            if ($codeMask && !in_array($serviceCode, $codeMask)) {
                continue;
            }

            if($useNegotiatedRate && $rsiMethod['negotiated_rate'] == null) {
                continue;
            }

            if(!$useNegotiatedRate && $rsiMethod['negotiated_rate']) {
                continue;
            }

            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier($fullCode);

            $showCarrierName = Mage::getStoreConfig('carriers/'.$fullCode.'/show_carrier_title');
            $configuredCarrierName = $showCarrierName ? $carrierName : '';
            $method->setCarrierTitle($configuredCarrierName);

            $methodTitle = $rsiMethod['desc'];
            if (strpos($methodTitle, $carrierName) === 0) {
                $methodTitle = trim(substr($methodTitle, strlen($carrierName)));
            }

            $configuredMethodTitleKey = 'carriers/rocketshipit_'.$carrierCode.'/title_'.strtoLower(str_replace(' ', '_', $methodTitle));
            $configuredMethodTitle = Mage::getStoreConfig($configuredMethodTitleKey);
            if (!$configuredMethodTitle) {
                $configuredMethodTitle = $methodTitle;
            }

            $method->setMethod($serviceCode);
            $method->setMethodTitle($configuredMethodTitle);

            Mage::helper('rocketshipit/data')->log("Free coupon flag? {$addrObj->getFreeShipping()}; Request: {$addrObj->getFreeShipping()}; free mask: ".print_r($freeCodeMask, true));


            if ($addrObj->getFreeShipping()) {
                if ((!$freeCodeMask) || in_array($serviceCode, $freeCodeMask)) {
                    $freeMethod = Mage::getModel('shipping/rate_result_method');

                    $freeMethod->setCost(0);
                    $freeMethod->setPrice(0);

                    $freeMethod->setCarrier($fullCode);
                    $freeMethod->setCarrierTitle('');

                    $freeMethod->setMethod($serviceCode);
                    $freeMethod->setMethodTitle('TRS Free Shipping');

                    $result->append($freeMethod);
                }
            }
            $method->setCost($rsiMethod[$rateKey]);
            $method->setPrice($rsiMethod[$rateKey] + $handling);

            $result->append($method);
        }

        return $result;
    }

    function _getServiceCode($carrierCode, $rsiMethod) {
        $serviceCode = $rsiMethod['service_code'];
        if ($carrierCode === 'stamps') {
            $desc = $rsiMethod['desc'];
            $descArr = explode(' - ', $desc);
            $packageType = str_replace(' ', '-', $descArr[1]);
            $serviceCode.=':'.$packageType;
        }
        return $serviceCode;
    }

    function _setCachedRateResponse($key, $response) {
        if (Mage::app()->useCache(self::RATE_CACHE_NAME)) {
            $cacheStr = serialize($response);
            Mage::app()->saveCache($cacheStr, $key, array(self::RATE_CACHE_KEY), 86400);
        }
    }

    function _getCachedRateResponse($key) {
        if (Mage::app()->useCache(self::RATE_CACHE_NAME)) {
            $cachedVal = Mage::app()->loadCache($key);
            $cachedObj = unserialize($cachedVal);
            return $cachedObj;
        }
        return null;
    }

    function _getRateResponseCacheKey($rsiRate) {
        return md5(serialize($rsiRate));
    }
}

