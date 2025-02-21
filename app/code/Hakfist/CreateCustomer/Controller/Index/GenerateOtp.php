<?php

namespace Hakfist\CreateCustomer\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Hakfist\CreateCustomer\Model\ResourceModel\EmailOtpValidation as EmailOtpValidationResource;
use Hakfist\CreateCustomer\Model\EmailOtpValidationFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class GenerateOtp extends Action
{
    protected $resultJsonFactory;
    protected $emailOtpValidationResource;
    protected $emailOtpValidationFactory;
    protected $dateTime;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmailOtpValidationResource $emailOtpValidationResource,
        EmailOtpValidationFactory $emailOtpValidationFactory,
        DateTime $dateTime
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->emailOtpValidationResource = $emailOtpValidationResource;
        $this->emailOtpValidationFactory = $emailOtpValidationFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $response = ['success' => false];

        $email = $this->getRequest()->getParam('email');

        if ($email) {
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
                            return $result->setData($response);
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

                // Here you can add logic to send the OTP via email

                $response = [
                    'success' => true,
                    'message' => __('OTP generated successfully.'),
                    //'otp' => $otp // Only for testing, remove in production
                ];
            } catch (LocalizedException $e) {
                $response['message'] = $e->getMessage();
            } catch (\Exception $e) {
                $response['message'] = __('An error occurred while generating the OTP.');
            }
        } else {
            $response['message'] = __('Email is required.');
        }

        return $result->setData($response);
    }

    /**
     * Get the number of OTP generation attempts for today.
     *
     * @param string $email
     * @return int
     */
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
