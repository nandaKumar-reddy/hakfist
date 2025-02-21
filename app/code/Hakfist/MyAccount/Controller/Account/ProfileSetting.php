<?php

namespace Hakfist\MyAccount\Controller\Account;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Psr\Log\LoggerInterface; // Import the LoggerInterface
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProfileSetting extends \Magento\Customer\Controller\AbstractAccount
{
    protected $resultPageFactory;
    protected $customerCollectionFactory;
    protected $resultJsonFactory;
    protected $logger;
    protected $customerSession;
    protected $groupRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        CustomerSession $customerSession,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder

    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->getTeamDetails();
    }

    public function getTeamDetails()
    {
        $customer = $this->customerSession->getCustomer();
        // Manager email and Salesrepresentative group id
        $managerEmail = $customer->getEmail();
        $salesRepGroupId = $this->getCustomerGroupIdByName('Salesrepresentative');
        if ($salesRepGroupId === null) {
            // Handle the case where the group ID was not found
            return $this->resultJsonFactory->create()->setData([
                'error' => true,
                'message' => 'Salesrepresentative group not found.'
            ]);
        }

        // Get the customer collection
        $collection = $this->customerCollectionFactory->create();
        $collection->addFieldToFilter('group_id', $salesRepGroupId)
            ->addFieldToFilter('assigned_manager', $managerEmail)
            ->addFieldToFilter('is_deleted', ['null' => true]);
       
        $teamMembers = [];
        foreach ($collection as $customer) {
            $customer->load($customer->getId());
            $teamMembers[] = [
                'email' => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'team_number' => $customer->getData('team_number') ? $customer->getData('team_number') : null,
                'team_designation' => $customer->getData('team_designation') ? $customer->getData('team_designation') : null,
                'customer_id' => $customer->getId(),

            ];
        }


        // if (empty($teamMembers)) {
        //     return $this->resultJsonFactory->create()->setData([
        //         'error' => true,
        //         'message' => 'No team members found.'
        //     ]);
        // }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('profilesetting.block')->setData('team_members', $teamMembers);

        return $resultPage;
    }

    private function getCustomerGroupIdByName($groupName)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('customer_group_code', $groupName, 'eq')->create();
        $groupList = $this->groupRepository->getList($searchCriteria);
        $groups = $groupList->getItems();

        if (!empty($groups)) {
            $group = reset($groups);
            return $group->getId();
        }

        return null; 
    }
}
