<?php

namespace Hakfist\CreateCustomer\Block;

use Magento\Framework\View\Element\Template;

use Magento\Backend\Block\Template\Context;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class Form extends Template
{
    protected $_countryCollectionFactory;

    public function __construct(
        CollectionFactory $countryCollectionFactory,
        Context $context,
        array $data = []
    ) {
    
        $this->_countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getCountryCollection()
    {
   
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    public function getCountries()
    {
    $this->getCountryCollection()->toOptionArray();
    }
}

?>
