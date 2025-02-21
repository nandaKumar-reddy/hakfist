<?php

namespace Hakfist\MyAccount\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Psr\Log\LoggerInterface;

class DeleteMember extends \Magento\Framework\App\Action\Action
{
    protected $customerRepository;
    protected $resultRedirectFactory;
    protected $logger;

    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        RedirectFactory $resultRedirectFactory,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $customer->setCustomAttribute('is_deleted', 'Inactive'); // Mark as deleted
                $this->customerRepository->save($customer);

                $this->messageManager->addSuccessMessage(__('The team member has been deleted.'));
            } catch (\Exception $e) {
                $this->logger->error('Error marking team member as deleted: ' . $e->getMessage());
                $this->messageManager->addErrorMessage(__('An error occurred while trying to mark the team member as deleted.'));
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/profilesetting');
    }
}
