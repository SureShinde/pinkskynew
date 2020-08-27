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
namespace Lof\SocialLogin\Controller\Google;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Session;
use Lof\SocialLogin\Model\Google;
use Lof\SocialLogin\Helper\Google\Data as HelperGoogle;
use Lof\SocialLogin\Helper\Data as HelperData;

class Login extends Action
{
    protected $resultPageFactory;
    protected $google;
    protected $helperData;
    protected $helperGoogle;
    protected $accountManagement;
    protected $customerUrl;
    protected $session;


    public function __construct(
        Context $context,
        Google $google,
        StoreManagerInterface $storeManager,
        HelperGoogle $helperGoogle,
        HelperData $helperData,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        Session $customerSession
    ) {

        parent::__construct($context);
        $this->google            = $google;
        $this->storeManager      = $storeManager;
        $this->helperData        = $helperData;
        $this->helperGoogle      = $helperGoogle;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->customerUrl       = $customerUrl;
        $this->session           = $customerSession;
    }

    public function execute()
    {
        if ($this->helperData->isEnabled()) {
            if (!$this->getAuthorizedToken()) {
                $token = $this->getAuthorization();
            } else {
                $token = $this->getAuthorizedToken();
            }
            return $token;
        }
        return false;
    }

    public function getAuthorizedToken()
    {
        $token = false;
        if (!is_null($this->session->getAccessToken())) {
            $token = unserialize($this->session->getAccessToken());
        }
        return $token;
    }

    public function getAuthorization()
    {
        $scope = [
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email'
            ];
        $this->google->setScopes($scope);
        $this->google->authenticate();

        $authUrl = $this->google->createAuthUrl();
        header('Localtion: ' . $authUrl);
        die(0);
    }
}
