<?php

namespace Hakfist\CreateCustomer\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\Action\Action;


class Create extends Action

{

    protected $_pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        return $this->_pageFactory->create();

    }
}
