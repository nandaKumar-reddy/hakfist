<?php

namespace Hakfist\MyAccount\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Controller\Account\Index as CustomerIndex;

class Index extends CustomerIndex
{
    public function execute()
    {
        // Custom logic here
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Account'));
        return $resultPage;
    }
}
