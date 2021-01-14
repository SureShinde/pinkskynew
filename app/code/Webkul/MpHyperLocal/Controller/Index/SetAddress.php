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
namespace Webkul\MpHyperLocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class SetAddress extends Action
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'hyper_local';

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadata;

    /**
     * @var Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        HttpContext $httpContext,
        JsonFactory $jsonFactory,
        SessionManagerInterface $sessionManager,
        CookieMetadataFactory $cookieMetadata,
        CookieManagerInterface $cookieManager,
        CheckoutSession $checkoutSession
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->httpContext = $httpContext;
        $this->jsonFactory = $jsonFactory;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadata = $cookieMetadata;
        $this->cookieManager = $cookieManager;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Set Address
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data && $data['lat'] != '' && $data['lng'] != '') {
            $jsonAddressData = $this->jsonHelper->jsonEncode($data);
            $metadata = $this->cookieMetadata->createPublicCookieMetadata()
                ->setDuration(86400)
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain());
            $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $jsonAddressData, $metadata);
            $this->httpContext->setValue(
                'hyperlocal_data',
                $jsonAddressData,
                false
            );
            $this->checkoutSession->getQuote()->delete();
            $result = ['status'=> 1, 'msg' => __('Address Set')];
        } else {
            $result = ['status'=> 0, 'msg' => __('Fill Correct Address.')];
        }
        return $jsonFactory->setData($result);
    }
}
