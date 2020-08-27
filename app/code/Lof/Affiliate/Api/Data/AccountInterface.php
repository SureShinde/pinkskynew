<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Api\Data;

/**
 * Lof Affiliate interface.
 * @api
 * @since 100.0.2
 */
interface AccountInterface
{
    const ACCOUNTAFFILIATE_ID = 'accountaffiliate_id';
    const FIRST_NAME = 'firstname';
    const LAST_NAME = 'lastname';
    const FULL_NAME = 'fullname';
    const EMAIL = 'email';
    const TRACKING_CODE = 'tracking_code';
    const PAYPAL_EMAIL = 'paypal_email';
    const SKRILL_EMAIL = 'skrill_email';
    const BALANCE = 'balance';
    const CUSTOMER_ID = 'customer_id';
    const GROUP_ID = 'group_id';
    const CAMPAIGN_CODE = 'campaign_code';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setId($id);

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setFirstName($firstname);

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setLastName($lastname);

    /**
     * Get fullname
     *
     * @return string
     */
    public function getFullName();

    /**
     * Set fullname
     *
     * @param string $fullname
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setFullName($fullname);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setEmail($email);

    /**
     * Get tracking_code
     *
     * @return string
     */
    public function getTrackingCode();

    /**
     * Set tracking_code
     *
     * @param string $tracking_code
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setTrackingCode($tracking_code);

    /**
     * Get paypal_email
     *
     * @return string
     */
    public function getPaypalEmail();

    /**
     * Set paypal_email
     *
     * @param string $paypal_email
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setPaypalEmail($paypal_email);

    /**
     * Get skrill_email
     *
     * @return string
     */
    public function getSkrillEmail();

    /**
     * Set skrill_email
     *
     * @param string $skrill_email
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setSkrillEmail($skrill_email);

    /**
     * Get balance
     *
     * @return string
     */
    public function getBalance();

    /**
     * Set balance
     *
     * @param string $balance
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setBalance($balance);

    /**
     * Get customer_id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer_id
     *
     * @param int $customer_id
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get group_id
     *
     * @return int
     */
    public function getGroupAffiliateId();

    /**
     * Set group_id
     *
     * @param int $group_id
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setGroupId($group_id);

    /**
     * Get campaign_code
     *
     * @return string
     */
    public function getCampaignCode();

    /**
     * Set campaign_code
     *
     * @param string $campaign_code
     * @return \Lof\Affiliate\Api\Data\AccountInterface
     */
    public function setCampaignCode($campaign_code);
}
