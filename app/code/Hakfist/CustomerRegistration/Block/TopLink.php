<?php

namespace Hakfist\CustomerRegistration\Block;

use Magento\Customer\Model\Context;
use Magento\Framework\View\Element\Template\Context as TemplateContex;
use Magento\Customer\Model\Session;

class TopLink extends \Magento\Framework\View\Element\Html\Link {


    /**
     *
     * @var Session
     */
    protected $session;

    protected $httpContext;

    /**
     * 
     * @param TemplateContex $context    
     * @param Session $session
     * @param array $data
     */
    public function __construct(
    TemplateContex $context, \Magento\Framework\App\Http\Context $httpContext, Session $session,array $data = []) {        
        $this->session = $session;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml() {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }
        $IsloggedIn = $this->getCustomerIsLoggedIn();
        if ($IsloggedIn == 1) {
            $session = $this->getCustomerIsLoggedIn();
            // return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLinkLabel()) . '</a></li>';
            return '<li class="myaccount"'.$session.'"><a href="#">My Account</a>
                            <ul class="dropdown">
                                <li><a href="../sales/order/history/">My Orders</a></li>
                                <li><a href="#">Track Orders</a></li>
                                <li><a href="../customer/account/logout">Sign Out</a></li>
                            </ul>
                        </li>';
        }else {
            $session = $this->getCustomerIsLoggedIn();
            return '<li class="myaccount'.$session.'"><a href="hakfist/customer/login">My Account</a></li>';
        }
    }

    /**
     * 
     * @return string
     */
    private function getSignOut() {
        return 'Sign Out';
    }    
    public function getCustomerIsLoggedIn()
	{
    	return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
	}

	public function getCustomerId()
	{
    	return $this->httpContext->getValue('customer_id');
	}

	public function getCustomerName()
	{
    	return $this->httpContext->getValue('customer_name');
	}

	public function getCustomerEmail()
	{
    	return $this->httpContext->getValue('customer_email');
	}

}