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

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Magento\Sales\Model\Order\ConfigFactory $orderConfigFactory,
        \Magento\Framework\Model\Context $context
    ) {
        $this->orderConfigFactory = $orderConfigFactory;
        $this->context            = $context;
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $statuses = $this->orderConfigFactory->create()->getStatuses();
            foreach ($statuses as $id => $status) {
                $this->options[] = ['value' => $id, 'label' => $status];
            }
        }

        return $this->options;
    }
}
