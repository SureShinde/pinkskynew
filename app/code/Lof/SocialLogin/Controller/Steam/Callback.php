<?php

namespace Lof\SocialLogin\Controller\Steam;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lof\SocialLogin\Model\Steam as Steam;
use Lof\SocialLogin\Helper\Steam\Data as SocialHelper;
use Lof\SocialLogin\Model\SocialFactory as SteamModelFactory;
use Lof\SocialLogin\Model\ResourceModel\Social\CollectionFactory as SteamCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Controller\ResultFactory;

class Callback extends Action
{
    const SOCIAL_TYPE = 'steam';
    protected $resultPageFactory;
    protected $steam;
    protected $socialHelper;
    protected $accountManagement;
    protected $customerUrl;
    protected $session;
    protected $steamCustomerCollectionFactory;
    protected $steamCustomerModelFactory;
    protected $customerFactory;
    protected $curl;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;


    public function __construct(
        Context $context,
        Steam $steam,
        StoreManagerInterface $storeManager,
        SocialHelper $socialHelper,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        SteamModelFactory $steamCustomerModelFactory,
        SteamCollectionFactory $steamCustomerCollectionFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        Visitor $visitor,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        parent::__construct($context);
        $this->steam                          = $steam;
        $this->storeManager                      = $storeManager;
        $this->socialHelper                      = $socialHelper;
        $this->resultPageFactory                 = $resultPageFactory;
        $this->accountManagement                 = $accountManagement;
        $this->customerUrl                       = $customerUrl;
        $this->session                           = $customerSession;
        $this->steamCustomerModelFactory      = $steamCustomerModelFactory;
        $this->steamCustomerCollectionFactory = $steamCustomerCollectionFactory;
        $this->customerFactory                   = $customerFactory;
        $this->customerRepository                = $customerRepository;
        $this->visitor                           = $visitor;
        $this->curl = $curl;
    }

    public function execute()
    {
        $redirect_uri = $this->socialHelper->getAuthUrl();
        $apiKey = $this->socialHelper->getApiKey();

        $steamIdentity = $_REQUEST['openid_identity'];
        preg_match('/(\d+)(?!.*\d)/m' , $steamIdentity, $steamIdentity);
        $steamID = $steamIdentity[0];

        if (! $steamID) {
            die('Warning! Visitor may have declined access or navigated to the page without being redirected.');
        }

        $request = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?';
        $request .= 'key=' . $apiKey;
        $request .= '&steamids=' . $steamID;
        $this->curl->get($request);
        $result = json_decode($this->curl->getBody());
        $players = $result->response->players;
        $userData = $players[0];

        $redirect = $this->socialHelper->getConfig(('general/redirect_page'));
        if(empty($redirect)){
            $redirect = $this->_redirect->getRefererUrl();
        }

        $customerId = $this->getCustomerIdBySteamId($steamID);
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customer1 = $this->customerFactory->create()->load($customerId);
            if ($customer->getConfirmation()) {
                try {
                    $customer1->setConfirmation(null);
                    $customer1->save();
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
                }
            }
            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
            $this->session->setCustomerDataAsLoggedIn($customer);
            $this->messageManager->addSuccess(__('Login successful.'));
            $this->session->regenerateId(); 
            $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
            $this->_eventManager->dispatch('customer_login', ['customer' => $customer1]);

            /** VISITOR */
            $visitor = $this->visitor;
            $visitor->setData($this->session->getVisitorData());
            $visitor->setLastVisitAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
            $visitor->setSessionId($this->session->getSessionId());
            $visitor->save();
            $this->_eventManager->dispatch('visitor_init', ['visitor' => $visitor]);
            $this->_eventManager->dispatch('visitor_activity_save', ['visitor' => $visitor]);
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($redirect);
            return $resultRedirect;
        }
        if ($userData) {
            $data['id'] = $steamID;
            $data['email'] = $userData->personaname . '@steam.com';
            $data['password'] =  $this->socialHelper->createPassword();
            $data['password_confirmation'] = $data['password'];
            $data['first_name'] = $userData->personaname;
            $data['last_name']  = $userData->personaname;

            $store_id   = $this->storeManager->getStore()->getStoreId();
            $website_id = $this->storeManager->getStore()->getWebsiteId();
            $customer   = $this->socialHelper->getCustomerByEmail($data['email'], $website_id);
            if (!$customer || !$customer->getId()) {
                $customer = $this->socialHelper->createCustomerMultiWebsite($data, $website_id, $store_id);
                if ($this->socialHelper->sendPassword()) {
                    try {
                        $this->accountManagement->sendPasswordReminderEmail($customer);
                    } catch (Exception $e) {
                        $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
                    }
                }
            }
            $this->setAuthorCustomer($data['id'], $customer->getId(), $userData->personaname);
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __( 
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $data['email']
                    )
                );
            } else {
                $this->session->setCustomerDataAsLoggedIn($customer);
                $this->messageManager->addSuccess(__('Login successful.'));
                $this->session->regenerateId();
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($redirect);
            return $resultRedirect;
        }
    }

    public function getCustomerIdBySteamId($steamId)
    {
        $customer = $this->steamCustomerCollectionFactory->create();
        $dataUser     = $customer
            ->addFieldToFilter('social_id', $steamId)
            ->addFieldToFilter('type', self::SOCIAL_TYPE)
            ->getFirstItem();
        if ($dataUser && $dataUser->getId()) {
            return $dataUser->getCustomerId();
        } else {
            return null;
        }
    }
    public function setAuthorCustomer($steamId, $customerId, $username)
    {
        $steamCustomer = $this->steamCustomerModelFactory->create();
        $steamCustomer->setData('social_id', $steamId);
        $steamCustomer->setData('username', $username);
        $steamCustomer->setData('customer_id', $customerId);
        $steamCustomer->setData('type', self::SOCIAL_TYPE);
        $steamCustomer->setData('is_send_password_email', $this->socialHelper->sendPassword());
        try {
            $steamCustomer->save();
        } catch (Exception $e) {
            $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
        }
        return;
    }


    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }
}