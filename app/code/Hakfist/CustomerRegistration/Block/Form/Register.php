<?php
namespace Hakfist\CustomerRegistration\Block\Form;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Customer\Model\Session as CustomerSession;

class Register extends Template
{
    protected $_countryFactory;

    public function __construct(
        Context $context,
        Country $countryFactory,
        RegionCollectionFactory $regionCollectionFactory,
        RegionFactory $regionFactory,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_countryFactory = $countryFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->regionFactory = $regionFactory;
          $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getCountryOptions()
    {
        return $this->_countryFactory->toOptionArray();
    }

    public function getRegionOptions()
    {
        $regions = $this->regionFactory->create()->getCollection();
        $options = [];
        foreach ($regions as $region) {
            $options[$region->getId()] = $region->getDefaultName();
        }
        return $options;
    }
}

