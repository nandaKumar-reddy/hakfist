<?php

namespace Hakfist\MyAccount\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Order\History as SalesOrderHistory;

class History extends SalesOrderHistory
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        //dd("Order History");
        // Custom logic here
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order History'));
        return $resultPage;
    }
}
