<?php

namespace Hakfist\CreateCustomer\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

class LoginForm extends Action
{
    protected $customerSession;
    protected $customerFactory;
    protected $accountManagement;
    protected $storeManager;
    protected $customerRepository;
    protected $resultJsonFactory;
    protected $_pageFactory;
    protected $persistentHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerFactory $customerFactory,
        AccountManagement $accountManagement,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Hakfist\CreateCustomer\Helper\PersistentHelper $persistentHelper
    ) {
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_pageFactory = $pageFactory;
        $this->persistentHelper = $persistentHelper;
        parent::__construct($context);
    }

    public function execute()
    {

        $response = ['success' => false, 'message' => 'Invalid login details.'];
        if ($this->customerSession->isLoggedIn()) {
            $response['message'] = 'Customer is already logged in.';
            return $this->resultJsonFactory->create()->setData($response);
        }

        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $response['message'] = 'Post data is missing.';
            return $this->resultJsonFactory->create()->setData($response);
        }

        $email = $post['email'] ?? '';
        $password = $post['password'] ?? '';
        $rememberMe = isset($post['remember_me']) && $post['remember_me'] == '1';
        if (empty($email) || empty($password)) {
            $response['message'] = 'Email or password is missing.';
            return $this->resultJsonFactory->create()->setData($response);
        }

        try {
            // Check if email exists
            $customerData = $this->customerRepository->get($email);

            // If email exists, attempt to authenticate
            $customer = $this->accountManagement->authenticate($email, $password);
            $this->customerSession->setCustomerDataAsLoggedIn($customerData);
            $this->customerSession->regenerateId();
            // Set Remember Me functionality
        if ($rememberMe) {
            $this->persistentHelper->setRememberMeCookie(true);
        }
            $response = ['success' => true, 'message' => 'Login successfully.'];
        } catch (NoSuchEntityException $e) {
            // Email does not exist
            $response['message'] = 'Please verify your email or create an account.';
        } catch (InvalidEmailOrPasswordException $e) {
            // Incorrect password
            $response['message'] = 'Wrong password. Please try again.';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
