<?php

namespace Hakfist\CustomerRegistration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Hakfist\CustomerRegistration\Helper\CustomerRegistration as CustomerRegistrationHelper;

class CustomerRegisterSuccess implements ObserverInterface
{
    protected $jsonHelper;
    protected $customerRegistrationHelper;

    public function __construct(JsonHelper $jsonHelper, CustomerRegistrationHelper $customerRegistrationHelper)
    {
        $this->jsonHelper = $jsonHelper;
        $this->customerRegistrationHelper = $customerRegistrationHelper;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $customerData = [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
    
        ];

        $customerJsonData = $this->jsonHelper->jsonEncode($customerData);

       // $this->logCustomerData($customerJsonData);

        // Call the AddCustomerInOddo helper function
        $accountInfo = $this->customerRegistrationHelper->AddCustomerInOddo($customerData);
    }

    private function logCustomerData($data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customer_registration.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($data);
    }
}
