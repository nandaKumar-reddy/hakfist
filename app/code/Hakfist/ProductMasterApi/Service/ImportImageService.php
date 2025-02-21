<?php

namespace Hakfist\ProductMasterApi\Service;
	
	use Magento\Catalog\Model\Product;
	use Magento\Framework\App\Filesystem\DirectoryList;
	use Magento\Framework\Filesystem\Io\File;
	
	class ImportImageService
    {
        protected $directoryList;
        protected $file;

        public function __construct(DirectoryList $directoryList,File $file)
        {
            $this->directoryList = $directoryList;
            $this->file = $file;
        }

        public function execute($product, $imageUrl, $visible = false, $imageType = [])
        {
            $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            $newFileName = $tmpDir . baseName($imageUrl);
            $result = $this->file->read($imageUrl, $newFileName);
            if ($result) {
                $product->addImageToMediaGallery($newFileName, $imageType, true, $visible);
            }
            return $result;
        }

        protected function getMediaDirTmpDir()
        {
            return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
        }
    }