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
namespace Webkul\MpHyperLocal\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\MpHyperLocal\Helper\Data;

class AddressForm extends Template
{
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Get Save Action URL
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('mphyperlocal/index/setaddress', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get Hyperlocal Helper
     *
     * @return object $helper
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
