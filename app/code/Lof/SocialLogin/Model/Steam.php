<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SocialLogin
 *
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\SocialLogin\Model;

use Magento\Framework\ObjectManagerInterface;
use Lof\SocialLogin\Helper\Slack\Data as DataHelper;

class Steam
{
    protected $storeManager;

    protected $dataHelper;

    protected $objectManager;

    public function __construct(
        DataHelper $dataHelper,
        ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->dataHelper = $dataHelper;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    public function getSteamLoginUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $lightOpenID = $this->objectManager->create(
            '\Lof\SocialLogin\lib\LightOpenID\LightOpenID',
            ['host' => $baseUrl]
        );

        $lightOpenID->identity = 'http://steamcommunity.com/openid';
        $lightOpenID->returnUrl = $baseUrl . 'lofsociallogin/steam/callback/';
        $lightOpenID->data['openid_return_to'] = $baseUrl . 'lofsociallogin/steam/callback/';
        return $lightOpenID->authUrl();
    }
}
