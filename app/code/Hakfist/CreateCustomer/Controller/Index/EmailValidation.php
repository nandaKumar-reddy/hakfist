<?php

namespace Hakfist\CreateCustomer\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Hakfist\CreateCustomer\Model\ResourceModel\EmailOtpValidation as EmailOtpValidationResource;
use Hakfist\CreateCustomer\Model\EmailOtpValidationFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class EmailValidation extends Action
{
    protected $resultJsonFactory;
    protected $emailOtpValidationResource;
    protected $emailOtpValidationFactory;
    protected $dateTime;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $logger;


    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmailOtpValidationResource $emailOtpValidationResource,
        EmailOtpValidationFactory $emailOtpValidationFactory,
        DateTime $dateTime,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface  $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->emailOtpValidationResource = $emailOtpValidationResource;
        $this->emailOtpValidationFactory = $emailOtpValidationFactory;
        $this->dateTime = $dateTime;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {

        $result = $this->resultJsonFactory->create();
        $response = ['success' => false];

        $action = $this->getRequest()->getParam('action');
        $email = $this->getRequest()->getParam('email');
        $otp = $this->getRequest()->getParam('otp');

        if (!$email) {
            $response['message'] = __('Email is required.');
            return $result->setData($response);
        }

        switch ($action) {
            case 'generate':
                $response = $this->generateOtp($email);
                break;

            case 'validate':
                if (!$otp) {
                    $response['message'] = __('OTP is required.');
                } else {
                    $response = $this->validateOtp($email, $otp);
                }
                break;

            default:
                $response['message'] = __('Invalid action.');
        }

        return $result->setData($response);
    }

    // public function generateOtp($email)
    // {
    //     $response = ['success' => false];
    //     try {
    //         // Get current date
    //         $currentDate = $this->dateTime->gmtDate('Y-m-d');

    //         // Load the most recent record for this email
    //         $emailOtpValidation = $this->emailOtpValidationFactory->create();
    //         $connection = $this->emailOtpValidationResource->getConnection();
    //         $select = $connection->select()
    //             ->from($this->emailOtpValidationResource->getMainTable())
    //             ->where('customer_email = ?', $email)
    //             ->order('created_at DESC')
    //             ->limit(1);
    //         $latestRecord = $connection->fetchRow($select);

    //         // Check if there are attempts today
    //         if ($latestRecord) {
    //             $latestCreatedDate = $this->dateTime->gmtDate('Y-m-d', $latestRecord['created_at']);
    //             if ($latestCreatedDate === $currentDate) {
    //                 // Check if attempts exceed the limit
    //                 $attempts = $this->getAttemptsForToday($email);
    //                 if ($attempts >= 3) {
    //                     $response['message'] = __('You have exceeded the maximum number of OTP generation attempts for today.');
    //                     return $response;
    //                 }
    //             }
    //         }

    //         // Generate a new OTP
    //         $otp = random_int(100000, 999999);
    //         $emailOtpValidation->setCustomerEmail($email);
    //         $emailOtpValidation->setOtp($otp);
    //         $emailOtpValidation->setValidate(null);
    //         $emailOtpValidation->setCreatedAt($this->dateTime->gmtDate());
    //         $this->emailOtpValidationResource->save($emailOtpValidation);

    //         // Here you can add logic to send the OTP via email
    //             // Prepare and send the OTP email
    //             $this->inlineTranslation->suspend();

    //             $sender = [
    //                 'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
    //                 'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
    //             ];

    //             $transport = $this->transportBuilder
    //                 ->setTemplateIdentifier('otp_email_template') // Use your email template identifier
    //                 ->setTemplateOptions([
    //                     'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // Set the area, typically frontend
    //                     'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID, // Set the store ID
    //                 ])
    //                 ->setTemplateVars(['otp' => $otp])
    //                 ->setFromByScope($sender)
    //                 ->addTo($email)
    //                 ->getTransport();
    //             $transport->sendMessage();

    //             $this->inlineTranslation->resume();

    //         $response = [
    //             'success' => true,
    //             'message' => __('OTP generated successfully.'),
    //             // 'otp' => $otp // Only for testing, remove in production
    //         ];
    //     } catch (LocalizedException $e) {
    //         $response['message'] = $e->getMessage();
    //     } catch (\Exception $e) {
    //         $response['message'] = __('An error occurred while generating the OTP.');
    //     }
    //     return $response;
    // }

    public function generateOtp($email)
    {
        $response = ['success' => false];
        try {
            // Get current date
            $currentDate = $this->dateTime->gmtDate('Y-m-d');

            // Load the most recent record for this email
            $emailOtpValidation = $this->emailOtpValidationFactory->create();
            $connection = $this->emailOtpValidationResource->getConnection();
            $select = $connection->select()
                ->from($this->emailOtpValidationResource->getMainTable())
                ->where('customer_email = ?', $email)
                ->order('created_at DESC')
                ->limit(1);
            $latestRecord = $connection->fetchRow($select);

            // Check if there are attempts today
            if ($latestRecord) {
                $latestCreatedDate = $this->dateTime->gmtDate('Y-m-d', $latestRecord['created_at']);
                if ($latestCreatedDate === $currentDate) {
                    // Check if attempts exceed the limit
                    $attempts = $this->getAttemptsForToday($email);
                    if ($attempts >= 3) {
                        $response['message'] = __('You have exceeded the maximum number of OTP generation attempts for today.');
                        return $response;
                    }
                }
            }

            // Generate a new OTP
            $otp = random_int(100000, 999999);
            $emailOtpValidation->setCustomerEmail($email);
            $emailOtpValidation->setOtp($otp);
            $emailOtpValidation->setValidate(null);
            $emailOtpValidation->setCreatedAt($this->dateTime->gmtDate());
            $this->emailOtpValidationResource->save($emailOtpValidation);

            // Prepare and send the OTP email
            $this->inlineTranslation->suspend();

            $sender = [
                'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('otp_email_template') // Use your email template identifier
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // Set the area, typically frontend
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID, // Set the store ID
                ])
                ->setTemplateVars(['otp' => $otp])
                ->setFromByScope($sender)
                ->addTo($email)
                ->getTransport();
            $transport->sendMessage();


            $this->inlineTranslation->resume();


            $response = [
                'success' => true,
                'message' => __('OTP generated and sent successfully.'),
                // 'otp' => $otp // Only for testing, remove in production
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $response['message'] = __('An error occurred while generating the OTP.');
        }
        return $response;
    }


    public function validateOtp($email, $otp)
    {
        $response = ['success' => false];
        try {
            // Load the most recent record for this email
            $emailOtpValidation = $this->emailOtpValidationFactory->create();
            $connection = $this->emailOtpValidationResource->getConnection();
            $select = $connection->select()
                ->from($this->emailOtpValidationResource->getMainTable())
                ->where('customer_email = ?', $email)
                ->order('created_at DESC')
                ->limit(1);
            $latestRecord = $connection->fetchRow($select);

            if ($latestRecord) {
                if ($latestRecord['validate'] == 'validated') {
                    $response['message'] = __('This OTP has already been validated.');
                } elseif ($latestRecord['otp'] == $otp) {
                    $emailOtpValidation->setId($latestRecord['id']);
                    $emailOtpValidation->setValidate('validated');
                    $this->emailOtpValidationResource->save($emailOtpValidation);

                    $response = [
                        'success' => true,
                        'message' => __('OTP validated successfully.')
                    ];
                } else {
                    $response['message'] = __('Invalid OTP.');
                }
            } else {
                $response['message'] = __('No OTP found for this email.');
            }
        } catch (LocalizedException $e) {
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $response['message'] = __('An error occurred while validating the OTP.');
        }

        return $response;
    }

    protected function getAttemptsForToday($email)
    {
        $connection = $this->emailOtpValidationResource->getConnection();
        $select = $connection->select()
            ->from($this->emailOtpValidationResource->getMainTable(), 'COUNT(*)')
            ->where('customer_email = ?', $email)
            ->where('DATE(created_at) = ?', $this->dateTime->gmtDate('Y-m-d'));

        return (int) $connection->fetchOne($select);
    }
}
