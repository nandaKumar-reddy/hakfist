<?php
namespace Hakfist\CustomerRegistration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SaveCustomerAddress implements ObserverInterface
{
    protected $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $request = $observer->getEvent()->getRequest();

        // Get the state value from the request
        $state = $request->getParam('region_id');

        // Save the state attribute to the customer
        $customer->setCustomAttribute('state', $state);

        // Save the customer with the new attribute value
        $this->customerRepository->save($customer);
    }
}
