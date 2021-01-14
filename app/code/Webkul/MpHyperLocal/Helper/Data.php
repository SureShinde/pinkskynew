<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Catalog\Model\ProductFactory;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Webkul\Marketplace\Model\SellerFactory;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Json\Helper\Data as JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    protected $shipAreaFactory;

    /**
     * @var SellerFactory
     */
    protected $sellerFactory;
    
    /**
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param EncryptorInterface $encryptor
     * @param JsonHelper $jsonHelper
     * @param ProductFactory $productFactory
     * @param MpProductFactory $mpProductFactory
     * @param ShipAreaFactory $shipAreaFactory
     * @param SellerFactory $sellerFactory
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        EncryptorInterface $encryptor,
        JsonHelper $jsonHelper,
        ProductFactory $productFactory,
        MpProductFactory $mpProductFactory,
        ShipAreaFactory $shipAreaFactory,
        SellerFactory $sellerFactory = null
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
        $this->encryptor = $encryptor;
        $this->jsonHelper = $jsonHelper;
        $this->productFactory = $productFactory;
        $this->mpProductFactory = $mpProductFactory;
        $this->shipAreaFactory = $shipAreaFactory;
        $this->sellerFactory = $sellerFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
                                                ->create(SellerFactory::class);
    }

    /**
     * Get Field Value for HyperLocal Configuration
     */
    public function getHyperLocalConfig($field)
    {
        return $this->scopeConfig->getValue('mphyperlocal/general_settings/'.$field);
    }

    /**
     * Get Enabled value from HyperLocal Configuration
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getHyperLocalConfig('enable');
    }

    /**
     * Get Google API Key from HyperLocal Configuration
     *
     * @return string
     */
    public function getGoogleApiKey()
    {
        $encryptApiKey = $this->getHyperLocalConfig('google_api_key');
        $googleApiKey = $this->encryptor->decrypt($encryptApiKey);
        return $googleApiKey;
    }

    /**
     * Get Popup Window Message from HyperLocal Configuration
     *
     * @return string
     */
    public function getPopupMessage()
    {
        return $this->getHyperLocalConfig('location_popup_window');
    }

    /**
     * Get Product Collection Filter Type from HyperLocal Configuration
     *
     * @return string
     */
    public function getFilterCollectionType()
    {
        return $this->getHyperLocalConfig('show_collection');
    }

    /**
     * Get Radius Value from HyperLocal Configuration
     *
     * @return int
     */
    public function getRadiusValue()
    {
        return $this->getHyperLocalConfig('radious');
    }

    /**
     * Get Radius Unit Value from HyperLocal Configuration
     *
     * @return string
     */
    public function getRadiusUnitValue()
    {
        return $this->getHyperLocalConfig('radious_unit');
    }

    /**
     * Get Admin Address Value from HyperLocal Configuration
     *
     * @return string
     */
    public function getAdminAddress()
    {
        return $this->getHyperLocalConfig('address');
    }

    /**
     * Get Admin Latitude Value from HyperLocal Configuration
     *
     * @return float
     */
    public function getAdminLatitude()
    {
        return $this->getHyperLocalConfig('latitude');
    }

    /**
     * Get Admin Longitude Value from HyperLocal Configuration
     *
     * @return float
     */
    public function getAdminLongitude()
    {
        return $this->getHyperLocalConfig('longitude');
    }
    
    /**
     * Get Formatted Time
     *
     * @return Time
     */
    public function getFormettedTime($time)
    {
        return date('h:i:s a', strtotime($time));
    }

    /**
     * getSavedAddress
     *
     * @return array
     */
    public function getSavedAddress()
    {
        $location = [];
        if ($this->isEnabled()) {
            $cachedAddress = $this->cookieManager->getCookie('hyper_local') ?? "[]";
            $setAddress = $this->jsonHelper->jsonDecode($cachedAddress);
            if (!empty($setAddress)) {
                $location['latitude'] = $setAddress['lat'];
                $location['longitude'] = $setAddress['lng'];
                $location['address'] = $setAddress['address'];
                $location['city'] = $setAddress['city'] ?? '';
                $location['state'] = $setAddress['state'] ?? '';
                $location['country'] = $setAddress['country'] ?? '';
            }
        }
        return $location;
    }

    /**
     * getDistanceFromTwoPoints
     * @param string $from
     * @param string $to
     * @return float $d
     */
    public function getDistanceFromTwoPoints($from, $to, $radiousUnit)
    {
        $R = 6371; // km
        $dLat = ($from['latitude'] - $to['latitude']) * M_PI / 180;
        $dLon = ($from['longitude'] - $to['longitude']) * M_PI / 180;
        $lat1 = $to['latitude'] * M_PI / 180;
        $lat2 = $from['latitude'] * M_PI / 180;
     
        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = $R * $c;
        if ($radiousUnit == 'mile') {
            $m = $d * 0.621371; //for milles
            return $m;
        }
        return $d;
    }

    /**
     * getSellersProducts
     * @param array $sellerIds
     * @return array
     */
    public function getNearestProducts($sellerIds)
    {
        $adminProList = [];
        $mpProColl = $this->mpProductFactory->create()->getCollection()
                        ->addFieldToFilter('seller_id', ['in' => $sellerIds])
                        ->getColumnValues('mageproduct_id');
        
        if (in_array(null, $sellerIds, true)) {
            $adminProList = $this->getAdminProducts();
        }
        $allowedProList = array_merge($mpProColl, $adminProList);
        
        return empty($allowedProList) ? [0] : $allowedProList;
    }

    /**
     * getAdminProducts
     * @return array
     */
    public function getAdminProducts()
    {
        $sellerProList = $this->mpProductFactory->create()->getCollection()
                            ->getColumnValues('mageproduct_id');
        $collection = $this->productFactory->create()->getCollection();
        
        if (!empty($sellerProList)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $sellerProList]);
        }
        $adminProList = $collection->getColumnValues('entity_id');

        return empty($adminProList) ? [0] : $adminProList;
    }

    /**
     * getNearestSellers
     * @return array
     */
    public function getNearestSellers()
    {
        $collectionFilterOption = $this->getFilterCollectionType();
        $sellerIds = [0];
        if ($collectionFilterOption == 'address') {
            $collection = $this->shipAreaFactory->create()->getCollection();
            $allowedAddress = $this->getAllowedAddress();
            $collectionArray = [];
            foreach ($allowedAddress as $key => $value) {
                if ($value) {
                    $collectionArray[] = $this->shipAreaFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('address_type', $key)
                    ->addFieldToFilter('address', ['like' => '%'.$value.'%'])->getSelect();
                }
            }
            if (count($collectionArray) == 3) {
                $collection->getSelect()->reset();
                $collection->getSelect()->union([$collectionArray[0], $collectionArray[1], $collectionArray[2]]);
            } elseif (count($collectionArray) == 2) {
                $collection->getSelect()->reset();
                $collection->getSelect()->union([$collectionArray[0], $collectionArray[1]]);
            } elseif (count($collectionArray) == 1) {
                $collection->getSelect()->reset();
                $collection->getSelect()->union([$collectionArray[0]]);
            }
            foreach ($collection as $shipArea) {
                $sellerIds[] = $shipArea->getSellerId();
            }
        } else {
            $sellerCollection = $this->sellerFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        ['latitude','longitude'],
                                        [
                                            ['neq'=> 'NULL'],
                                            ['neq'=> 'NULL']
                                        ]
                                    );
            $shipArea = [
                'latitude' => $this->getAdminLatitude(),
                'longitude' => $this->getAdminLongitude()
            ];
            if($this->isInRadious($shipArea)) {
                $sellerIds[] = NULL;
            }
            foreach ($sellerCollection as $sellerData) {
                $shipArea['latitude'] = $sellerData->getLatitude();
                $shipArea['longitude'] = $sellerData->getLongitude();
                if($this->isInRadious($shipArea)) {
                    $sellerIds[] = $sellerData->getSellerId();
                }
            }
        }
        return array_unique($sellerIds);
    }

    public function getAllowedAddress()
    {
        $address = $this->getSavedAddress();
        return [
            'city'    => $address['city'] ?? '',
            'state'   => $address['state'] ?? '',
            'country' => $address['country'] ?? '' 
        ];
    }

    /**
     * isInRadious
     * @param \Webkul\MpHyperLocal\Model\ShipArea $shipArea
     * @return bool
     */
    public function isInRadious($shipArea)
    {
        $distance = 0;
        $radious = $this->scopeConfig->getValue('mphyperlocal/general_settings/radious');
        $radiousUnit = $this->scopeConfig->getValue('mphyperlocal/general_settings/radious_unit');
        $to['latitude'] = $shipArea['latitude'];
        $to['longitude'] = $shipArea['longitude'];
        $savedAddress = $this->getSavedAddress();
        if ($savedAddress) {
            $distance = $this->getDistanceFromTwoPoints($savedAddress, $to, $radiousUnit);
        }
        return $radious >= $distance;
    }

    /**
     * isSellerAvilableInSavedLocation
     * @param int $sellerId
     * @return boolean
     */
    public function isSellerAvilableInSavedLocation($sellerId)
    {
        $sellerlist = $this->getNearestSellers();
        return in_array($sellerId, $sellerlist);
    }
}
