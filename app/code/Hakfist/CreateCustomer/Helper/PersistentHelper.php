<?php

namespace Hakfist\CreateCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class PersistentHelper extends AbstractHelper
{
    const COOKIE_LIFETIME = 86400 * 30; // 30 days

    protected $cookieMetadataFactory;
    protected $cookieManager;

    public function __construct(
        Context $context,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        parent::__construct($context);
    }

    public function setRememberMeCookie($value)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_LIFETIME)
            ->setPath('/')
            ->setHttpOnly(true);
        $this->cookieManager->setPublicCookie('remember_me', $value, $metadata);
    }

    public function deleteRememberMeCookie()
    {
        $this->cookieManager->deleteCookie('remember_me');
    }

    public function isRemembered()
    {
        return $this->cookieManager->getCookie('remember_me') === 'true';
    }
}
