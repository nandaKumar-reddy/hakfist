<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hakfist\HomeBanners\Model\HomeBanners;

use Hakfist\HomeBanners\Model\ResourceModel\HomeBanners\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    public $_storeManager;
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $blockCollectionFactory->create();
        $this->_storeManager=$storeManager;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $baseurl =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Magento\Cms\Model\Block $block */
        
        foreach ($items as $block) {
            
            $this->loadedData[$block->getId()] = $block->getData();

             $temp = $block->getData();
                $img = [];
                if($temp['homebannersimage']){
                    $img[0]['name'] = $temp['homebannersimage'];
                    $img[0]['url'] = $baseurl.$temp['homebannersimage'];
                   $temp['homebannersimage'] = $img;
                }

                // if($temp['homebannersbanner']){
                //     $img[0]['name'] = $temp['homebannersbanner'];
                //     $img[0]['url'] = $baseurl.$temp['homebannersbanner'];
                //    $temp['homebannersbanner'] = $img;
                // }
                             
                // if($temp['homebannershoverimage']){
                //     $img[0]['name'] = $temp['homebannershoverimage'];
                //     $img[0]['url'] = $baseurl.$temp['homebannershoverimage'];
                //    $temp['homebannershoverimage'] = $img;
                // }
        }
        
        $data = $this->dataPersistor->get('Hakfist_homebanners');
        
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);



            $this->loadedData[$block->getId()] = $block->getData();
             
            $this->dataPersistor->clear('Hakfist_homebanners');
        }
        
        if (empty($this->loadedData)) {
            return $this->loadedData;
        } else {
            if ($block->getData('homebannersimage') != null) {
                $t2[$block->getId()] = $temp;
                return $t2;
            } 
            // if ($block->getData('homebannersbanner') != null) {
            //     $t3[$block->getId()] = $temp;
            //     return $t3;
            // } if ($block->getData('homebannershoverimage') != null) {
            //     $t3[$block->getId()] = $temp;
            //     return $t3;
            // } 
            else {
                return $this->loadedData;
            }
        }
    }
}
