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

namespace Lof\Affiliate\Model\Config\Source;

class GroupAccount implements \Magento\Framework\Option\ArrayInterface
{

    protected $_groupCollection=null;

    public function __construct(
        \Lof\Affiliate\Model\ResourceModel\GroupAffiliate\CollectionFactory $groupCollection
    ) {
        $this->_groupCollection = $groupCollection;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = array();
        $collection = $this->_groupCollection->create();
        $collection->addFieldToFilter("is_active", 1);
        if($collection->count()){
            foreach ($collection as $_group) {
                $tmp = [];
                $tmp['label'] = $_group->getName(). ' ('.$_group->getCommission() . '%)';
                $tmp['value'] =  $_group->getId();
                $data[] = $tmp;
            }
        }
        return $data;
    }
}