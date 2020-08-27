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

namespace Lof\SocialLogin\Block\SocialLogin;

use Lof\SocialLogin\Block\SocialLogin;

class Steam extends SocialLogin
{
    /**
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->helperData()->isEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return \Lof\SocialLogin\Helper\Steam\Data
     */
    protected function helperSteam()
    {
        return $this->objectManager->create('Lof\SocialLogin\Helper\Steam\Data');
    }

    /**
     * @return \Lof\SocialLogin\Helper\Data
     */
    protected function helperData()
    {
        return $this->objectManager->create('Lof\SocialLogin\Helper\Data');
    }

    /**
     * @return \Lof\SocialLogin\Model\Steam
     */
    protected function steamModel()
    {
        return $this->objectManager->create('Lof\SocialLogin\Model\Steam');
    }

    /**
     * retrive form login url
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->steamModel()->getSteamLoginUrl();
    }
}
