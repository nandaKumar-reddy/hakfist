<?php

namespace Hakfist\CustomerRegistration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Hakfist\CustomerRegistration\Model\OdooCustomerSuccessLogFactory;
use Hakfist\CustomerRegistration\Model\OdooCustomerErrorLogFactory;
use Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerErrorLog as OdooCustomerErrorLogResource;
use Hakfist\CustomerRegistration\Model\ResourceModel\OdooCustomerSuccessLog as OdooCustomerSuccessLogResource;

use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerRegistration extends AbstractHelper
{

    protected $customerRepository;
    protected $odooCustomerSuccessLogFactory;
    protected $odooCustomerErrorLogFactory;
    protected $odooCustomerErrorLogResource;
    protected $odooCustomerSuccessLogResource;

    
    public function __construct(
        Context $context,  
        CustomerRepositoryInterface $customerRepository,
        OdooCustomerSuccessLogFactory $odooCustomerSuccessLogFactory,
        OdooCustomerErrorLogFactory $odooCustomerErrorLogFactory,
        OdooCustomerErrorLogResource $odooCustomerErrorLogResource,
        OdooCustomerSuccessLogResource $odooCustomerSuccessLogResource
        )

    {
        
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->odooCustomerSuccessLogFactory = $odooCustomerSuccessLogFactory;
        $this->odooCustomerErrorLogFactory = $odooCustomerErrorLogFactory;
        $this->odooCustomerErrorLogResource = $odooCustomerErrorLogResource;
        $this->odooCustomerSuccessLogResource = $odooCustomerSuccessLogResource;
    }

    public function AddCustomerInOddo($data)
    {
        $email    = $data["email"];
        $fistName = $data["firstname"];
        $lastName = $data["lastname"];

        $customerArray = [
            "customer" => [
                "email" => $email,
                "firstname" => $fistName,
                "lastname" => $lastName,
                "Tax_ID" => "Not Available",
                "addresses" => [
                    [
                        "defaultShipping" => true,
                        "defaultBilling" => true,
                        "firstname" => $fistName,
                        "lastname" => $lastName,
                        "region" => [
                            "regionCode" => "Not Available",
                            "region" => "Not Available",
                            "regionId" => "Not Available",
                        ],
                        "postcode" => "Not Available",
                        "street" => ["Not Available"],
                        "city" => "Not Available",
                        "telephone" => "Not Available",
                        "countryId" => "Not Available",
                    ],
                ],
            ],
        ];
        // Convert the customer array to a JSON string
        $customerJson = json_encode($customerArray, true);
        $AccountStatus = $this->SubmitAccountInfo($customerJson);

        // Update odoo_status attribute based on the account creation status
     if (isset($data['id'])) {
            $customer = $this->customerRepository->getById($data['id']);
            if ($AccountStatus['error']) {
                $customer->setCustomAttribute('odoo_status', 'Account not created');
                $this->logOdooError($AccountStatus['data'], $data['id'], $email);
            } else {
                $customer->setCustomAttribute('odoo_status', 'Account created');
                // Update the created_in_odoo attribute with the current datetime
                $customer->setCustomAttribute('created_in_odoo', date('Y-m-d')); 
                $this->logOdooSuccess($AccountStatus['data']['id'], $data['id'], $email);
            }
            $this->customerRepository->save($customer);
        }
        
        return $AccountStatus;
    }


    public function SubmitAccountInfo($info)
    {
        $result = [];

        try {
            // /Need to access URL & Accesstoken from backend
            $url = "https://dev.hakfist.com/api/post_customers"; // ODOO API ENDPOINT
            $accessToken = "c34f4c3a535a6d65b990ae645155df9338cbe11f"; // ODOO ACCESS TOKEN
            $header = [
                "Content-Type:application/json",
                "Authorization:Bearer " . $accessToken,
            ];
            $body = $info;
            $mage = $this->curlExec($url, "POST", $header, $body);

            // return $mage;
            $responseCode = $mage["response_code"];
            if ($responseCode == 200) {
                $result_body = json_decode($mage["result"], true);
                $result = [
                    "error" => false,
                    "message" => "Data Created successfully",
                    "data" => $result_body,
                ];
            } else {
                $result = [
                    "error" => true,
                    "message" => "An unexpected error occured",
                    "data" => $responseCode,
                ];
            }
        } catch (\Exception $e) {
            $result = [
                "error" => true,
                "message" => $e->getMessage(),
                "data" => "",
            ];
        } finally {
            return $result;
        }
    }

    protected function logOdooError($errorStatus, $customerId, $customerEmail)
    {
        $odooCustomerErrorLog = $this->odooCustomerErrorLogFactory->create();
        $odooCustomerErrorLog->setData([
            'odoo_id' => 0, // Assuming 0 or some default value for error cases
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'error_status' => $errorStatus
        ]);
        $this->odooCustomerErrorLogResource->save($odooCustomerErrorLog);
    }

    protected function logOdooSuccess($odooId, $customerId, $customerEmail)
    {
        $odooCustomerSuccessLog = $this->odooCustomerSuccessLogFactory->create();
        $odooCustomerSuccessLog->setData([
            'odoo_id' => $odooId,
            'customer_id' => $customerId,
            'customer_email' => $customerEmail,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
        $this->odooCustomerSuccessLogResource->save($odooCustomerSuccessLog);
    }

    private function curlExec($url, $method, $header, $body)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ["response_code" => $httpCode, "result" => $result];
    }
}
