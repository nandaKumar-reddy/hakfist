<?php

namespace Hakfist\ProductMasterApi\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class Exportproducts extends Action
{

    private $customerFactory;
    private $directory;
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->customerFactory = $customerFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    public function execute()
    {
        $filepath = 'export/allcustomer.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $header = ['CustomerId', 'Customer Name', 'Customer Email'];
        $stream->writeCsv($header);

        $collection = $this->customerFactory->create()->getCollection();
        foreach ($collection as $customer) {
            $customerData = [];
            $customerData[] = $customer->getId();
            $customerData[] = $customer->getName();
            $customerData[] = $customer->getEmail();
            $stream->writeCsv($customerData);
        }
    }
}