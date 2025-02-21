<?php

namespace Hakfist\ProductMasterApi\Block;

use Magento\Framework\App\Request\DataPersistorInterface;

class Productmasterdata extends \Magento\Framework\View\Element\Template
{
    protected $customData;

    public function __construct(
        // \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Element\Template\Context $context,
        \Hakfist\ProductMasterApi\Model\ResourceModel\Productmaster\CollectionFactory $customData
    ) {
        parent::__construct($context);
        $this->customData = $customData;
    }
    public function getCustomTableData()
    {
        // return $this->customData->create();

        return __('Data retrived successfully');

    }
}
