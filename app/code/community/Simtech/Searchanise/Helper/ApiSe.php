<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

class Simtech_Searchanise_Helper_ApiSe
{
    const COMPRESS_RATE = 5;
    const PLATFORM_NAME = 'magento';
    const CONFIG_PREFIX = 'se_';

    const MAX_PAGE_SIZE = 100; // The "All" variant of the items per page menu is replaced with this value if the "Allow All Products per Page" option is active.

    // const MIN_QUANTITY_DECIMALS = '0.00001';
    const MIN_QUANTITY_DECIMALS = ''; // not activated, because Server have floaf = decimal(12,2) 

    const SUGGESTIONS_MAX_RESULTS = 1;
    const FLOAT_PRECISION = 2; // for server float = decimal(12,2)
    const LABEL_FOR_PRICES_USERGROUP = 'se_price_';

    protected static $parentPrivateKeySe;
    protected static $privateKeySe = array();
    
    const EXPORT_STATUS_QUEUED     = 'queued';
    const EXPORT_STATUS_START      = 'start';
    const EXPORT_STATUS_PROCESSING = 'processing';
    const EXPORT_STATUS_SENT       = 'sent';
    const EXPORT_STATUS_DONE       = 'done';
    const EXPORT_STATUS_SYNC_ERROR = 'sync_error';
    const EXPORT_STATUS_NONE       = 'none';

    const STATUS_NORMAL = 'normal';
    const STATUS_DISABLED = 'disabled';
        
    public static $exportStatusTypes = array(
        self::EXPORT_STATUS_QUEUED,
        self::EXPORT_STATUS_START,
        self::EXPORT_STATUS_PROCESSING,
        self::EXPORT_STATUS_SENT,
        self::EXPORT_STATUS_DONE,
        self::EXPORT_STATUS_SYNC_ERROR,
        self::EXPORT_STATUS_NONE,
    );

    const NOT_USE_HTTP_REQUEST     = 'not_use_http_request';
    const NOT_USE_HTTP_REQUEST_KEY = 'Y';

    const FL_SHOW_STATUS_ASYNC     = 'show_status';
    const FL_SHOW_STATUS_ASYNC_KEY = 'Y';

    public static function getParamNotUseHttpRequest()
    {
        return self::NOT_USE_HTTP_REQUEST . '=' . self::NOT_USE_HTTP_REQUEST_KEY;
    }

    public static function checkNotificationAsyncComleted()
    {
        return !self::getSetting('notification_async_completed', self::CONFIG_PREFIX);
    }

    public static function setNotificationAsyncComleted($value = null)
    {
        self::setSetting('notification_async_completed', $value, self::CONFIG_PREFIX);
        
        return true;
    }
    
    public static function checkAutoInstall()
    {
        return !self::getSetting('auto_install_initiated', self::CONFIG_PREFIX);
    }

    public static function setAutoInstall($value = null)
    {
        self::setSetting('auto_install_initiated', $value, self::CONFIG_PREFIX);
        
        return true;
    }

    public static function checkCronAsync()
    {
        return self::getSetting('cron_async_enabled');
    }

    public static function checkAjaxAsync()
    {
        return self::getSetting('ajax_async_enabled');
    }

    public static function checkObjectAsync()
    {
        return self::getSetting('object_async_enabled');
    }
    
    public static function getInputIdSearch()
    {
        return self::getSetting('input_id_search');
    }

    public static function getEnabledSearchaniseSearch()
    {
        return self::getSetting('enabled_searchanise_search');
    }

    public static function getLabelForPricesUsergroup() {
        return self::LABEL_FOR_PRICES_USERGROUP;
    }

    public static function getCurLabelForPricesUsergroup() {
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if (!$customerGroupId) {
            $customerGroupId = 0;
        }
        return self::getLabelForPricesUsergroup() . $customerGroupId;
    }

    public static function getMaxPageSize() {
        return self::MAX_PAGE_SIZE;
    }

    public static function getFloatPrecision() {
        return self::FLOAT_PRECISION;
    }

    public static function getSuggestionsMaxResults() {
        return self::SUGGESTIONS_MAX_RESULTS;
    }

    public static function getMinQuantityDecimals() {
        return self::MIN_QUANTITY_DECIMALS;
    }

    public static function getServiceUrl($onlyHttp = true)
    {
        $ret = self::getSetting('service_url');

        if (!$onlyHttp) {
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $ret = str_replace('http://', 'https://', $ret);
            }
        }
        
        return $ret;
    }

    public static function getSearchWidgetsLink($onlyHttp = true)
    {
        return self::getServiceUrl($onlyHttp) . '/widgets/v1.0/init.js';
    }

    public static function checkSearchaniseResult($searchaniseRequest = null, $store = null)
    {
        if ((self::getStatusModule($store) == 'Y') &&
            (self::checkExportStatus($store)) &&
            (!empty($searchaniseRequest))) {

            if ($searchaniseRequest === true) {
                return true;
            }

            return $searchaniseRequest->checkSearchResult();
        }

        return false;
    }

    public static function getStatusModule($store = null, $moduleName = 'Simtech_Searchanise')
    {
        if (empty($moduleName)) {
            return;
        }
        
        $disableStatus = self::getSettingStore($moduleName, $store, 'advanced/modules_disable_output/');
        
        if ($disableStatus) {
            return 'D';
        }
        
        return 'Y';
    }

    public static function checkStatusModule($store = null, $moduleName = 'Simtech_Searchanise')
    {
        return self::getStatusModule($store, $moduleName) == 'Y';
    }
    
    public static function setUseNavigation($value = null)
    {
        self::setSetting('use_navigation', $value, self::CONFIG_PREFIX);
        
        return true;
    }

    public static function getUseNavigation($value = null)
    {
        return self::getSetting('use_navigation', self::CONFIG_PREFIX);
    }

    public static function setLastRequest($value = null)
    {
        self::setSetting('last_request', $value, self::CONFIG_PREFIX);
        
        return true;
    }
    
    public static function getLastRequest()
    {
        return self::getSetting('last_request', self::CONFIG_PREFIX);
    }
    
    public static function setLastResync($value = null)
    {
        self::setSetting('last_resync', $value, self::CONFIG_PREFIX);
        
        return true;
    }
    
    public static function getLastResync()
    {
        return self::getSetting('last_resync', self::CONFIG_PREFIX);
    }
    
    public static function getSearchaniseLink()
    {
        return 'searchanise/index';
    }
    
    public static function getAsyncLink($flNotUserHttpRequest = false)
    {
        $link = 'searchanise/async/';

        if ($flNotUserHttpRequest) {
            $link .= '?' . self::getParamNotUseHttpRequest();
        }

        return $link;
    }

    public static function getAsyncUrl($flNotUserHttpRequest = false, $storeId = '', $flCheckSecure = true)
    {
        return self::getUrl(self::getAsyncLink(false), $flNotUserHttpRequest, $storeId, $flCheckSecure);
    }

    public static function getReSyncLink()
    {
        return 'searchanise/resync';
    }

    public static function getOptionsLink()
    {
        return 'searchanise/options';
    }

    public static function getModuleLink()
    {
        return 'searchanise/index/index';
    }

    public static function getModuleUrl()
    {
        return Mage::helper("adminhtml")->getUrl(self::getModuleLink());
    }

    public static function getConnectLink()
    {
        return 'searchanise/signup';
    }

    public static function getConnectUrl($flNotUserHttpRequest = false, $storeId = '', $flCheckSecure = true)
    {
        return self::getUrl(self::getConnectLink(), $flNotUserHttpRequest, $storeId, $flCheckSecure);
    }

    public static function getUrl($link, $flNotUserHttpRequest = false, $storeId = '', $flCheckSecure = true)
    {
        if ($storeId != '') {
            $prevStoreId = Mage::app()->getStore()->getId();
            // need for generate correct url
            if ($prevStoreId != $storeId) {
                Mage::app()->setCurrentStore($storeId);
            }
        }

        $params = array();

        if ($flCheckSecure) {
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $params['_secure'] = true;
            }
        }

        $url = Mage::getUrl($link, $params);

        if ($flNotUserHttpRequest) {
            $url .= strpos($asyncUrl, '?') === false ? '?' : '&';
            $url .= self::getParamNotUseHttpRequest();
        }

        if ($storeId != '') {
            if ($prevStoreId != $storeId) {
                Mage::app()->setCurrentStore($prevStoreId);
            }
        }

        return $url;
    }
    
    public static function getLocaleCode($store = null) {
        if (empty($store)) {
            return self::getSetting('code', 'general/locale/');
        }
        
        return self::getSettingStore('code', $store, 'general/locale/');
    }
    
    public static function getDefaultCurrency($store = null) {
        if (empty($store)) {
            return self::getSetting('default', 'currency/options/');
        }
        
        return self::getSettingStore('default', $store, 'currency/options/');
    }
    
    public static function getJsPriceFormat($store = null)
    {
        if (!($store instanceof Mage_Core_Model_Store)) {
            return Mage::app()->getLocale()->getJsPriceFormat();
        }
                
        $format = Zend_Locale_Data::getContent(self::getLocaleCode($store), 'currencynumber');
        $symbols = Zend_Locale_Data::getList(self::getLocaleCode($store), 'symbols');
        
        $pos = strpos($format, ';');
        if ($pos !== false) {
            $format = substr($format, 0, $pos);
        }
        $format = preg_replace("/[^0\#\.,]/", "", $format);
        $totalPrecision = 0;
        $decimalPoint = strpos($format, '.');
        if ($decimalPoint !== false) {
            $totalPrecision = (strlen($format) - (strrpos($format, '.')+1));
        } else {
            $decimalPoint = strlen($format);
        }
        $requiredPrecision = $totalPrecision;
        $t = substr($format, $decimalPoint);
        $pos = strpos($t, '#');
        if ($pos !== false) {
            $requiredPrecision = strlen($t) - $pos - $totalPrecision;
        }
        $group = 0;
        if (strrpos($format, ',') !== false) {
            $group = ($decimalPoint - strrpos($format, ',') - 1);
        } else {
            $group = strrpos($format, '.');
        }
        
        $integerRequired = (strpos($format, '.') - strpos($format, '0'));
        
        $result = array(
            'pattern'           => $store->getCurrentCurrency()->getOutputFormat(),
            'precision'         => $totalPrecision,
            'requiredPrecision' => $requiredPrecision,
            'decimalSymbol'     => $symbols['decimal'],
            'groupSymbol'       => $symbols['group'],
            'groupLength'       => $group,
            'integerRequired'   => $integerRequired
        );
        
        return $result;
    }
    
    public static function getPriceFormat($store = null)
    {
        $jsPriceFormat = self::getJsPriceFormat($store);
        // It is need after getJsPriceFormat, because the 'getJsPriceFormat' function has simple method for '$store = null'.
        if (!($store instanceof Mage_Core_Model_Store)) {
            $store = Mage::app()->getStore($store);
        }

        // fixme if need
        // change $positionPrice for AdminPanelWidget like as customer area
        $positionPrice = strpos($jsPriceFormat['pattern'], '%s');

        $symbol = str_replace('%s', '', $jsPriceFormat['pattern']);        
        // fixme in the future
        // need delete
        // } else {
        //     $currency = Mage::app()->getLocale()->currency($store->getCurrentCurrencyCode());
        //     $symbol = $currency->getSymbol(self::getDefaultCurrency($store), self::getLocaleCode($store));

        //     if (empty($symbol)) {
        //         $symbol = str_replace('%s', '', $jsPriceFormat['pattern']);
        //     }
        // }

        $seRate = 1;
        $rate = $store->getCurrentCurrencyRate();
        if (!empty($rate)) {
            $seRate = 1 / $rate;
        }

        $priceFormat = array(
            'rate'                => $seRate, // It requires inverse value.
            'decimals'            => $jsPriceFormat['precision'],
            'decimals_separator'  => $jsPriceFormat['decimalSymbol'],
            'thousands_separator' => $jsPriceFormat['groupSymbol'],
            'symbol'              => $symbol,
            'after'               => $positionPrice == 0 ? true : false,
        );

        return $priceFormat;
    }
    
    /**
     * Format date using current locale options
     *
     * @param   timestamp|int
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public static function formatDate($timestamp = null, $format = 'short', $showTime = false)
    {
        if (empty($timestamp)) {
            return '';
        }

        $date = Mage::app()->getLocale()->date($timestamp, null, null);
        
        return Mage::helper('core')->formatDate($date, $format, $showTime);
    }
    
    public static function getAddonOptions($store = null)
    {
        $ret = array();
        
        $ret['parent_private_key'] = self::getParentPrivateKey();
        $ret['private_key']        = self::getPrivateKeys();
        $ret['api_key']            = self::getApiKeys();
        $ret['export_status']      = self::getExportStatuses();
        
        $ret['last_request'] = self::formatDate(self::getLastRequest(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
        $ret['last_resync']  = self::formatDate(self::getLastResync(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
        
        $ret['addon_status'] = self::getStatusModule() == 'Y' ? 'enabled' : 'disabled';
        $ret['addon_version'] = (string) Mage::getConfig()->getModuleConfig("Simtech_Searchanise")->version;

        $versionInfo = Mage::getVersionInfo();
        $coreEdition = 'Community';
        // [v1.7] [v1.8]
        if (method_exists('Mage', 'getEdition')) {
            $coreEdition = Mage::getEdition();
        // [/v1.7] [/v1.8]
        // [v1.5] [v1.6]
        } elseif (isset($versionInfo['minor']) && $versionInfo['minor'] > 6) {
            $coreEdition = 'Enterprise';
        }
        // [/v1.5] [/v1.6]
        $ret['core_edition'] = $coreEdition;
        $ret['core_version'] = Mage::getVersion();
        $ret['core_version_info'] = $versionInfo;

        return $ret;
    }

    public static function setInsalledModuleVersion($value = null)
    {
        self::setSetting('installed_module_version', $value, self::CONFIG_PREFIX);
        
        return true;
    }

    public static function getInsalledModuleVersion()
    {
        return (string) self::getSetting('installed_module_version', self::CONFIG_PREFIX);
    }

    public static function updateInsalledModuleVersion()
    {
        $currentVersion = (string) Mage::getConfig()->getModuleConfig("Simtech_Searchanise")->version;

        return self::setInsalledModuleVersion($currentVersion);
    }

    public static function checkModuleIsUpdated()
    {
        $currentVersion = Mage::getConfig()->getModuleConfig("Simtech_Searchanise")->version;

        return self::getInsalledModuleVersion() != $currentVersion;
    }

    public static function getServerVersion()
    {
        return self::getSetting('server_version');
    }

    public static function getAsyncMemoryLimit()
    {
        return self::getSetting('async_memory_limit');
    }
    
    public static function getSearchTimeout()
    {
        return self::getSetting('search_timeout');
    }
    
    public static function getRequestTimeout()
    {
        return self::getSetting('request_timeout');
    }

    public static function getAjaxAsyncTimeout()
    {
        return self::getSetting('ajax_async_timeout');
    }
    
    public static function getProductsPerPass()
    {
        return self::getSetting('products_per_pass');
    }
    
    public static function getMaxErrorCount()
    {
        return self::getSetting('max_error_count');
    }
    
    public static function getMaxProcessingThread()
    {
        return self::getSetting('max_processing_thread');
    }
    
    public static function getMaxProcessingTime()
    {
        return self::getSetting('max_processing_time');
    }
    
    public static function getMaxSearchRequestLength()
    {
        return self::getSetting('max_search_request_length');
    }
    
    public static function setApiKey($value, $store = null)
    {
        return self::setSettingStore('api_key', $store, $value, self::CONFIG_PREFIX);
    }
    
    public static function getApiKey($store = null)
    {
        return self::getSettingStore('api_key', $store, self::CONFIG_PREFIX);
    }

    public static function getApiKeys()
    {
        $ret = array();
        $stores = Mage::app()->getStores();
        
        if ($stores != '') {
            foreach ($stores as $k_store => $store) {
                $ret[$store->getId()] = self::getApiKey($store);
            }
        }
        
        return $ret;
    }
    
    public static function getDate()
    {
        return date('Y-m-d H:i:s');
    }
    
    public static function getTime()
    {
        return time();
    }
    
    
    public static function getStores($curStores = null, $storeIds = null)
    {
        if (!empty($storeIds)) {
            if (!is_array($storeIds)) {
                $curStores = Mage::app()->getStore($storeIds);
            } else {
                $curStores = array();
                foreach ($storeIds as $storeId) {
                    $curStores[] = Mage::app()->getStore($storeId);
                }
            }
        }
        
        if (!empty($curStores)) {
            if (!is_array($curStores)) {
                $stores = array(
                    0 => $curStores,
                );
            } else {
                $stores = $curStores;
            } 
        } else {
            $stores = Mage::app()->getStores();
        }
        
        return $stores;
    }
    
    public static function showNotificationAsyncCompleted()
    {
        if (self::checkImportIsDone()) {
            if (self::checkNotificationAsyncComleted()) {
                $textNotification = Mage::helper('searchanise')->__('Catalog indexation is complete. Configure Searchanise via the <a href="%s">Admin Panel</a>.', Mage::helper('searchanise/ApiSe')->getModuleUrl());

                Mage::helper('searchanise/ApiSe')->setNotification('N', Mage::helper('searchanise')->__('Searchanise'), $textNotification);
                self::setNotificationAsyncComleted(true);
            }
        }

        return true;
    }

    /**
     * Set notification message
     *
     * @param string $type notification type (E - error, W - warning, N - notice)
     * @param string $title notification title
     * @param string $message notification message
     */
    public static function setNotification($type = 'N', $title = '', $message = '', $session = null)
    {
        if (empty($session)) {
            $session = Mage::getSingleton('adminhtml/session');
        }
        
        if ($session) {
            if ($type == 'N') {
                $session->addNotice($title . ': '. $message);
            } elseif ($type == 'W') {
                $session->addWarning($title . ': '. $message);
            } elseif ($type == 'E') {
                $session->addError($title . ': '. $message);
            }
        }
        
        return true;
    }
    
    public static function httpRequest($method = Zend_Http_Client::POST, $url = '', $data = array(), $cookies = array(), $basicAuth = array(), $timeout = 0, $maxredirects = 5)
    {
        $client = new Zend_Http_Client();
        
        $client->setUri($url);
        
        $client->setConfig(array(
            'maxredirects'  => $maxredirects,
            'timeout'       => $timeout,
        ));

        if ($method == Zend_Http_Client::GET) {
            $client->setParameterGet($data);

        } elseif ($method == Zend_Http_Client::POST) {
            $client->setParameterPost($data);
        }
        
        try {
            $response = $client->request($method);
        } catch (Exception $e) {
            self::log($e->getMessage());
            
            return null;
        }
        
        $responseBody = $response->getBody();
        
        // fixme in the future
        // add getHeader()
        //~ return array($response->getHeader(), $response->getBody());
        return array('', $response->getBody());
    }

    /**
     * 
     *
     * @param array $arr_cat
     * @param Mage_Catalog_Model_Category $category
     * @return array
     */
    public static function getAllChildrenCategories(&$arr_cat, $category, $fl_include_cur_cat = true)
    {
        if (empty($arr_cat)) { 
            $arr_cat = array(); 
        }
        
        if (!empty($category)) {
            if ($fl_include_cur_cat == true) { 
                $arr_cat[] = $category->getId(); 
            }
            
            $children_cat = $category->getChildrenCategories();
            
            if (!empty($children_cat)) {
                foreach ($children_cat as $cat) {
                    self::getAllChildrenCategories($arr_cat, $cat, $fl_include_cur_cat);
                }
            }
        }
        
        return $arr_cat;
    }

    public static function escapingCharacters($str)
    {
        $ret = '';

        if ($str != '') {
            $str = trim($str);

            if ($str != '') {
                $str = str_replace('|', '\|', $str);
                $str = str_replace(',', '\,', $str);

                $ret = $str;
            }
        }

        return $ret;
    }
    
    public static function getPriceValueFromRequest($dataPrice)
    {
        $ret = '';
        $priceFrom = '';
        $priceTo = '';

        if (is_array($dataPrice)) {
            // example array( [from] => 0 [to] => 33 )
            if (!empty($dataPrice)) {
                if ($dataPrice['from'] != '') {
                    $priceFrom = trim($dataPrice['from']);
                }
                if ($dataPrice['to'] != '') {
                    $priceTo = trim($dataPrice['to']);
                }
            }
        } elseif ($dataPrice != '') {
            if (strpos($dataPrice, '-') === false) {
                // [v1.5] [v1.6]
                $arrPrice = explode(',', $dataPrice);

                if (is_array($arrPrice) && (count($arrPrice) >= 2)) {
                    $numberRange = (int) reset($arrPrice);
                    next($arrPrice);
                    $step = (int) current($arrPrice);

                    if ($numberRange > 1) {
                        $priceFrom = ($numberRange - 1) * $step;
                    }
                    $priceTo = $numberRange * $step;
                }                
            } else {
                // [v1.7] [v1.8]
                $arrPrice = explode('-', $dataPrice);
                if (is_array($arrPrice) && (count($arrPrice) >= 2)) {
                    $priceFrom = (int) reset($arrPrice);
                    next($arrPrice);
                    $priceTo = (int) current($arrPrice);
                }
            }
        }

        if (($priceFrom != '') || ($priceTo != '')) {
            $rate = Mage::app()->getStore()->getCurrentCurrencyRate();

            if ((!$rate) || ($rate == 1)) {
                // nothing
            } else {
                if ($priceFrom != '') {
                    $priceFrom /= $rate;    
                }
                if ($priceTo != '') {
                    $priceTo /= $rate;    
                }
            }
            if ($priceFrom != '') {
                $ret .= $priceFrom;
            }
            $ret .= ',';
            if ($priceTo != '') {
                $ret .= $priceTo;
            }
        }
        
        return $ret;
    }
    
    public static function queueImport($curStore = NULL, $showNotification = true)
    {
        if (!self::checkParentPrivateKey()) {
            return;
        }
        self::setNotificationAsyncComleted(false);

        // Delete all exist queue, need if exists error
        Mage::getModel('searchanise/queue')->clearActions($curStore);
        
        Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_PREPARE_FULL_IMPORT, NULL, $curStore);
        
        $stores = self::getStores($curStore);
        
        foreach ($stores as $store) {
            self::setExportStatus(self::EXPORT_STATUS_QUEUED, $store);
        }
        
        if ($showNotification == true) {
            self::setNotification('N', Mage::helper('searchanise')->__('Notice'), Mage::helper('searchanise')->__('The product catalog is queued for syncing with Searchanise'));
        }
        
        return true;
    }
    
    /**
     * Build query from the array
     *
     * @param array $array data to build query from
     * @param string $query part of query to attach new data
     * @param string $prefix prefix
     * @return string well-formed query
     */
    public static function buildQuery($array, $query = '', $prefix = '')
    {
        if (!is_array($array)) { return false; }
        
        foreach ($array as $k => $v)
        {
            if (is_array($v)) {
                $query = self::buildQuery($v, $query, rawurlencode(empty($prefix) ? "$k" : $prefix . "[$k]"));
            } else {
                $query .= (!empty($query) ? '&' : '') . (empty($prefix) ? $k : $prefix . rawurlencode("[$k]")). '=' . rawurlencode($v);
            }
        }
        
        return $query;
    }
    
    public static function setSettingStore($name, $store = null, $value = null, $prefix = 'searchanise/config/')
    {
        if (!empty($name)) {
            if (empty($store)) {
                $store = Mage::app()->getStore();
            }

            if ($prefix == self::CONFIG_PREFIX) {
                Mage::getModel('searchanise/config')->saveConfig($prefix . $name, $value, 'stores', $store->getId());
            } else {
                $config = new Mage_Core_Model_Config();
                $config->saveConfig($prefix . $name, $value, 'stores', $store->getId());
                
                // make changes apply right away
                Mage::getConfig()->cleanCache();
            }
            
            return true;
        }
        
        return false;
    }
    
    public static function getSettingStore($name, $store = null, $prefix = 'searchanise/config/')
    {
        if (!empty($name)) {
            if (empty($store)) {
                $store = Mage::app()->getStore();
            }

            if ($prefix == self::CONFIG_PREFIX) {
                return Mage::getModel('searchanise/config')->getConfig($prefix . $name, 'stores', $store->getId());
            } else {
                return $store->getConfig($prefix . $name);
            }
        }
        
        return null;
    }
    
    public static function setSetting($name, $value = null, $prefix = 'searchanise/config/')
    {
        if (!empty($name)) {
            if ($prefix == self::CONFIG_PREFIX) {
                Mage::getModel('searchanise/config')->saveConfig($prefix . $name, $value);
            } else {
                $config = new Mage_Core_Model_Config();
                $config->saveConfig($prefix . $name, $value);
                
                // make changes apply right away
                Mage::getConfig()->cleanCache();
            }
            
            return true;
        }
        
        return false;
    }
    
    public static function getSetting($name, $prefix = 'searchanise/config/')
    {
        if (!empty($name)) {
            if ($prefix == self::CONFIG_PREFIX) {
                return Mage::getModel('searchanise/config')->getConfig($prefix . $name);
            } else {
                return Mage::app()->getStore()->getConfig($prefix . $name);
            }
        }
        
        return null;
    }
    
    public static function setExportStatus($value, $store = null)
    {
        return self::setSettingStore('export_status', $store, $value, self::CONFIG_PREFIX);
    }
    
    public static function getExportStatus($store = null)
    {
        return self::getSettingStore('export_status', $store, self::CONFIG_PREFIX);
    }

    public static function checkExportStatus($store = null)
    {
        self::checkImportIsDone($store);
        return self::getExportStatus($store) == self::EXPORT_STATUS_DONE;
    }
    
    public static function getExportStatuses($store = null)
    {
        $ret = array();
        $stores = self::getStores($store);

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $ret[$store->getId()] = self::getExportStatus($store);
            }
        }
        
        return $ret;
    }
    
    public static function setPrivateKey($value = null, $store = null)
    {
        if (empty($store)) {
            $store = Mage::app()->getStore();
        }
        
        if (!empty($store)) {
            self::$privateKeySe[$store->getId()] = $value;
        }
        
        return self::setSettingStore('private_key', $store, $value, self::CONFIG_PREFIX);
    }
    
    public static function getPrivateKey($store = null, $storeId = null)
    {
        if (!empty($storeId)) {
            $store = Mage::app()->getStore($storeId);
        }
        
        if (empty($store)) {
            $store = Mage::app()->getStore(0);
        }
        
        if (!empty($store)) {
            if (!isset(self::$privateKeySe[$store->getId()])) {
                self::$privateKeySe[$store->getId()] = self::getSettingStore('private_key', $store, self::CONFIG_PREFIX);
            }
            
            return self::$privateKeySe[$store->getId()];
        }
        
        return null;
    }
    
    public static function checkPrivateKey($store = null, $storeId = null)
    {
        $key = self::getPrivateKey($store, $storeId);
        
        return empty($key) ? false : true;
    }
    
    public static function getPrivateKeys()
    {
        $ret = array();
        $stores = Mage::app()->getStores();
        
        if (!empty($stores)) {
            foreach ($stores as $k_store => $store) {
                $ret[$store->getId()] = self::getPrivateKey($store);
            }
        }
        
        return $ret;
    }
    
    public static function setParentPrivateKey($value = null)
    {
        self::$parentPrivateKeySe = $value;
        
        return self::setSetting('parent_private_key', $value, self::CONFIG_PREFIX);
    }
    
    public static function getParentPrivateKey()
    {
        if (!isset(self::$parentPrivateKeySe)) {
            self::$parentPrivateKeySe = self::getSetting('parent_private_key', self::CONFIG_PREFIX);
        }
        
        return self::$parentPrivateKeySe;
    }
    
    public static function checkParentPrivateKey()
    {
        $parentPrivateKey = self::getParentPrivateKey();
        
        return !empty($parentPrivateKey);
    }
    
    public static function signup($curStore = null, $showNotification = true, $flSendRequest = true)
    {
        @ignore_user_abort(true);
        @set_time_limit(0);

        if (self::checkAutoInstall()) {
            self::setAutoInstall(true);
        }
        
        $connected = false;
        
        static $isShowed = false;
        
        if (!empty($curStore)) {
            $privateKey = self::getPrivateKey($curStore);
            if (empty($privateKey)) {
                //~ return false;
            }
        }
        
        $adminSession = Mage::getSingleton('admin/session');
        $email = '';
        if ($adminSession) {
            $email = $adminSession->getUser()->getEmail();
        }

        if ($email != '') {
            $stores = self::getStores($curStore);
            $parentPrivateKey = self::getParentPrivateKey();
            
            foreach ($stores as $store) {
                $privateKey = self::getPrivateKey($store);
                
                if (!empty($privateKey)) {
                    if ($flSendRequest) {
                        if ($store->getIsActive()) {
                            self::sendAddonStatusRequest('enabled', $store);
                        } else {
                            self::sendAddonStatusRequest('disabled', $store);
                        }
                    }
                    $connected = true;

                    continue;
                }
                
                if ($showNotification == true && empty($isShowed)) {
                    self::echoConnectProgress('Connecting to Searchanise..');
                    $isShowed = true;
                }
                
                $url = $store->getUrl('', array('_nosid' => true));
                
                // need if new store without baseUrl
                if (!(strstr($url, "http"))) {
                    $base_url = Mage::app()->getStore()->getBaseUrl();
                    
                    $url = str_replace('index.php/', $base_url, $url);
                }
                
                list($h, $res) = self::httpRequest(
                    Zend_Http_Client::POST,
                    self::getServiceUrl() . '/api/signup',
                    array(
                        'url'                => $url,
                        'email'              => $email,
                        'version'            => self::getServerVersion(),
                        'platform'           => self::PLATFORM_NAME,
                        'parent_private_key' => $parentPrivateKey,        
                    ),
                    array(),
                    array(),
                    self::getRequestTimeout()
                );
                
                if ($showNotification == true) {
                    self::echoConnectProgress('.');
                }
                
                if (!empty($res)) {
                    $res = self::parseResponse($res, true);
                    
                    if (is_object($res)) {
                        $api_key = (string)$res->api;
                        $privateKey = (string)$res->private;
                        
                        if (empty($api_key) || empty($privateKey)) {
                            return false;
                        }
                        
                        if (empty($parentPrivateKey)) {
                            self::setParentPrivateKey($privateKey);
                            $parentPrivateKey = $privateKey;
                        }
                        
                        self::setApiKey($api_key, $store);
                        self::setPrivateKey($privateKey, $store);
                        
                        $connected = true;
                        
                    } else {
                        if ($showNotification == true) {
                            self::echoConnectProgress(' Error<br />');
                        }

                        break;
                    }
                }
                
                self::setExportStatus(self::EXPORT_STATUS_NONE, $store);
                self::setUseNavigation(true);
            }
        }

        if ($connected == true && $showNotification == true) {
            self::echoConnectProgress(' Done<br/>');
            self::setNotification('N', Mage::helper('searchanise')->__('Notice'), Mage::helper('searchanise')->__("Congratulations, you've just connected to Searchanise"));
        }

        return $connected;
    }
    
    public static function getMinMaxProductId($store = null)
    {
        $startId = 0;
        $endId = 0;

        $productStartCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_ASC)
            ->setPageSize(1);
        if ($store) {
            $productStartCollection = $productStartCollection->addStoreFilter($store);
        }
        $productStartCollection = $productStartCollection->load();

        $productEndCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize(1);
        if ($store) {
            $productEndCollection = $productEndCollection->addStoreFilter($store);
        }
        $productEndCollection = $productEndCollection->load();

        if ($productStartCollection) {
            $productArr = $productStartCollection->toArray(array('entity_id'));
            if (!empty($productArr)) {
                $firstItem = reset($productArr);
                $startId = $firstItem['entity_id'];
            }
        }

        if ($productEndCollection) {
            $productArr = $productEndCollection->toArray(array('entity_id'));
            if (!empty($productArr)) {
                $firstItem = reset($productArr);
                $endId = $firstItem['entity_id'];
            }
        }

        return array($startId, $endId);
    }
    
    public static function getProductIdsFormRange($start, $end, $step, $store = null)
    {
        $arrProducts = array();
        
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('entity_id', array("from" => $start, "to" => $end))
            ->setPageSize($step);
        
        if ($store) {
            $products = $products->addStoreFilter($store);
        }
        
        $products = $products->load();
        if ($products) {
            // Not used because 'arrProducts' comprising 'stock_item' field and is 'array(array())'
            // $arrProducts = $products->toArray(array('entity_id'));
            foreach ($products as $product) {
                $arrProducts[] = $product->getId();
            }
        }
        // It is necessary for save memory.
        unset($products);

        return $arrProducts;
    }
    
    public static function getFilterableFiltersIds($store = null)
    {
        $arrFilters = array();
        
        $filters = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->addIsFilterableFilter();
        
        if (!empty($store)) {
            $filters->addStoreLabel($store->getId());
        }
            
        if ($filters) {
            // It not used because 'arrFilters' is array(array())
            // $arrFilters = $filters->toArray(array('attribute'));
            foreach ($filters as $filter) {
                $arrFilters[] = $filter->getId();
            }
        }
        // It is necessary for save memory.
        unset($filters);

        return $arrFilters;
    }

    public static function getFilterableInSearchFiltersIds($store = null)
    {
        $arrFilters = array();
        
        $filters = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->addIsFilterableInSearchFilter();
        
        if (!empty($store)) {
            $filters->addStoreLabel($store->getId());
        }

        $filters->load();
        
        if ($filters) {
            // It not used because 'arrFilters' is array(array())
            // $arrFilters = $filters->toArray(array('attribute'));
            foreach ($filters as $filter) {
                $arrFilters[] = $filter->getId();
            }
        }
        // It is necessary for save memory.
        unset($filters);

        return $arrFilters;
    }

    public static function getFiltersIds($store = null)
    {
        $filterableInSearchFiltersIds = self::getFilterableInSearchFiltersIds($store);
        $filterableFiltersIds = self::getFilterableFiltersIds($store);

        if (empty($filterableInSearchFiltersIds)) {
            return $filterableFiltersIds;
        }

        if (empty($filterableFiltersIds)) {
            return $filterableInSearchFiltersIds;
        }

        foreach ($filterableFiltersIds as $key => $value) {
            if (!in_array($value, $filterableInSearchFiltersIds)) {
                $filterableInSearchFiltersIds[] = $value;
            }
        }

        return $filterableInSearchFiltersIds;
    }

    public static function checkStartAsync()
    {
        $ret = false;
        $q = Mage::getModel('searchanise/queue')->getNextQueue();

        if (!empty($q)) {
            //Note: $q['started'] can be in future.
            if ($q['status'] == Simtech_Searchanise_Model_Queue::STATUS_PROCESSING && ($q['started'] + self::getMaxProcessingTime() > self::getTime())) {
                $ret = false;

            } elseif ($q['error_count'] >= self::getMaxErrorCount()) {
                if ($q['store_id']) {
                    $store = Mage::app()->getStore($q['store_id']);
                } else {
                    $store = null;
                }

                $statuses = self::getExportStatuses($store);
                if ($statuses) {
                    foreach ($statuses as $statusKey => $status) {
                        if ($status != self::EXPORT_STATUS_SYNC_ERROR) {
                            if ($store) {
                                self::setExportStatus(self::EXPORT_STATUS_SYNC_ERROR, $store);
                            } else {
                                $stores = self::getStores();
                                foreach ($stores as $stKey => $_st) {
                                    self::setExportStatus(self::EXPORT_STATUS_SYNC_ERROR, $_st);
                                }
                                break;
                            }
                        }
                    }
                }

                $ret = false;
                
            } else {
                $ret = true;
            }
        }

        return $ret;
    }

    public static function async($flIgnoreProcessing = false)
    {
        @ignore_user_abort(true);
        @set_time_limit(0);
        // Adding memory if necessary
        $asyncMemoryLimit = Mage::helper('searchanise/ApiSe')->getAsyncMemoryLimit();
        if (substr(ini_get('memory_limit'), 0, -1) < $asyncMemoryLimit) {
            @ini_set('memory_limit', $asyncMemoryLimit . 'M');
        }
        
        // Need for get all products.
        Mage::app()->setCurrentStore('admin');

        self::echoConnectProgress('.');
        
        $q = Mage::getModel('searchanise/queue')->getNextQueue();
        
        while (!empty($q)) {
            if (Mage::helper('searchanise')->checkDebug()) {
                Mage::helper('searchanise/ApiSe')->printR($q);
            }
            $xml = '';
            $status = true;
            $data = array();
            $store = Mage::app()->getStore($q['store_id']);
            $xmlHeader = Mage::helper('searchanise/ApiXML')->getXMLHeader($store);
            $xmlFooter = Mage::helper('searchanise/ApiXML')->getXMLFooter($store);
            if ((!empty($q['data'])) && ($q['data'] != Simtech_Searchanise_Model_Queue::NOT_DATA)) {
                $data = unserialize($q['data']);
            }
            
            $privateKey = self::getPrivateKey($store);
            
            if (empty($privateKey)) {
                Mage::getModel('searchanise/queue')->load($q['queue_id'])->delete();
                $q = array();
                
                continue;
            }
            
            //Note: $q['started'] can be in future.
            if ($q['status'] == Simtech_Searchanise_Model_Queue::STATUS_PROCESSING && ($q['started'] + self::getMaxProcessingTime() > self::getTime())) {
                if (!$flIgnoreProcessing) {
                    return Simtech_Searchanise_Model_Queue::STATUS_PROCESSING;
                }
            }
            
            if ($q['error_count'] >= self::getMaxErrorCount()) {
                self::setExportStatus(self::EXPORT_STATUS_SYNC_ERROR, $store);
                
                return Simtech_Searchanise_Model_Queue::STATUS_DISABLED;
            }
            
            // Set queue to processing state
            Mage::getModel('searchanise/queue')
                ->load($q['queue_id'])
                ->setData('status',  Simtech_Searchanise_Model_Queue::STATUS_PROCESSING)
                ->setData('started', self::getTime())
                ->save();
            
            if ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_PREPARE_FULL_IMPORT) {
                Mage::getModel('searchanise/queue')
                    ->getCollection()
                    ->addFieldToFilter('action', array("neq" => Simtech_Searchanise_Model_Queue::ACT_PREPARE_FULL_IMPORT))
                    ->addFilter('store_id', $store->getId())
                    ->load()
                    ->delete();

                $queueData = array(
                    'data'     => Simtech_Searchanise_Model_Queue::NOT_DATA,
                    'action'   => Simtech_Searchanise_Model_Queue::ACT_START_FULL_IMPORT,
                    'store_id' => $store->getId(),
                );

                Mage::getModel('searchanise/queue')->setData($queueData)->save();
                
                $i = 0;
                $step = self::getProductsPerPass() * 50;

                list($start, $max) = self::getMinMaxProductId($store);

                do {
                    $end = $start + $step;
                    
                    $_productIds = self::getProductIdsFormRange($start, $end, $step, $store);
                    
                    $start = $end + 1;
                    
                    if (empty($_productIds)) {
                        continue;
                    }
                    $_productIds = array_chunk($_productIds, self::getProductsPerPass());
                    
                    foreach ($_productIds as $productIds) {
                        $_data = serialize($productIds);
                        $queueData = array(
                            'data'     => $_data,
                            'action'   => Simtech_Searchanise_Model_Queue::ACT_UPDATE,
                            'store_id' => $store->getId(),
                        );
                        $_result = Mage::getModel('searchanise/queue')->setData($queueData)->save();
                        // It is necessary for save memory.
                        unset($_result);
                        unset($_data);
                        unset($queueData);
                    }
                    
                } while ($end <= $max);

                self::echoConnectProgress('.');
                
                //
                // reSend all active filters
                //
                
                $queueData = array(
                    'data'     => Simtech_Searchanise_Model_Queue::NOT_DATA,
                    'action'   => Simtech_Searchanise_Model_Queue::ACT_FACET_DELETE_ALL,
                    'store_id' => $store->getId(),
                );
                Mage::getModel('searchanise/queue')->setData($queueData)->save();
                
                $filterIds = self::getFiltersIds($store);
                
                if (!empty($filterIds)) {
                    $queueData = array(
                        'data'     => serialize($filterIds),
                        'action'   => Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE,
                        'store_id' => $store->getId(),
                    );
                    
                    Mage::getModel('searchanise/queue')->setData($queueData)->save();
                }
                
                // add facet-categories
                {
                    $queueData = array(
                        'data'     => serialize(Simtech_Searchanise_Model_Queue::DATA_FACET_CATEGORIES),
                        'action'   => Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE,
                        'store_id' => $store->getId(),
                    );
                    
                    Mage::getModel('searchanise/queue')->setData($queueData)->save();
                }
                                
                // add facet-tags
                {
                    $queueData = array(
                        'data'     => serialize(Simtech_Searchanise_Model_Queue::DATA_FACET_TAGS),
                        'action'   => Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE,
                        'store_id' => $store->getId(),
                    );
                    
                    Mage::getModel('searchanise/queue')->setData($queueData)->save();
                }
                
                $queueData = array(
                    'data'     => Simtech_Searchanise_Model_Queue::NOT_DATA,
                    'action'   => Simtech_Searchanise_Model_Queue::ACT_END_FULL_IMPORT,
                    'store_id' => $store->getId(),
                );
                
                Mage::getModel('searchanise/queue')->setData($queueData)->save();
                
                $status = true;
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_START_FULL_IMPORT) {
                $status = self::sendRequest('/api/state/update', $privateKey, array('full_import' => self::EXPORT_STATUS_START), true);
                
                if ($status == true) {
                    self::setExportStatus(self::EXPORT_STATUS_PROCESSING, $store);
                }
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_END_FULL_IMPORT) {
                $status = self::sendRequest('/api/state/update', $privateKey, array('full_import' => self::EXPORT_STATUS_DONE), true);
                
                if ($status == true) {
                    self::setExportStatus(self::EXPORT_STATUS_SENT, $store);
                    self::setLastResync(self::getTime());
                }
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_FACET_DELETE_ALL) {
                $status = self::sendRequest('/api/facets/delete', $privateKey, array('all' => true), true);
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE) {
                if ($data == Simtech_Searchanise_Model_Queue::DATA_FACET_CATEGORIES) {
                    $xml .= Mage::helper('searchanise/ApiXML')->generateFacetXMLCategories();
                    
                } elseif ($data == Simtech_Searchanise_Model_Queue::DATA_FACET_PRICES) {
                    $xml .= Mage::helper('searchanise/ApiXML')->generateFacetXMLPrices();

                } elseif ($data == Simtech_Searchanise_Model_Queue::DATA_FACET_TAGS) {
                    $xml .= Mage::helper('searchanise/ApiXML')->generateFacetXMLTags();
                    
                } else {
                    $xml .= Mage::helper('searchanise/ApiXML')->generateFacetXMLFilters($data, $store);
                }
                
                if (!empty($xml)) {
                    $status = self::sendRequest('/api/facets/update', $privateKey, array('data' => $xmlHeader . $xml . $xmlFooter), true);
                }
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_FACET_DELETE) {
                if (!empty($data)) {
                    foreach ($data as $facetAttribute) {
                        $status = self::sendRequest('/api/facets/delete', $privateKey, array('attribute' => $facetAttribute), true);
                        
                        self::echoConnectProgress('.');
                        
                        if ($status == false) {
                            break;
                        }
                    }
                }
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_UPDATE) {
                $xml .= Mage::helper('searchanise/ApiXML')->generateProductsXML($data, $store);
                
                if (!empty($xml)) {
                    if (function_exists('gzcompress')) {
                        $data = gzcompress($xmlHeader . $xml . $xmlFooter, self::COMPRESS_RATE);
                    } else {
                        $data = $xmlHeader . $xml . $xmlFooter;
                    }
                    $status = self::sendRequest('/api/items/update', $privateKey, array('data' => $data), true);
                }
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_DELETE) {
                foreach ($data as $product_id) {
                    $status = self::sendRequest('/api/items/delete', $privateKey, array('id' => $product_id), true);
                    
                    self::echoConnectProgress('.');
                    
                    if ($status == false) {
                        break;
                    }
                }
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_DELETE_ALL) {
                $status = self::sendRequest('/api/items/delete', $privateKey, array('all' => true), true);
                
            } elseif ($q['action'] == Simtech_Searchanise_Model_Queue::ACT_PHRASE) {
                foreach ($data as $phrase) {
                    $status = self::sendRequest('/api/phrases/update', $privateKey, array('phrase' => $phrase), true);
                    
                    self::echoConnectProgress('.');
                    
                    if ($status == false) {
                        break;
                    }
                }
            }

            if (Mage::helper('searchanise')->checkDebug()) {
                Mage::helper('searchanise/ApiSe')->printR('status', $status);
            }
            
            // Change queue item status
            if ($status == true) {
                Mage::getModel('searchanise/queue')->load($q['queue_id'])->delete();
                $q = Mage::getModel('searchanise/queue')->getNextQueue($q['queue_id']);
            } else {
                $nextStartedTime = (self::getTime() - self::getMaxProcessingTime()) + $q['error_count'] * 60;
                
                $modelQueue = Mage::getModel('searchanise/queue')->load($q['queue_id']);
                
                $modelQueue
                    ->setData('status',  Simtech_Searchanise_Model_Queue::STATUS_PROCESSING)
                    ->setData('error_count',  $modelQueue->getData('error_count') + 1)
                    ->setData('started', $nextStartedTime)
                    ->save();
                
                break; //try later
            }
            self::echoConnectProgress('.');
        }

        return 'OK';
    }
    
    public static function echoConnectProgress($text)
    {
        // It is commented out as far as the warning occurs
        //~ echo $text;
    }
    
    public static function sendAddonStatusRequest($status = 'enabled', $curStore = NULL)
    {
        $stores = self::getStores($curStore);
        
        if (!empty($stores)) {
            foreach ($stores as $store) {
                $privateKey = self::getPrivateKey($store);
                self::sendRequest('/api/state/update', $privateKey, array('addon_status' => $status), true);
            }
        }
    }
    
    public static function sendRequest($urlPart, $privateKey, $data, $onlyHttp = true)
    {
        $ret = null;
        
        if (!empty($privateKey)) {
            $params = array('private_key' => $privateKey) + $data;
            
            list($h, $result) = self::httpRequest(Zend_Http_Client::POST, self::getServiceUrl($onlyHttp) . $urlPart, $params, array(), array(), self::getRequestTimeout());
            
            $ret = self::parseResponse($result, false);
            
            self::setLastRequest(self::getTime());
        }
        
        return $ret;
    }
    
    /**
     * Parse response from service
     *
     * @param string $res xml service response
     * @return mixed false if errors returned, true if response is ok, xml object if data was passed in the response
     */
    public static function parseResponse($res, $showNotification = false)
    {
        $xml = simplexml_load_string($res, null, LIBXML_NOERROR);
        
        if (empty($res) || $xml === false) {
            return false;
        }
        
        if (!empty($xml->error)) {
            foreach ($xml->error as $e) {
                if ($showNotification == true) {
                    self::setNotification('E', Mage::helper('searchanise')->__('Error'), 'Searchanise: ' . (string)$e);
                }
            }
            
            return false;
        } elseif (strtolower($xml->getName()) == 'ok') {
            return true;
        } else {
            return $xml;
        }
    }
    
    public static function parseStateResponse($xml, $element = false)
    {
        $res = array();
        if (!is_object($xml)) {
            return false;
        }
        
        if ($xml->getName() == 'variable') {
            foreach ($xml as $v) {
                $res[$v->getName()] = (string)$v;
            }
        } else {
            return false;
        }
        
        if (!empty($element)) {
            $res = isset($res[$element])? $res[$element] : false;
        }
        
        return $res;
    }
    
    public static function deleteKeys($stores = null)
    {
        if (empty($stores)) {
            $stores = Mage::app()->getStores();
        }
        
        if (!empty($stores)) {
            foreach ($stores as $store) {
                self::sendAddonStatusRequest('deleted', $store);
                
                self::setApiKey(null, $store);
                self::setPrivateKey(null, $store);
                self::setExportStatus(null, $store);

                self::setUseNavigation(true, $store);
                
                Mage::getModel('searchanise/queue')->deleteKeys($store);
            }
        }
        
        return true;
    }
    
    public static function checkImportIsDone($curStore = null)
    {
        $result = true;
        $stores = self::getStores($curStore);

        $skipTimeCheck = false;
        
        foreach ($stores as $store) {
            if (self::getExportStatus($store) == self::EXPORT_STATUS_SENT && ((self::getTime() - self::getLastRequest()) > self::getRequestTimeout() || $skipTimeCheck == true)) {
                $xml = self::sendRequest('/api/state/get', self::getPrivateKey($store), array('status' => '', 'full_import' => ''), true);
                
                $variables = self::parseStateResponse($xml);

                if ((!empty($variables)) && (isset($variables['status']))) {
                    if (($variables['status'] == self::STATUS_NORMAL) && 
                        (isset($variables['full_import'])) &&
                        ($variables['full_import'] == self::EXPORT_STATUS_DONE)) {
                        $skipTimeCheck = true;
                        self::setExportStatus(self::EXPORT_STATUS_DONE, $store);

                    } elseif ($variables['status'] == self::STATUS_DISABLED) {
                        self::setExportStatus(self::EXPORT_STATUS_NONE, $store);
                    }
                }
            }
            if (self::getExportStatus($store) != self::EXPORT_STATUS_DONE) {
                $result = false;
            }
        }
        
        if ($skipTimeCheck == true) {
            // nothing
        }

        return $result;
    }
    
    public static function getPriceFilters($store = null)
    {
        $filters = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->addIsFilterableFilter()
            ->addFieldToFilter('frontend_input', array('eq' => 'price'));
        
        
        if (!empty($store)) {
            $filters->addStoreLabel($store->getId());
        }
        
        $filters->load();
        
        return $filters;
    }
    
    public static function getStoreByWebsiteIds($websiteIds = array())
    {
        $ret = array();
        
        if (!empty($websiteIds)) {
            if (!is_array($websiteIds)) {
                $websiteIds = array(
                    0 => $websiteIds
                );
            }
            
            $stores = Mage::app()->getStores();
            
            if (!empty($stores)) {
                foreach ($stores as $k => $store) {
                    $websiteId = $store->getWebsite()->getId();
                    
                    if (in_array($websiteId, $websiteIds)) {
                        $ret[] = $store->getId();
                    }
                }
            }
        }
        
        return $ret;
    }
    
    public static function getStoreByWebsiteCodes($websiteCodes = array())
    {
        $ret = array();
        
        if (!empty($websiteCodes)) {
            if (!is_array($websiteCodes)) {
                $websiteCodes = array(
                    0 => $websiteCodes
                );
            }
            $stores = Mage::app()->getStores();
            
            if (!empty($stores)) {
                foreach ($stores as $k => $store) {
                    $websiteCode = $store->getWebsite()->getCode();
                    
                    if (in_array($websiteCode, $websiteCodes)) {
                        $ret[] = $store->getId();
                    }
                }
            }
        }
        
        return $ret;
    }
    
    function printR()
    {
        static $count = 0;
        $args = func_get_args();

        if (!empty($args)) {
            echo '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';
            foreach ($args as $k => $v) {
                $v = htmlspecialchars(print_r($v, true));
                if ($v == '') {
                    $v = '    ';
            }

                echo '<li><pre>' . $v . "\n" . '</pre></li>';
            }
            echo '</ol><div style="clear:left;"></div>';
        }
        $count++;
    }

    public static function log($message = '', $type = 'Error')
    {
        Mage::log("Searchanise # {$type}: {$message}");
        
        return true;
    }
}