<?php
namespace Hakfist\HomeBanners\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
        
class HomeBanners extends Template
{
          
    protected $scopeConfig;
    protected $collectionFactory;
    protected $objectManager;
    
       /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Hakfist\HomeBanners\Model\ResourceModel\HomeBanners\CollectionFactory $collectionFactory,
        ObjectManagerInterface $objectManager
    ) {
        
        $this->scopeConfig = $context->getScopeConfig();
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->objectManager = $objectManager;
                
        parent::__construct($context);
    }
        
    public function getHomebanners(){
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('Hakfist_homebanners');
        $sql = "SELECT title,homebannersimage,homebannerssceneimg,homebannerslink,mobilebannertitle,mobilehomebannerslink,homebannersmobileimg FROM ". $tableName. " WHERE status = 1 ORDER BY homebannerssort ASC";
        $collection = $connection->fetchAll($sql);
        return $collection;
    }

    public function getFrontHomeBanners()
    {
        $collection = $this->collectionFactory
                    ->create()
                    ->addFieldToFilter('status', 1)
                    ->setOrder('homebannerssort','ASC');

        /*
         * cehck for arguments,provided in block call
         */
        if ($ids_list = $this->getBannerBlockArguments()) {
            $collection->addFilter('homebanners_id', ['in' => $ids_list], 'public');
        }
                
                
        return $collection;
    }
                
        
    public function getBannerBlockArguments()
    {
            
        $list =  $this->getBannerList();
                
        $listArray = [];
                
        if ($list != '') {
            $listArray = explode(',', $list);
        }
                
        return $listArray;
    }
        
    public function getMediaDirectoryUrl()
    {
            
        $media_dir = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
        ->getStore()
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            
        return $media_dir;
    }
}
