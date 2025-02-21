<?php

namespace Hakfist\ProductSync\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends Action
{
 /**
     * Import field separator.
     */
    const FIELD_FIELD_SEPARATOR = '_import_field_separator';

    /**
     * @var \Hakfist\ProductSync\Model\Import\Product
     */
    protected $productImport;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryManager;
    protected $resultJsonFactory;
    protected $helperApi;
    /**
     * @var \Magento\ImportExport\Model\Import $import
     */
    protected $import;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;
    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;

    /**
     * @param \Hakfist\ProductSync\Model\Import\Product $productImport
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryManager
     * @param \Magento\ImportExport\Model\Import $import
     * @param Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface $logger
     */

    public function __construct(
        Context $context,
        \Hakfist\ProductSync\Model\Import\Product $productImport,
        \Magento\Framework\Filesystem\DirectoryList $directoryManager,
        \Magento\ImportExport\Model\Import $import,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Framework\Indexer\ConfigInterface $config,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Hakfist\ProductMasterApi\Helper\Productdetails $helperApi,
        Filesystem $filesystem,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->productImport = $productImport;
        $this->directoryManager = $directoryManager;
        $this->import = $import;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperApi = $helperApi;
        $this->filesystem = $filesystem;
        $this->logger = $logger; 
        $this->indexerFactory = $indexerFactory;
        $this->config = $config;
        parent::__construct($context);
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    /**
     * Regenerate full index
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {

        $responseData = [
			'error' => false,
			'message' => __('Products Verification!.'),
			'data' => [],
            'time' => '',
		];

        try {
            $varDirectory = $this->directoryManager->getPath('var');
            $productCsvFileName = "product-csv";            
            $file = $varDirectory . DIRECTORY_SEPARATOR . $productCsvFileName . '.csv';
            $this->logger->info('product csv file successfully fetched from '.$file);
            $importtime =  $this->helperApi->LastImportDetails();
            $postData = [
                'entity' => 'catalog_product',
                'behavior' => 'append',
                'validation_strategy' => 'validation-stop-on-errors',
                'allowed_error_count' => 1,
                '_import_field_separator' => ',',
                '_import_multiple_value_separator' => ',',
                '_import_empty_attribute_value_constant' => '__EMPTY__VALUE__',
                'import_images_file_dir' => $varDirectory . '/import/images'
            ];

            $this->import->setData($postData);
            $source = $this->_getSourceAdapter($file);
            $isValid = $this->import->validateSource($source);

            if ($isValid) {
                $this->productImport->startImport($varDirectory . '/import/sync');
                $this->logger->info('file validated successfully '. $isValid);
                $this->reindexAll();
                $result = [
                    'error' => false,
                    'message' => __('file imported successfully.Please verify log for complete repot.'),
                    'data' => '',
                    'time' => $importtime,
                ];
            } else {
                $this->logger->info('error(s) found while importing products from '.$file);
                $errros = $this->import->getErrorAggregator()->getAllErrors();
                $this->logger->info(count($errros) . 'error(s) found while importing products from CSV File.');
                foreach ($errros as $error) {
                    $this->logger->info($error->getErrorMessage() . ' at row number ' . $error->getRowNumber() . ' and coloumn name ' . $error->getColumnName());
                }
                $result = [
                    'error' => true,
                    'message' => __('error(s) found while importing products from CSV File.Please verify log for complete repot.'),
                    'data' => $errros,
                    'time' => $importtime,
                ];

            }


        } catch (\Exception $e) {
            $this->logger->info('Something went wrong while importing products');
            $this->logger->info($e->getMessage());
            $result = [
                'error' => true,
                'message' => __('Something went wrong while importing products'),
                'data' => $e->getMessage(),
            ];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
        // return $result;
    }

     /**
     * Returns source adapter object.
     *
     * @param string $sourceFile Full path to source file
     * @return AbstractSource
     * @throws FileSystemException
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return Adapter::findAdapterFor(
            $sourceFile,
            $this->filesystem->getDirectoryWrite(DirectoryList::ROOT),
            $this->import->getData(self::FIELD_FIELD_SEPARATOR)
        );
    }
    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexAll(){
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {          
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();            
        }
    }
}