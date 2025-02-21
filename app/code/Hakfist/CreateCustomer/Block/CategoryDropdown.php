<?php

namespace Hakfist\CreateCustomer\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class CategoryDropdown extends Template
{
    protected $categoryCollectionFactory;

    public function __construct(
        Template\Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getCategoryOptions()
    {
        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', ['gt' => 1]) // Exclude root category
            ->addOrderField('name');

        $options = [];
        foreach ($categories as $category) {
            $options[] = [
                'value' => $category->getId(),
                'label' => $category->getName()
            ];
        }

        return $options;
    }

    public function getCountryOptions()
    {
        $countries = $this->countryCollectionFactory->create()
            ->loadData()
            ->toOptionArray(false);

        return $countries;
    }
}
