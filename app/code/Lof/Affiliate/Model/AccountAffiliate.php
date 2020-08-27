<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Venustheme
 * @package    Lof_Affiliate
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Lof\Affiliate\Model;

use Magento\Customer\Model\Session;
use Lof\Affiliate\Api\Data\AccountInterface;

class AccountAffiliate extends \Magento\Framework\Model\AbstractModel implements AccountInterface
{
    /**
     * Affiliate's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;


    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_accountlHelper;

    protected $_resource;

    protected $_accountFactory;

    protected $_resourceModel;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Page cache tag
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\Affiliate\Model\ResourceModel\AccountAffiliate $resource,
        \Lof\Affiliate\Model\ResourceModel\AccountAffiliate\Collection $resourceCollection = null,
        \Lof\Affiliate\Model\ResourceModel\AccountAffiliate\CollectionFactory $accountFactory,
        \Magento\Framework\UrlInterface $url,
        \Lof\Affiliate\Helper\Data $accountlHelper,
        Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resourceModel,
        array $data = []
    )
    {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->_accountlHelper = $accountlHelper;
        $this->_accountFactory = $accountFactory;
        $this->_resourceModel = $resourceModel;
        $this->session = $customerSession;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\Affiliate\Model\ResourceModel\AccountAffiliate');
    }

    /**
     * Prevent blocks recursion
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $needle = 'accountaffiliate_id="' . $this->getId() . '"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::beforeSave();
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Make sure that category content does not reference the block itself.')
        );
    }

    public function getGroupType()
    {
        $data = array();

        $table_name = $this->_resourceModel->getTableName('lof_affiliate_group');
        $connection = $this->_resource->getConnection();
        $select = $connection->select()->from(
            ['ce' => $table_name],
            ['group_id', 'name']
        );
        $rows = $connection->fetchAll($select);

        foreach ($rows as $key => $result) {
            $data[$result['group_id']] = $result['name'];
        }

        return $data;
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getGroupId($customer_id = 0)
    {
        if ($this->hasData("group_id")) {
            return $this->getData("group_id");
        } else {
            $account = $this->load($customer_id, 'customer_id');
            $data = $account->getData();
            if (!empty($data)) {
                return $data['group_id'];
            } else {
                return -1;
            }
        }
    }

    public function checkExistAffiliate($customer_id)
    {
        $account = $this->load($customer_id, 'customer_id');
        if ($account->getId()) {
            return true;
        }
        return false;
    }

    public function loadByAttribute($attribute, $value)
    {
        $this->load($value, $attribute);
        return $this;
    }

    public function updateAffiliateInformation($customer, $customerData)
    {
        $customer_id = $customer->getId();

        $affiliateAccount = $this->loadByAttribute('customer_id', $customer_id);

        // create new account if not exists
        if (!$affiliateAccount->getId()) {
            $tracking_code = $this->_accountlHelper->getAffiliateTrackingCode();
            $default_group_id = $this->_accountlHelper->getConfig("general_settings/default_affilate_group", null, 1);
            $auto_active_account = $this->getConfig("general_settings/auto_active_account", null, 0);
            $affiliateAccount->setTrackingCode($tracking_code)->setGroupId((int)$default_group_id);
            $affiliateAccount->setIsActive((int)$auto_active_account);
            $affiliateAccount->setCommissionPaid('0');
            $affiliateAccount->setBalance('0');
        }

        $fullname = $customerData['firstname'] . ' ' . $customerData['lastname'];
        $email = isset($customerData['email']) ? $customerData['email'] : $customer->getEmail();

        $bank_account_name = $bank_account_number = $bank_name = $swift_code = $bank_branch_city = $bank_branch_country_code = $intermediary_bank_code = $intermediary_bank_name = $intermediary_bank_city = $intermediary_bank_country_code = "";

        if (isset($customerData['bank_account_name'])) {
            $bank_account_name = $customerData['bank_account_name'];
        }
        if (isset($customerData['bank_account_number'])) {
            $bank_account_number = $customerData['bank_account_number'];
        }
        if (isset($customerData['bank_name'])) {
            $bank_name = $customerData['bank_name'];
        }
        if (isset($customerData['swift_code'])) {
            $swift_code = $customerData['swift_code'];
        }
        if (isset($customerData['bank_branch_city'])) {
            $bank_branch_city = $customerData['bank_branch_city'];
        }
        if (isset($customerData['bank_branch_country_code'])) {
            $bank_branch_country_code = $customerData['bank_branch_country_code'];
        }
        if (isset($customerData['intermediary_bank_code'])) {
            $intermediary_bank_code = $customerData['intermediary_bank_code'];
        }
        if (isset($customerData['intermediary_bank_name'])) {
            $intermediary_bank_name = $customerData['intermediary_bank_name'];
        }
        if (isset($customerData['intermediary_bank_city'])) {
            $intermediary_bank_city = $customerData['intermediary_bank_city'];
        }
        if (isset($customerData['intermediary_bank_country_code'])) {
            $intermediary_bank_country_code = $customerData['intermediary_bank_country_code'];
        }
        $affiliateAccount->setPaypalEmail($customerData['paypal_email'])
            // ->setSkrillEmail($customerData['skrill_email'])
            ->setReferingWebsite($customerData['refering_website'])
            ->setFirstname($customerData['firstname'])
            ->setLastname($customerData['lastname'])
            ->setFullname($fullname)
            ->setEmail($email)
            ->setCustomerId($customer->getId())
            ->setBankAccountName($bank_account_name)
            ->setBankAccountNumber($bank_account_number)
            ->setBankName($bank_name)
            ->setSwiftCode($swift_code)
            ->setBankBranchCity($bank_branch_city)
            ->setBankBranchCountryCode($bank_branch_country_code)
            ->setIntermediaryBankCode($intermediary_bank_code)
            ->setIntermediaryBankName($intermediary_bank_name)
            ->setIntermediaryBankCity($intermediary_bank_city)
            ->setIntermediaryBankCountryCode($intermediary_bank_country_code);
        $affiliateAccount->save();
        return $affiliateAccount;
    }


    public function updateBalance($amount, $customer)
    {
        $customer_id = $customer->getId();
        $collection = $this->loadByAttribute('customer_id', $customer_id);
        $balance_old = $collection->getBalance();
        $commission_paid_old = $collection->getCommissionPaid();
        $balance = $balance_old - $amount;
        $commission_paid = $commission_paid_old + $amount;

        $collection->setBalance($balance)
            ->setCommissionPaid($commission_paid)
            ->save();
        return $collection;
    }

    public function checkAccountExist($email)
    {
        $select = $this->getCollection();
        $select->addFieldToFilter('email', $email);
        return (int)count($select);
    }

    public function checkAccExist($email)
    {
        $table_name = $this->_resourceModel->getTableName('customer_entity');
        $connection = $this->_resource->getConnection();
        $select = $connection->select()->from(
            $table_name
        )->where(
            'email = ?',
            $email
        );
        $row = $connection->fetchRow($select);
        return $row;
    }

    public function getAllCustomers()
    {
        $table_name = $this->_resourceModel->getTableName('customer_entity');
        $connection = $this->_resource->getConnection();
        $select = $connection->select()->from($table_name)->where('is_active = 1');
        $rows = $connection->fetchAll($select);

        $customers = array();
        foreach ($rows as $row) {
            $customers[] = array(
                'value' => $row['entity_id'],
                'label' => $row['email']
            );
        }
        return $customers;
    }


    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return parent::getData(self::ACCOUNTAFFILIATE_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ACCOUNTAFFILIATE_ID, $id);
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstName()
    {
        return parent::getData(self::FIRST_NAME);
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setFirstName($firstname)
    {
        return $this->setData(self::FIRST_NAME, $firstname);
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastName()
    {
        return parent::getData(self::LAST_NAME);
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setLastName($lastname)
    {
        return $this->setData(self::LAST_NAME, $lastname);
    }

    /**
     * Get fullname
     *
     * @return string
     */
    public function getFullName()
    {
        return parent::getData(self::FULL_NAME);
    }

    /**
     * Set fullname
     *
     * @param string $fullname
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setFullName($fullname)
    {
        return $this->setData(self::FULL_NAME, $fullname);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get tracking_code
     *
     * @return string
     */
    public function getTrackingCode()
    {
        return parent::getData(self::TRACKING_CODE);
    }

    /**
     * Set tracking_code
     *
     * @param string $tracking_code
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setTrackingCode($tracking_code)
    {
        return $this->setData(self::TRACKING_CODE, $tracking_code);
    }

    /**
     * Get paypal_email
     *
     * @return string
     */
    public function getPaypalEmail()
    {
        return parent::getData(self::PAYPAL_EMAIL);
    }

    /**
     * Set paypal_email
     *
     * @param string $paypal_email
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setPaypalEmail($paypal_email)
    {
        return $this->setData(self::PAYPAL_EMAIL, $paypal_email);
    }

    /**
     * Get skrill_email
     *
     * @return string
     */
    public function getSkrillEmail()
    {
        return parent::getData(self::SKRILL_EMAIL);
    }

    /**
     * Set skrill_email
     *
     * @param string $skrill_email
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setSkrillEmail($skrill_email)
    {
        return $this->setData(self::SKRILL_EMAIL, $skrill_email);
    }

    /**
     * Get balance
     *
     * @return string
     */
    public function getBalance()
    {
        return parent::getData(self::BALANCE);
    }

    /**
     * Set balance
     *
     * @param string $balance
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setBalance($balance)
    {
        return $this->setData(self::BALANCE, $balance);
    }

    /**
     * Get customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     *
     * @param int $customer_id
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    /**
     * Get group_id
     *
     * @return int
     */
    public function getGroupAffiliateId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Set group_id
     *
     * @param int $group_id
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setGroupId($group_id)
    {
        return $this->setData(self::GROUP_ID, $group_id);
    }

    /**
     * Get campaign_code
     *
     * @return string
     */
    public function getCampaignCode()
    {
        return parent::getData(self::CAMPAIGN_CODE);
    }

    /**
     * Set campaign_code
     *
     * @param string $campaign_code
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setCampaignCode($campaign_code)
    {
        return $this->setData(self::CAMPAIGN_CODE, $campaign_code);
    }
}
