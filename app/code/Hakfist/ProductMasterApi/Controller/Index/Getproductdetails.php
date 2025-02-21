<?php
namespace Hakfist\ProductMasterApi\Controller\Index;

use Hakfist\ProductMasterApi\Model\ProductmasterFactory;
use Magento\Framework\App\Filesystem\DirectoryList;


class Getproductdetails extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Catalog\Model\ProductFactory
    */
    protected $_productFactory;

    /**
    * @var \Magento\Catalog\Model\ResourceModel\Product
    */
    protected $_resourceModel;

    /**
    * @var SourceItemInterface
    */
    protected $sourceItemsSaveInterface;

    /**
    * @var \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory
    */
    protected $sourceItem;

    /**
    * @var \Magento\Eav\Api\AttributeRepositoryInterface
    */
    protected $attributeRepository;

    /**
    * @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory
    */
    protected $_optionsFactory;
    /**
    * @var \Magento\Catalog\Api\ProductRepositoryInterface
    */
    protected $productRepository;

    /**
    * @var \Magento\Catalog\Setup\CategorySetup
    */
    protected $categorySetup;

    private $directory; 

    /**
    * Index constructor.
    * @param \Magento\Catalog\Model\ProductFactory $productFactory
    * @param \Magento\Catalog\Model\ResourceModel\Product $resourceModel
    * @param SourceItemInterface $sourceItemsSaveInterface
    * @param \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItem
    * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    * @param \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionsFactory
    * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    * @param \Magento\Catalog\Setup\CategorySetup $categorySetup
    * @param Context $context
    */
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $helperApi;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
   
    protected $categoryFactory;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */

    protected $_productmasterfactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItem,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionsFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Setup\CategorySetup $categorySetup,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Hakfist\ProductMasterApi\Helper\Productdetails $helperApi,
        \Hakfist\ProductMasterApi\Model\ProductmasterFactory $_productmasterfactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperApi = $helperApi;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_productFactory = $productFactory;
        $this->_resourceModel = $resourceModel;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItem = $sourceItem;
        $this->attributeRepository = $attributeRepository;
        $this->_optionsFactory = $optionsFactory;
        $this->productRepository = $productRepository;
        $this->categorySetup = $categorySetup;
        $this->storeManager=$storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->_productmasterfactory = $_productmasterfactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $getresponse = $this->ExportproductCSV();
        
        $responseData = [
			'error' => false,
			'message' => __('Products Verification!.'),
			'data' => [],
		];

        $tablecreation = [
			'error' => true,
			'message' => __('Products Insertion Failed Please check data!.'),
			'data' => [],
		];

        $baseurl = $this->storeManager->getStore()->getBaseUrl();
        // $productattribute = $this->helperApi->getattributedetails();
        // foreach($products as $attributes){
        //     print_r($attributes);
        // }
        $responseData = $this->helperApi->getProductsfromAPI();
       
        if($responseData['error'] == false){
            // $tablecreation =$this->helperApi->InsertIntoTable($responseData['data']['products']);
        }else {
                $tablecreation['error'] = true;
                $tablecreation['message'] = "<p>Something error while getting products from Master API</p>";
                $tablecreation['message'] = $responseData;
        }
        if($tablecreation['error'] == false){
            $getresponse = $this->ExportproductCSV();
            $tablecreation['time'] = $getresponse;
            // $this->CreateCategory();
        }   
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($tablecreation);
        // return $this->resultPageFactory->create();
		
    } 
    public function CreateProducts(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $sku = 'HK0015';
        $product->setSku($sku);
        $product->setName('IREA-Color'); 
        $product->setAttributeSetId(4); 
        $product->setStatus(1); 
        $product->setWeight(1);
        $product->setVisibility(1); 
        $product->setWebsiteIds(array(1)); 
        $product->setTaxClassId(0); 
        $product->setTypeId('simple'); 
        $product->setPrice(100);
        $product->setMaterial(12);
        // $product->setCustomAttribute('material','Leather');
        // $product->setData('material', array('Leather'));
        $product->setStockData(
        array(
        'use_config_manage_stock' => 100, 
        'manage_stock' => 1, 
        'min_sale_qty' => 1, 
        'max_sale_qty' => 2, 
        'is_in_stock' => 1,
        'qty' => 100
        )
        );
        $product->save();
        $categoryIds = array('2','3');
        $category = $objectManager->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
        $category->assignProductToCategories($sku, $categoryIds);
        if ($product->getId())
        {
            // return "Product Created";
        }
        $productid = $product->getMaterial();
        return $productid;
    }

    public function CreateCategory(){
        // get the current stores root category
        $categoryName = "TShirts";
        $parentId = $this->storeManager->getStore()->getRootCategoryId();
        $category = $this->categoryFactory->create();
        $cate = $category->getCollection()
            ->addAttributeToFilter('name',['like' => $categoryName])
            ->getFirstItem();
            if ($cate->getId()) {
                // echo $cate->getId();
                $parentCategory = $this->categoryFactory->create()->load($parentId);
                // die();
            }
        $cate = $category->getCollection()
            ->addAttributeToFilter('name', $categoryName)
            ->getFirstItem();

        if (!$cate->getId()) {
            $category->setPath($parentCategory->getPath())
                ->setParentId($parentId)
                ->setName($categoryName)
                ->setIsActive(true);
            $category->save();
        }

        return $category;
    }
    public function CreateconfigurableProducts($response){
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $product->setTypeId('configurable') 
        ->setStatus(1) // 1 = enabled, 2 = disabled
        ->setAttributeSetId(4) // 4 = default
        ->setName('Configurable test product')
        ->setSku('1000')
        ->setPrice(0)
        ->setTaxClassId(2) // 0 = None, 2 = Default product tax class
        ->setCategoryIds(array(2)) // 2 = Default category
        ->setDescription('test description')
        ->setShortDescription('test short description')
        ->setUrlKey('configurable-test-product')
        ->setWebsiteIds(array(1)) // 1 = Default Website ID
        ->setStoreId(0) // 0 = Default store ID
        ->setVisibility(4); // 4 = Catalog & Search
                
        $product_resource = $product->getResource();
        $color_attribute = $product_resource->getAttribute('color');
        $size_attribute = $product_resource->getAttribute('size');
        $color_attribute_id = $color_attribute->getId();
        $size_attribute_id = $size_attribute->getId();
        $configurable_attributes = array('color', 'size'); // put here all configurable attributes you need and get their attribute IDs like I showed above
        $product->getTypeInstance()->setUsedProductAttributeIds(array($color_attribute_id, $size_attribute_id), $product); // assign attributes to configurable product
    
        $configurable_attributes_data = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        $product->setCanSaveConfigurableAttributes(true);
        $product->setConfigurableAttributesData($configurable_attributes_data);

        $child_product_skus = array('1234','2345'); // add all child skus you need
        $child_products = explode(',', $child_product_skus);
        $child_ids = array();
        foreach ($child_products as $child_product) {
            $child_ids[] = $product->getIdBySku($child_product);
        }

        $product->setAssociatedProductIds($child_ids);

        $product->save();
    }

    public function CreateconfigurableProduct(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create(Magento\Catalog\Model\Product::class);
        $sku = 'sku11-config'; 
        $product->setSku($sku);
        $product->setName('IREA-Config');
        $product->setWeight(1);
        $product->setPrice(100);
        $product->setDescription('description'); 
        $product->setAttributeSetId(4); 
        $product->setCategoryIds(array(35)); 
        $product->setStatus(1);
        $product->setVisibility(4); 
        $product->setTaxClassId(1); 
        $product->setTypeId('grouped'); 
        $product->setStoreId(1); 
        $product->setWebsiteIds(array(1)); 
        $product->setVisibility(4); 
        $product->setImage('/groupedproduct/image.jpg'); 
        $product->setSmallImage('/groupedproduct/image.jpg'); 
        $product->setThumbnail('/groupedproduct/image.jpg'); 
        $product->setStockData(array(
        'use_config_manage_stock' => 0, 
        'manage_stock' => 1, 
        'min_sale_qty' => 1, 
        'max_sale_qty' => 2,
        'is_in_stock' => 1, 
        'qty' => 1000
        )
        );
        $product->save();

        $childrenIds = [2, 3, 4]; 
        $associated = [];
        $position = 0;
        foreach ($childrenIds as $productId)
        {
        $position++;
        $linkedProduct = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface')->getById($productId);
        $productLink = $objectManager->create('\Magento\Catalog\Api\Data\ProductLinkInterface');
        $productLink->setSku($product->getSku())
        ->setLinkType('associated') 
        ->setLinkedProductSku($linkedProduct->getSku()) 
        ->setLinkedProductType($linkedProduct->getTypeId())
        ->setPosition($position) 
        ->getExtensionAttributes()
        ->setQty(1);
        $associated[] = $productLink;
        }
        $product->setProductLinks($associated);
        $product->save();
        $categoryIds = array('2','3'); 
        $category = $objectManager->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
        $category->assignProductToCategories($sku, $categoryIds);
        if ($product->getId())
        {
            echo "Product Created";
        }
    }
    public function ExportproductCSV(){

        $filepath = $this->appendDateTimeToFileName("product-csv.csv");
        $filepath = "product-csv.csv";
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $header = ['sku','store_view_code','attribute_set_code','product_type','categories','product_websites','name','description','short_description','weight','product_online','tax_class_name','visibility','price','special_price','special_price_from_date','special_price_to_date','url_key','meta_title','meta_keywords','meta_description','additional_attributes','qty','is_in_stock','configurable_variations','configurable_variation_labels'];
        $stream->writeCsv($header);
        $result = $this->_productmasterfactory->create();
        $color = $size = $capacity = $digitalspace = "";
        $updatedcolortattribute = $updatedsizeattribute = $updateddigitalspaceattribute = $updatedcapacityattribute = [];
        $collection = $result->getCollection(); 
        $colortattribute = $this->helperApi->getattributedetails('color');
        $sizeattribute = $this->helperApi->getattributedetails('size');
        $digitalspaceattribute = $this->helperApi->getattributedetails('digitalspace');
        $capacityattribute = $this->helperApi->getattributedetails('capacity');
        foreach ($collection as $product) {
            $typeofproduct =$product->getProducttype();
            if($typeofproduct == 'simple'){
                if($product->getColor()){                  
                    if (!in_array($product->getColor(), $colortattribute) && !in_array($product->getColor(), $updatedcolortattribute))
                    {
                        $updatedcolortattribute[] = $product->getColor();
                        // $this->helperApi->setattributeOptions($product->getColor(),'color');
                    }
                    $color = 'color='.$product->getColor();
                }
                if($product->getSize()){
                    if (!in_array($product->getSize(), $sizeattribute) && !in_array($product->getSize(), $updatedsizeattribute))
                    {
                        $updatedsizeattribute[] = $product->getSize();
                        // $this->helperApi->setattributeOptions($product->getSize(),'size');
                    }
                    $size = ',size='.$product->getSize();
                }
                if($product->getCapacity()){
                    if (!in_array($product->getCapacity(), $capacityattribute) && !in_array($product->getCapacity(), $updatedcapacityattribute))
                    {
                        $updatedcapacityattribute[] = $product->getCapacity();
                        // echo "Getting digital space attribute values".$product->getCapacity();
                        // $this->helperApi->setattributeOptions($product->getCapacity(),'capacity');
                    }
                    $capacity = ',capacity='.$product->getCapacity();
                }
                if($product->getDigitalspace()){
                    if (!in_array($product->getDigitalspace(), $digitalspaceattribute) && !in_array($product->getDigitalspace(), $updateddigitalspaceattribute))
                    {
                        $updateddigitalspaceattribute[] = $product->getDigitalspace();
                        // echo "Getting digital space attribute values".$product->getDigitalspace();
                        // $this->helperApi->setattributeOptions($product->getDigitalspace(),'digitalspace');
                    }
                    $digitalspace = ',digitalspace='.$product->getDigitalspace();
                }
                $additional_attributes = $color.$size.$capacity.$digitalspace;
            }
            $buildURL = trim($product->getName().'-'.$product->getSku());
            $buildURL = trim($product->getSku());
            $customURL = str_replace(' ', '-', $buildURL); 
            $symbols=array(' ','!','"','#','$','%','&','\'','(',')','*','+',',','---','.','/',':',';','<','>','=','?','@','[',']','\\','^','_','{','}','|','~','`');
            $replacement=array('');
            $customURL=str_replace($symbols,$replacement,$customURL);
            $productData = [];
            $productData[] = $product->getSku();
            $productData[] = '';
            $productData[] = 'Default';
            $productData[] = $product->getProducttype();
            $productData[] = $product->getCategory();
            $productData[] = 'base';
            $productData[] = $product->getName();
            $productData[] = $product->getDescription();
            $productData[] = '';
            $productData[] = '';
            $productData[] = 1;
            $productData[] = 'Taxable Goods';
            $productData[] = $product->getVisibility();
            $productData[] = $product->getPrice();
            $productData[] = '';
            $productData[] = '';
            $productData[] = '';
            $productData[] = $customURL;
            $productData[] = '';
            $productData[] = '';
            $productData[] = '';
            $productData[] = $additional_attributes;
            $productData[] = $product->getQuantity();
            $productData[] = 1;
            $productData[] = $product->getConfigs();
            $productData[] = '';
            $stream->writeCsv($productData);
        }
        $exportedtime =  $this->helperApi->LastSyncDetails();
        // $this->helperApi->setattributeOptions($updatedcolortattribute,'color');
        // $this->helperApi->setattributeOptions($updatedsizeattribute,'size');
        // $this->helperApi->setattributeOptions($updatedcapacityattribute,'capacity');
        // $this->helperApi->setattributeOptions($updateddigitalspaceattribute,'digitalspace');
        $result = [
			'error' => false,
			'message' => __('Products Exported successfully into Product-csv.csv file in var directory'),
			'data' => [],
		];

       return $exportedtime;
    }

    public function appendDateTimeToFileName($fileName) {
        $appended = date('_Y_m_d_H_i_s');
        $dotCount = substr_count($fileName, '.');
        if (!$dotCount) {
            return $fileName . $appended;
        }
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);
        return $fileName . $appended . '.' . $extension;
    }
   
}