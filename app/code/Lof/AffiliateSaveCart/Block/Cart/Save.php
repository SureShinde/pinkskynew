<?php

namespace Lof\AffiliateSaveCart\Block\Cart;

class Save extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'cart/save.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Lof\AffiliateSaveCart\Helper\Data $saveCartHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->saveCartHelper = $saveCartHelper;
    }
    /**
     * Return the save action Url.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('affiliatesavecart/cart/save');
    }

    public function getAffiliateSaveCartLinkConfig()
    {
        return \Zend_Json::encode([
            'saveCartLinkUrl' => $this->getAction()
        ]);
    }
    public function _toHtml()
    {
        $enabled = $this->saveCartHelper->getConfig('affiliatesavecart/enable');
        $enabled_cart_button = $this->saveCartHelper->getConfig('affiliatesavecart/enable_save_cart_button');
        if($enabled && $enabled_cart_button){
            return parent::_toHtml();
        } else {
            return ;
        }
    }
}
