<?php

namespace Hakfist\CreateCustomer\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerFactory;
use Hakfist\CreateCustomer\Controller\Index\EmailValidation;
use Magento\Framework\Exception\LocalizedException;

class Process extends Action
{
    protected $resultJsonFactory;
    protected $customerRepository;
    protected $customerDataFactory;
    protected $dataObjectHelper;
    protected $accountManagement;
    protected $encryptor;
    protected $storeManager;
    protected $directoryList;
    protected $groupRepository;
    protected $searchCriteriaBuilder;
    protected $accountManagementInterface;
    protected $customerFactory;
    protected $emailValidation;


    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        AccountManagementInterface $accountManagement,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AccountManagementInterface $accountManagementInterface,
        CustomerFactory $customerFactory,
        EmailValidation $emailValidation,

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountManagement = $accountManagement;
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->accountManagementInterface = $accountManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->emailValidation = $emailValidation;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPostValue();

            try {
                // Sanitize and process form data
                $businessName = htmlspecialchars($formData['business_name'] ?? '');
                $addressLine = htmlspecialchars($formData['address_line'] ?? '');
                $companyEmail = htmlspecialchars($formData['company_email'] ?? '');
                $trnNumber = htmlspecialchars($formData['trn_number'] ?? '');
                $streetAddress = htmlspecialchars($formData['address_line2'] ?? '');
                $companyNumber = htmlspecialchars($formData['company_number'] ?? '');
                $taxCertificate = isset($_FILES['tax_certificate']) ? $_FILES['tax_certificate'] : null;
                $city = htmlspecialchars($formData['city'] ?? '');
                $companyWebsite = htmlspecialchars($formData['company_website'] ?? '');
                $regNumber = htmlspecialchars($formData['reg_number'] ?? '');
                $country = htmlspecialchars($formData['country'] ?? '');
                $phoneNumber = htmlspecialchars($formData['phone_number'] ?? '');
                $pocName = htmlspecialchars($formData['poc_number'] ?? '');
                $pocDesignation = htmlspecialchars($formData['poc_designation'] ?? '');
                $pocEmail = htmlspecialchars($formData['poc_email'] ?? '');
                $category = htmlspecialchars($formData['category'] ?? '');
                $businessTurnover = htmlspecialchars($formData['business_turnover'] ?? '');
                $teamNames = $formData['team_name'] ?? [];
                $teamNumbers = $formData['team_number'] ?? [];
                $teamEmails = $formData['team_email'] ?? [];
                $teamDesignations = $formData['team_designation'] ?? [];
                //$otp = htmlspecialchars($formData['otp'] ?? '');



                $teamMembers = [];
                for ($i = 0; $i < count($teamNames); $i++) {
                    $teamMembers[] = [
                        'team_name' => htmlspecialchars($teamNames[$i] ?? ''),
                        'team_number' => htmlspecialchars($teamNumbers[$i] ?? ''),
                        'team_email' => htmlspecialchars($teamEmails[$i] ?? ''),
                        'team_designation' => htmlspecialchars($teamDesignations[$i] ?? ''),
                    ];
                }
                //$this->emailValidation->generateOtp($companyEmail);
                //$this->emailValidation->validateOtp($companyEmail);
                $this->createCustomer($companyEmail, $businessName, $formData, $taxCertificate);

                $response = [
                    'status' => 'success',
                    'message' => 'Customer account created successfully',
                    'formData' => [
                        'businessName' => $businessName,
                        'addressLine' => $addressLine,
                        'companyEmail' => $companyEmail,
                        'trnNumber' => $trnNumber,
                        'addressLine2' => $streetAddress,
                        'companyNumber' => $companyNumber,
                        'taxCertificate' => $taxCertificate,
                        'city' => $city,
                        'companyWebsite' => $companyWebsite,
                        'regNumber' => $regNumber,
                        'country' => $country,
                        'phoneNumber' => $phoneNumber,
                        'pocNumber' => $pocName,
                        'pocDesignation' => $pocDesignation,
                        'pocEmail' => $pocEmail,
                        'category' => $category,
                        'businessTurnover' => $businessTurnover,
                        'teamMembers' => $teamMembers,
                    ],
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }

            return $result->setData($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Invalid request method',
            ];

            return $result->setData($response);
        }
    }

    private function createCustomer($companyEmail, $businessName, $formData, $taxCertificate)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerData = $this->customerFactory->create();
        $customerData->setWebsiteId($websiteId);
        $customerData->setEmail($companyEmail);
        $customerData->setFirstname($businessName);
        $customerData->setLastname($businessName);
        $customerData->setPassword("Hakfist@2024");
        // Set the customer group ID
        $customerGroupId = $this->getCustomerGroupIdByName('Retailer');
        if ($customerGroupId) {
            $customerData->setGroupId($customerGroupId);
        } else {
            throw new \Exception('Customer group "Retailer" not found.');
        }
        $customerData->save();
        $customerId = $customerData->getEntityId();
        $this->updateCustomerAttribute($formData, $taxCertificate, $customerId);
        //$customerData->sendNewAccountEmail();
        //$this->sendResetPasswordLink($companyEmail);
        // Get the retailer customer ID
        $retailerCustomerId = $customerData->getEmail();
        // Create sales representative accounts
        $teamNames = $formData['team_name'] ?? [];
        $teamNumbers = $formData['team_number'] ?? [];
        $teamEmails = $formData['team_email'] ?? [];
        $teamDesignations = $formData['team_designation'] ?? [];
        for ($i = 0; $i < count($teamNames); $i++) {
            $this->createSalesRepresentative($teamNames[$i], $teamEmails[$i], $teamNumbers[$i], $teamDesignations[$i], $retailerCustomerId);
        }
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
        return null; // Or handle the case where the group is not found
    }

    private function createSalesRepresentative($name, $email, $phoneNumber, $designation, $retailerCustomerId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $salesRepData = $this->customerFactory->create();
        $salesRepData->setWebsiteId($websiteId);
        $salesRepData->setEmail($email);
        $salesRepData->setFirstname($name);
        $salesRepData->setLastname($name);
        $salesRepData->setPassword("Hakfist@2024");

        // Set the customer group ID for sales representatives
        $customerGroupId = $this->getCustomerGroupIdByName('Salesrepresentative');
        if ($customerGroupId) {
            $salesRepData->setGroupId($customerGroupId);
        } else {
            throw new \Exception('Customer group "Salesrepresentative" not found.');
        }

        $salesRepData->save();
        $customerId = $salesRepData->getEntityId();
        $salesRepData = $this->customerRepository->getById($customerId);
        // Set custom attributes
        $salesRepData->setCustomAttribute('team_number', htmlspecialchars($phoneNumber));
        $salesRepData->setCustomAttribute('team_designation', htmlspecialchars($designation));
        $salesRepData->setCustomAttribute('assigned_manager', $retailerCustomerId);
        $this->customerRepository->save($salesRepData);
        //$this->sendResetPasswordLink($email);

    }

    private function sendResetPasswordLink($emailAddress)
    {
        $message = "";
        try {
            $this->accountManagementInterface->initiatePasswordReset($emailAddress, AccountManagement::EMAIL_RESET);
        } catch (NoSuchEntityException $e) {
            // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            $message = 'We\'re unable to send the password reset email.!';
        }
        return $message;
    }

    private function updateCustomerAttribute($formData, $taxCertificate, $customerId)
    {
        $customerData = $this->customerRepository->getById($customerId);
        $customerData->setCustomAttribute('trn_number', htmlspecialchars($formData['trn_number'] ?? ''));
        $customerData->setCustomAttribute('address_line', htmlspecialchars($formData['address_line'] ?? ''));
        $customerData->setCustomAttribute('address_line2', htmlspecialchars($formData['address_line2'] ?? ''));
        $customerData->setCustomAttribute('company_number', htmlspecialchars($formData['company_number'] ?? ''));
        $customerData->setCustomAttribute('city', htmlspecialchars($formData['city'] ?? ''));
        $customerData->setCustomAttribute('company_website', htmlspecialchars($formData['company_website'] ?? ''));
        $customerData->setCustomAttribute('reg_number', htmlspecialchars($formData['reg_number'] ?? ''));
        $customerData->setCustomAttribute('country_billing', htmlspecialchars($formData['country'] ?? ''));
        $customerData->setCustomAttribute('phone_number', htmlspecialchars($formData['phone_number'] ?? ''));
        $customerData->setCustomAttribute('poc_number', htmlspecialchars($formData['poc_number'] ?? ''));
        $customerData->setCustomAttribute('poc_designation', htmlspecialchars($formData['poc_designation'] ?? ''));
        $customerData->setCustomAttribute('poc_email', htmlspecialchars($formData['poc_email'] ?? ''));
        $customerData->setCustomAttribute('category', htmlspecialchars($formData['category'] ?? ''));
        $customerData->setCustomAttribute('business_turnover', htmlspecialchars($formData['business_turnover'] ?? ''));
        $customerData->setCustomAttribute('tax_certificate', $this->processFileUpload($taxCertificate));
        $this->customerRepository->save($customerData);
    }

    public function processFileUpload($taxCertificate)
    {

        $allowedFileTypes = ['pdf', 'docx'];
        if ($taxCertificate && isset($taxCertificate['name'])) {
            try {
                $targetDir = $this->directoryList->getPath(DirectoryList::MEDIA) . '/customer/tax_certificates/';
                $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'customer/tax_certificates/';
                $originalFileName = basename($taxCertificate['name']);
                $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
                $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
                $newFileName = $fileNameWithoutExtension . '_' . '2200' . '.' . $fileExtension;
                $targetFile = $targetDir . $newFileName;

                if (!in_array($fileExtension, $allowedFileTypes)) {
                    throw new \Exception('Only PDF and DOCX files are allowed for tax certificates.');
                }

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                if ($taxCertificate['error'] !== UPLOAD_ERR_OK) {
                    throw new \Exception('File upload error code for tax certificate: ' . $taxCertificate['error']);
                }

                if (move_uploaded_file($taxCertificate['tmp_name'], $targetFile)) {
                    $crop = strpos($targetFile, '/tax_certificates/');

                    $dbString = substr($targetFile, $crop);
                    return $dbString;
                } else {
                    throw new \Exception('Failed to move uploaded tax certificate.');
                }
            } catch (\Exception $e) {
                echo 'Error with tax certificate: ' . $e->getMessage();
            }
        }
    }
}
