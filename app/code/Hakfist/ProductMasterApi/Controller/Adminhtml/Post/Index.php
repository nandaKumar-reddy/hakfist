<?php

namespace Hakfist\ProductMasterApi\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Hakfist_ProductMasterApi::productmasterapi');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Hakfist_ProductMasterApi::productmasterapi');
        $resultPage->addBreadcrumb(__('ProductSync Log'), __('ProductSync Log'));
        $resultPage->addBreadcrumb(__('Manage ProductSync Log'), __('Manage ProductSync Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage ProductSync Log'));

        return $resultPage;
    }
}
