<?php

namespace Hakfist\ProductSync\Cron;

use Magento\ImportExport\Model\Import\Adapter;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImportProducts
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
     * @param \Hakfist\ProductSync\Model\Import\Product $productImport
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryManager
     * @param \Magento\ImportExport\Model\Import $import
     * @param Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Hakfist\ProductSync\Model\Import\Product $productImport,
        \Magento\Framework\Filesystem\DirectoryList $directoryManager,
        \Magento\ImportExport\Model\Import $import,
        Filesystem $filesystem,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productImport = $productImport;
        $this->directoryManager = $directoryManager;
        $this->import = $import;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute()
 {
        try {
            $varDirectory = $this->directoryManager->getPath('var');
            $productCsvFileName = "product-csv";
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            
            $file = $varDirectory . DIRECTORY_SEPARATOR . $productCsvFileName . '.csv';
            $logger->info('product csv file successfully fetched from '.$file);

            $postData = [
                'entity' => 'catalog_product',
                'behavior' => 'append',
                'validation_strategy' => 'validation-stop-on-errors',
                'allowed_error_count' => 1,
                '_import_field_separator' => '',
                '_import_multiple_value_separator' => '',
                '_import_empty_attribute_value_constant' => '__EMPTY__VALUE__',
                'import_images_file_dir' => $varDirectory . '/import/images'
            ];

            $this->import->setData($postData);
            $source = $this->_getSourceAdapter($file);
            $isValid = $this->import->validateSource($source);

            if ($isValid) {
                $this->productImport->startImport($varDirectory . '/import/sync');
                $logger->info('file validated successfully '. $isValid);
            } else {
                $logger->info('error(s) found while importing products from '.$file);
                $errros = $this->import->getErrorAggregator()->getAllErrors();
                $logger->info(count($errros) . 'error(s) found while importing products from CSV File.');
                foreach ($errros as $error) {
                    $logger->info($error->getErrorMessage() . ' at row number ' . $error->getRowNumber() . ' and coloumn name ' . $error->getColumnName());
                    $logger->info($error->getErrorMessage() . ' at row number ' . $error->getRowNumber() . ' and coloumn name ' . $error->getColumnName());
                }

            }
        } catch (\Exception $e) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('Something went wrong while importing products');
            $logger->error($e->getMessage());
        }
        return $this;
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
}