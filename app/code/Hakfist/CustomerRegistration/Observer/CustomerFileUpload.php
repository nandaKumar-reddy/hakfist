<?php

namespace Hakfist\CustomerRegistration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerFileUpload implements ObserverInterface
{

    protected $directoryList;
    protected $urlBuilder;
    protected $customerRepository;

    public function __construct(
        DirectoryList $directoryList,
        UrlInterface $urlBuilder,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->directoryList = $directoryList;
        $this->urlBuilder = $urlBuilder;
        $this->customerRepository = $customerRepository;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer(); // Get the customer object
    
        // Define allowed file types
        $allowedFileTypes = array("pdf", "docx");
    
        // Process customer_document upload
        if (isset($_FILES['customer']) && isset($_FILES['customer']['name']['customer_document'])) {
            try {
                $targetDir = $this->directoryList->getPath(DirectoryList::MEDIA) . '/customer/W/i/';
                $customerId = $customer->getId();
                $originalFileName = basename($_FILES['customer']['name']['customer_document']);
                $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
                $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
                $newFileName = $fileNameWithoutExtension . '_' . $customerId . '.' . $fileExtension;
                $targetFile = $targetDir . $newFileName;
    
                if (!in_array($fileExtension, $allowedFileTypes)) {
                    echo "Sorry, only PDF and DOCX files are allowed for customer document.";
                    return;
                }
    
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
    
                if ($_FILES['customer']['error']['customer_document'] !== UPLOAD_ERR_OK) {
                    throw new \Exception('File upload error code for customer document: ' . $_FILES['customer']['error']['customer_document']);
                }
    
                if (move_uploaded_file($_FILES['customer']['tmp_name']['customer_document'], $targetFile)) {
                    $crop = strpos($targetFile, '/W/');
                    $dbString = substr($targetFile, $crop);
                    $customer->setCustomAttribute('customer_document', $dbString);
                    $this->customerRepository->save($customer);
                } else {
                    throw new \Exception('Failed to move uploaded customer document.');
                }
            } catch (\Exception $e) {
                echo 'Error with customer document: ' . $e->getMessage();
            }
        }
    
        // Process trn_number upload
        if (isset($_FILES['customer']) && isset($_FILES['customer']['name']['trn_number'])) {
            try {
                $targetDir = $this->directoryList->getPath(DirectoryList::MEDIA) . '/customer/W/i/';
                $customerId = $customer->getId();
                $originalFileName = basename($_FILES['customer']['name']['trn_number']);
                $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
                $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
                $newFileName = $fileNameWithoutExtension . '_' . $customerId . '.' . $fileExtension;
                $targetFile = $targetDir . $newFileName;
    
                if (!in_array($fileExtension, $allowedFileTypes)) {
                    echo "Sorry, only PDF and DOCX files are allowed for TRN document.";
                    return;
                }
    
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
    
                if ($_FILES['customer']['error']['trn_number'] !== UPLOAD_ERR_OK) {
                    throw new \Exception('File upload error code for TRN document: ' . $_FILES['customer']['error']['trn_number']);
                }
    
                if (move_uploaded_file($_FILES['customer']['tmp_name']['trn_number'], $targetFile)) {
                    $crop = strpos($targetFile, '/W/');
                    $dbString = substr($targetFile, $crop);
                    $customer->setCustomAttribute('trn_number', $dbString);
                    $this->customerRepository->save($customer);
                } else {
                    throw new \Exception('Failed to move uploaded TRN document.');
                }
            } catch (\Exception $e) {
                echo 'Error with TRN document: ' . $e->getMessage();
            }
        }
    
        if (!isset($_FILES['customer']['name']['customer_document']) && !isset($_FILES['customer']['name']['trn_number'])) {
            echo "No file uploaded.";
        }
    }
    
}
