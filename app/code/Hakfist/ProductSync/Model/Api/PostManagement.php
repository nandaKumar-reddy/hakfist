<?php
namespace Hakfist\ProductSync\Model\Api;
use Hakfist\ProductSync\Api\PostManagementInterface;
use Magento\Framework\Webapi\Rest\Request; 
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;
use Hakfist\ProductMasterApi\Model\ProductmasterlogFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class PostManagement implements PostManagementInterface
{
    protected $logger;
    protected $request;
    protected $productRepository;
    protected $newProduct;
    protected $productFactroy;
    protected $storeManager;
    protected $_Productmasterlog;
    protected $_categoryCollectionFactory;
    protected $categoryFactory; 
    protected $helperApi;
    protected $_objectManager = null;
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * File interface
     *
     * @var File
     */
    protected $file;
    /**
     * ImportImageService constructor
     *
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Hakfist\ProductMasterApi\Model\ProductmasterlogFactory  $Productmasterlog,
        \Hakfist\ProductMasterApi\Helper\Productdetails $helperApi,
        DirectoryList $directoryList,
        Product $newProduct,
        Request $request,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        File $file
    )
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->_objectManager = $objectManager;
        $this->newProduct = $newProduct;
        $this->productRepository = $productRepository; 
        $this->productFactroy = $productFactory;
        $this->storeManager = $storeManager;
        $this->_Productmasterlog = $Productmasterlog;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->directoryList = $directoryList;
        $this->helperApi = $helperApi;
        $this->file = $file;
    }
    /**
     * {@inheritdoc}
     */
    public function customGetMethod($value)
    {
        try{
            $response = [
                    'storeid' => $value,
                    'name' =>$value
            ];
        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }

        return json_encode($response);
    }

    /**
     * {@inheritdoc}
     */

    public function customPostMethod($productarray)
    {

        $data = ["status"=>true, "message"=> ""];
        // $requestData = json_decode(file_get_contents('php://input'), true);
        $body = $this->request->getBodyParams();
        $requestData  = $body;
        $getProductArray = $requestData['productarray'];
        foreach($getProductArray as $product){
            // Verifying type of product
            if($product['productType'] == "simple"){ 
                $productStatus = $this->CreateSimpleProducts($product,$requestData);
            }else if($product['productType'] == "parent"){
                $productStatus = $this->CreateconfigurableProduct($product,$requestData);
            }else{
                $prodSKU = "NA";
                if(isset($product['sku'])){$prodSKU = $product['sku'];};
                $productStatus = "Please verify ProductType for ". $prodSKU;
                $data = ["status"=>true, "message"=> $productStatus];
                $model = $this->_Productmasterlog->create();
                $model->addData([
                    "jsonresponse" => json_encode($requestData),
                    "message" => json_encode($data),
                    "typeofapi" => 'ProductSync',
                    "status" => 'Failed',
                    "requesteid" => $product['apiRefId']
                    ]);
                $saveData = $model->save();
                throw new \Magento\Framework\Webapi\Exception(    __(json_encode($productStatus)),0,\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            }
        }
       
        $data = ["status"=>true, "message"=> $productStatus];
        $model = $this->_Productmasterlog->create();
		$model->addData([
			"jsonresponse" =>json_encode($requestData),
			"message" => json_encode($data),
			"typeofapi" => 'ProductSync',
			"status" => 'Success',
			"requesteid" => $product['apiRefId']
			]);
        $saveData = $model->save();
        return $data;
        // throw new \Magento\Framework\Webapi\Exception(    __(json_encode($data)),0,\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
    }

    public function CreateconfigurableProduct($itemInfo){
       
        $configurable_product = $this->newProduct; 
        $categoryArray = [];  
        $productStatus = $this->ValidateData($itemInfo);
        $validatedjson = $productStatus;
        $imageUrl = $itemInfo['productImage'];
        unset($validatedjson[0]); 
        if(empty($validatedjson)) {
            $sku = $itemInfo['sku'];
            $IsProductexist = $this->getProductBySku($sku);
            if ($IsProductexist == false){
                try { 
                    $assignedCategory = $itemInfo['productCategory'];
                    foreach($assignedCategory as $eachcategory){
                        $childcategory = explode('/', $eachcategory);
                        foreach($childcategory as $subcategory){
                            $category = $this->GetCategoryId($subcategory);
                            $categoryArray[] = $category;
                        }
                    }                   
                    $configurable_product->setSku($sku);
                    $configurable_product->setName($itemInfo['productName']);
                    $configurable_product->setAttributeSetId(4);
                    $configurable_product->setStatus($itemInfo['active']);
                    $configurable_product->setVisibility(4); 
                    $configurable_product->setTypeId('configurable');
                    $configurable_product->setPrice($itemInfo['salePrice']);
                    $configurable_product->setUom($itemInfo['uom']);
                    $configurable_product->setWebsiteIds(array(1));
                    $configurable_product->setCategoryIds($categoryArray);
                    $configurable_product->setUrlKey(strtolower($sku));
                    // $configurable_product->setCustomAttribute('dimensions', $itemInfo['dimensions']);
                    $configurable_product->setDimensions($itemInfo['dimensions']);
                    $configurable_product->setDimensionswidth($itemInfo['dimensionsWidth']);
                    $configurable_product->setDimensionslength($itemInfo['dimensionsLength']);
                    $configurable_product->setDimensionsvolume($itemInfo['dimensionsVolume']);
                    $configurable_product->setdimensionsdepth($itemInfo['dimensionsDepth']);
                    $configurable_product->setGrossweight($itemInfo['grossWeight']);
                    $configurable_product->setNetweight($itemInfo['netWeight']);
                    $configurable_product->setCartonheight($itemInfo['cartonHeight']);
                    $configurable_product->setcartonwidth($itemInfo['cartonWidth']);
                    $configurable_product->setCartonlength($itemInfo['cartonLength']);
                    $configurable_product->setCartonvolume($itemInfo['cartonVolume']);
                    $configurable_product->setCartongrossweight($itemInfo['cartonGrossWeight']);
                    $configurable_product->setCartonquantity($itemInfo['cartonQuantity']);
                    $configurable_product->setTargetMarket(implode(',', $itemInfo['targetMarket']));
                    $configurable_product->setOccasions(implode(',', $itemInfo['occasions']));
                    $configurable_product->setSeasons(implode(',', $itemInfo['seasons']));
                    $configurable_product->setIsFragile($itemInfo['isFragile']);
                    $configurable_product->setProductVideos(implode(',', $itemInfo['productVideos']));
                    $configurable_product->setLifeStyleImages(implode(',', $itemInfo['lifeStyleImages']));
                    $configurable_product->setIndividualpackagingafterprinting($itemInfo['individualPackagingAfterPrinting']);
                    $configurable_product->setStockData(
                    array(
                        'use_config_manage_stock' => 100, 
                        'manage_stock' => 1, 
                        'min_sale_qty' => 1, 
                        'max_sale_qty' => 2, 
                        'is_in_stock' => 1,
                        'qty' => $itemInfo['quantity']
                        )
                    );
                    // $simpleProducts = $itemInfo['variants'];
                    // $associatedProductIds = [];
                    // foreach($simpleProducts as $simpleProduct){
                    //     $getProdInfo = $this->getProductBySku($simpleProduct['productId']);
                    //     if($getProdInfo != false){
                    //         $associatedProductIds[] = $getProdInfo->getId();
                    //     }

                    // }
                    // $size_attr_id = $configurable_product->getResource()->getAttribute('size')->getId();
                    // $color_attr_id = $configurable_product->getResource()->getAttribute('color')->getId();
                    // $configurable_product->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id, $size_attr_id], $configurable_product);
                    // $configurableAttributesData = $configurable_product->getTypeInstance()->getConfigurableAttributesAsArray($configurable_product);
                    // $configurable_product->setConfigurableAttributesData($configurableAttributesData);
                    // $configurableProductsData = [];
                    // $configurable_product->setConfigurableProductsData($configurableProductsData);

                    // $productId = $configurable_product->getId();
                    //  // Add Your Associated Product Ids.
                    // $configurable_product->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
                    // $configurable_product->setCanSaveConfigurableAttributes(true);
                    $configurable_product->save();
                    $setproductImage1 = $this->Imagefromidrive($configurable_product, $itemInfo['productImage'], $visible = false, 'image');
                    $setproductImage2 = $this->Imagefromidrive($configurable_product, $itemInfo['productImage'], $visible = false, 'small_image');
                    $setproductImage3 = $this->Imagefromidrive($configurable_product, $itemInfo['productImage'], $visible = false, 'thumbnail');
                    // $setproductHoverImage = $this->Imagefromidrive($configurable_product, $itemInfo['ProductHoverImage'], $visible = false, $imageType = []);
                    if ($configurable_product->getId())
                    {
                        return "Product Created";
                    }
            
                    } catch (Exception $e) {

                        return $e->getMessage();
                    }
                }else {
                    $assignedCategory = $itemInfo['productCategory'];
                    foreach($assignedCategory as $eachcategory){
                        $childcategory = explode('/', $eachcategory);
                        foreach($childcategory as $subcategory){
                            $category = $this->GetCategoryId($subcategory);
                            $categoryArray[] = $category;
                        }
                    }     
                    $websiteId = $this->storeManager->getStore()->getWebsiteId();
                    $productId = $IsProductexist->getId();
                    $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
                    $configurable_product = $this->productFactroy->create()->load($productId);
                    // $configurable_product->setSku($itemInfo['sku']);
                    $configurable_product->setName($itemInfo['productName']);
                    $configurable_product->setAttributeSetId(4);
                    $configurable_product->setStatus($itemInfo['active']);
                    $configurable_product->setVisibility(4); 
                    $configurable_product->setTypeId('configurable');
                    $configurable_product->setPrice($itemInfo['salePrice']);
                    $configurable_product->setUom($itemInfo['uom']);
                    $configurable_product->setWebsiteIds(array(1));
                    $configurable_product->setCategoryIds($categoryArray);
                    // $configurable_product->setUrlKey(strtolower($sku));
                    $configurable_product->setStockData(
                    array(
                        'use_config_manage_stock' => 100, 
                        'manage_stock' => 1, 
                        'min_sale_qty' => 1, 
                        'max_sale_qty' => 2, 
                        'is_in_stock' => 1,
                        'qty' => $itemInfo['quantity']
                        )
                    );
                    $configurable_product->setDimensions($itemInfo['dimensions']);
                    $configurable_product->setDimensionswidth($itemInfo['dimensionsWidth']);
                    $configurable_product->setDimensionslength($itemInfo['dimensionsLength']);
                    $configurable_product->setDimensionsvolume($itemInfo['dimensionsVolume']);
                    $configurable_product->setdimensionsdepth($itemInfo['dimensionsDepth']);
                    $configurable_product->setGrossweight($itemInfo['grossWeight']);
                    $configurable_product->setNetweight($itemInfo['netWeight']);
                    $configurable_product->setCartonheight($itemInfo['cartonHeight']);
                    $configurable_product->setcartonwidth($itemInfo['cartonWidth']);
                    $configurable_product->setCartonlength($itemInfo['cartonLength']);
                    $configurable_product->setCartonvolume($itemInfo['cartonVolume']);
                    $configurable_product->setCartongrossweight($itemInfo['cartonGrossWeight']);
                    $configurable_product->setCartonquantity($itemInfo['cartonQuantity']);
                    $configurable_product->setTargetMarket(implode(',', $itemInfo['targetMarket']));
                    $configurable_product->setOccasions(implode(',', $itemInfo['occasions']));
                    $configurable_product->setSeasons(implode(',', $itemInfo['seasons']));
                    $configurable_product->setIsFragile($itemInfo['isFragile']);
                    $configurable_product->setProductVideos(implode(',', $itemInfo['productVideos']));
                    $configurable_product->setLifeStyleImages(implode(',', $itemInfo['lifeStyleImages']));
                    $configurable_product->setIndividualpackagingafterprinting($itemInfo['individualPackagingAfterPrinting']);
                    // $product = $this->productRepository->getById('product-id');
                    // $configurable_product->setData('occasions',implode(',', $itemInfo['occasions']));
                    // $this->productRepository->save($product);
                    // $size_attr_id = $configurable_product->getResource()->getAttribute('size')->getId();
                    // $color_attr_id = $configurable_product->getResource()->getAttribute('color')->getId();
                    // $configurable_product->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id, $size_attr_id], $configurable_product);
                    // $configurableAttributesData = $configurable_product->getTypeInstance()->getConfigurableAttributesAsArray($configurable_product);
                    // $configurable_product->setConfigurableAttributesData($configurableAttributesData);
                    // $configurableProductsData = [];
                    // $configurable_product->setConfigurableProductsData($configurableProductsData);

                    // $productId = $configurable_product->getId();
                    // $associatedProductIds = [3784,3785]; // Add Your Associated Product Ids.
                    // $configurable_product->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
                    // $configurable_product->setCanSaveConfigurableAttributes(true);
                    $configurable_product->save();
                    $setproductImage1 = $this->Imagefromidrive($configurable_product, $itemInfo['productImage'], $visible = false, 'image');
                    $setproductImage1 = $this->Imagefromidrive($configurable_product, $itemInfo['productImage'], $visible = false, 'small_image');
                    $setproductImage1 = $this->Imagefromidrive($configurable_product, $itemInfo['productImage'], $visible = false, 'thumbnail');
                    
                    // $_product = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->getById(1, true);
                    // $options = [
                    //     [
                    //         'title' => 'radio option',
                    //         'type' => 'radio',
                    //         'is_require' => true,
                    //         'is_special_offer' => '30X30X60',
                    //         'image'=>'link1',
                    //         'description'=>'10',
                    //         'sort_order' => 4,
                    //         'values' => [
                    //             [
                    //                 'title' => 'FRONT(A)',
                    //                 'price' => 10,
                    //                 'price_type' => 'fixed',
                    //                 'sku' => 'sku1',
                    //                 'sort_order' => 1,
                    //             ],
                    //             [
                    //                 'title' => 'FRONT(B)',
                    //                 'price' => 20,
                    //                 'price_type' => 'fixed',
                    //                 'sku' => 'radio option 2 sku',
                    //                 'sort_order' => 2,
                    //             ],
                    //         ],
                    //     ]
                    // ];

                    // foreach ($options as $arrayOption) {
                    //     $option = $this->_objectManager->create(\Magento\Catalog\Model\Product\Option::class)
                    //         ->setProductId($configurable_product->getId())
                    //         ->setStoreId($configurable_product->getStoreId())
                    //         ->addData($arrayOption);
                    //     $option->save();
                    //     $configurable_product->addOption($option);
                    //     $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->save($configurable_product);
                    // }
                    if (!empty($configurable_product->getOptions())) {
                        foreach ($configurable_product->getOptions() as $option) {
                            $option->delete();
                        }
                        
                        $configurable_product->setHasOptions(0)->save();
                    } 

                  
                    if($itemInfo['PrintingAllowed'] == "Yes"){
                        $printingData = $itemInfo['printingData'];
                        // $printingData = $itemInfo['printingData'][0]['printingTypes'];
                       
                       $printingOptions = [];
                        
                        foreach($printingData as $printingTypes){
                           
                            $printingInfo = [];
                            $printingOptionValues = [];
                            $printingOptionValuesTypes = [];
                            $productPrintingTypes = $printingTypes['printingTypes'];
                            foreach($productPrintingTypes as $productPrinting){
                                $printingOptionValuesTypes['title'] = $productPrinting['printingType'];
                                $printingOptionValuesTypes['price'] = $productPrinting['printingCost'];
                                $printingOptionValuesTypes['price_type'] = 'fixed';
                                $printingOptionValuesTypes['sku'] = 'HK0002_00195';
                                $printingOptionValuesTypes['description'] = $productPrinting['maxColor'];;
                                $printingOptionValuesTypes['sort_order'] = 0;
                                array_push($printingOptionValues,$printingOptionValuesTypes);
                            }
                           
                            $printingInfo['title'] = $printingTypes['name'];
                            $printingInfo['type'] = 'radio';
                            $printingInfo['is_required'] = 0;
                            $printingInfo['is_special_offer'] = $printingTypes['maxHeight']."X".$printingTypes['maxWidth'];
                            $printingInfo['image'] = $printingTypes['printingRefImage'];
                            $printingInfo['sort_order'] = 0;
                            $printingInfo['values'] = $printingOptionValues;
                            array_push($printingOptions,$printingInfo);
                           
                            
                        }
                    }
                   
                   
                  

                    $options = [
                        [
                            'title' => 'FRONT TOP(A)',
                            'type' => 'radio',
                            'is_required' => 0,
                            'is_special_offer' => '50X20',
                            'image'=>'https://s7h4.c1.e2-9.dev/hakprod/hakprod/HAK130724/03/cb/03cb729a96a71641ddd33160855980cf0c804435.jpeg',
                            // 'description'=>'10',
                            'sort_order' => 0,
                            'values' => [
                                [
                                    'title' => 'SCREEN PRINTING ',
                                    'price' => 50,
                                    'price_type' => 'fixed',
                                    'sku' => 'HK0002_00195',
                                    'description' => '5',
                                    'sort_order' => 0,
                                ]
                            ]
                        ],
                        [
                            'title' => 'FRONT LEFT(B)',
                            'type' => 'radio',
                            'is_required' => 0,
                            'is_special_offer' => '100X20',
                            'image'=>'https://s7h4.c1.e2-9.dev/hakprod/hakprod/HAK130724/03/cb/03cb729a96a71641ddd33160855980cf0c804435.jpeg',
                            'sort_order' => 0,
                            'values' => [
                                [
                                    'title' => 'UV PRINTING',
                                    'price' => 40,
                                    'price_type' => 'fixed',
                                    'sku' => 'HK0002_00196',
                                    'description'=>'10',
                                    'sort_order' => 0,
                                ],
                                [
                                    'title' => 'SCREEN PRITING',
                                    'price' => 50,
                                    'price_type' => 'fixed',
                                    'sku' => 'HK0002_00196',
                                    'description'=>'5',
                                    'sort_order' => 0,
                                ],
                            ]
                            ],
                            [
                                'title' => 'FRONT RIGHT(C)',
                                'type' => 'radio',
                                'is_required' => 0,
                                'is_special_offer' => '100X20',
                                'image'=>'https://s7h4.c1.e2-9.dev/hakprod/hakprod/HAK130724/03/cb/03cb729a96a71641ddd33160855980cf0c804435.jpeg',
                                'sort_order' => 0,
                                'values' => [
                                    [
                                        'title' => 'UV PRINTING',
                                        'price' => 45,
                                        'price_type' => 'fixed',
                                        'sku' => 'HK0002_00197',
                                        'description'=>'10',
                                        'sort_order' => 0,
                                    ],
                                    [
                                        'title' => 'SCREEN PRITING',
                                        'price' => 55,
                                        'price_type' => 'fixed',
                                        'sku' => 'HK0002_00197',
                                        'description'=>'5',
                                        'sort_order' => 0,
                                    ],
                                ]
                                ],
                                [
                                    'title' => 'FRONT BOTTOM(D)',
                                    'type' => 'radio',
                                    'is_required' => 0,
                                    'is_special_offer' => '50X20',
                                    'image'=>'https://s7h4.c1.e2-9.dev/hakprod/hakprod/HAK130724/03/cb/03cb729a96a71641ddd33160855980cf0c804435.jpeg',
                                    'sort_order' => 0,
                                    'values' => [
                                        [
                                            'title' => 'SCREEN PRITING',
                                            'price' => 60,
                                            'price_type' => 'fixed',
                                            'sku' => 'HK0002_00198',
                                            'description'=>'5',
                                            'sort_order' => 0,
                                        ],
                                    ]
                                ],
                                [
                                        'title' => 'INSIDE NOTEBOOK(E)',
                                        'type' => 'radio',
                                        'is_required' => 0,
                                        'is_special_offer' => '130X70',
                                        'image'=>'https://s7h4.c1.e2-9.dev/hakprod/hakprod/HAK130724/03/cb/03cb729a96a71641ddd33160855980cf0c804435.jpeg',
                                        'sort_order' => 0,
                                        'values' => [
                                            [
                                                'title' => 'SCREEN PRITING',
                                                'price' => 75,
                                                'price_type' => 'fixed',
                                                'sku' => 'HK0002_00199',
                                                'description'=>'5',
                                                'sort_order' => 0,
                                            ],
                                        ]
                                ]
                    ];
                
                $configurable_product->setHasOptions(1);
                // $product->setCanSaveCustomOptions(true);
                
                foreach ($printingOptions as $arrayOption) {
                    $customOption = $this->_objectManager->create('\Magento\Catalog\Model\Product\Option')
                                        ->setProductId($configurable_product->getId())
                                        ->setStoreId($configurable_product->getStoreId())
                                        ->addData($arrayOption);
                
                    $customOption->save();
                    $configurable_product->addOption($customOption);
                }
                    
                    return "Product Updated Successfully";
                }
            }else {
                $data = ["status"=>"Product has empty attributes,Please verify", "message"=> $productStatus];
                // return $data;
                $model = $this->_Productmasterlog->create();
                $model->addData([
                    "jsonresponse" => 'jsonresponse empty attributes',
                    "message" => json_encode($data),
                    "typeofapi" => 'ProductSync',
                    "status" => 'Failed',
                    "requesteid" => 'requesteid'
                    ]);
                $saveData = $model->save();
                throw new \Magento\Framework\Webapi\Exception(    __(json_encode($data)),0,\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            }
    }

    public function CreateSimpleProducts($itemInfo,$requestData){
        $product = $this->newProduct;     
        $productStatus = $this->ValidateData($itemInfo);
        $categoryArray = [];
        $validatedjson = $productStatus;
        unset($validatedjson[0]);

        // $assignedCategory = $itemInfo['productCategory'];
        // foreach($assignedCategory as $eachcategory){
        //     $childcategory = explode('/', $eachcategory);
        //     foreach($childcategory as $subcategory){
        //         $category = $this->GetCategoryId($subcategory);
        //         $categoryArray[] = $category;
        //     }
        // }    


        if(empty($validatedjson)) {
            $sku = $itemInfo['sku'];
            $IsProductexist = $this->getProductBySku($sku);
            $IsParentexist = $this->getProductBySku($itemInfo['parentProduct']);
            if ($IsParentexist == false){
                $data = ["status"=>"False", "message"=> "Parent Product not available.Please create parent product"];
                $model = $this->_Productmasterlog->create();
                $model->addData([
                    "jsonresponse" => json_encode($requestData),
                    "message" => json_encode($data),
                    "typeofapi" => 'ProductSync',
                    "status" => 'Failed',
                    "requesteid" => 'requesteid'
                    ]);
                $saveData = $model->save();
                throw new \Magento\Framework\Webapi\Exception(    __(json_encode( $data)),0,\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            }
            if ($IsProductexist == false){
                try { 
                    $product->setSku($sku);
                    $product->setName($itemInfo['productName']); 
                    $product->setAttributeSetId(4); 
                    $product->setStatus($itemInfo['active']); 
                    $product->setWeight(1);
                    $product->setVisibility(1); 
                    $product->setWebsiteIds(array(1)); 
                    $product->setTaxClassId(0); 
                    $product->setTypeId('simple'); 
                    $product->setShortDescription($itemInfo['description']);
                    $product->setDescription($itemInfo['description']);
                    $product->setPrice($itemInfo['salePrice']);
                    $product->setUrlKey(strtolower($sku));
                    // $product->setCategoryIds($categoryArray);
                    $product->save();
                    // $product->setDimensions($itemInfo['dimensions']);
                    // $product->setData('dimensions', $itemInfo['uom']);
                    // $product->setCustomAttribute('dimensions', $itemInfo['dimensions']);
                    // $product->setCustomAttribute('dimensionsWidth', $itemInfo['dimensionsWidth']);
                    // $product->setCustomAttribute('dimensionsLength', $itemInfo['dimensionsLength']);
                    // $product->setCustomAttribute('dimensionsVolume', $itemInfo['dimensionsVolume']);
                    // $product->setCustomAttribute('dimensionsDepth', $itemInfo['dimensionsDepth']);
                    // $product->setCustomAttribute('grossWeight', $itemInfo['grossWeight']);
                    // $product->setCustomAttribute('netWeight', $itemInfo['netWeight']);
                    // $product->setCustomAttribute('cartonHeight', $itemInfo['cartonHeight']);
                    // $product->setCustomAttribute('cartonWidth', $itemInfo['cartonWidth']);
                    // $product->setCustomAttribute('cartonLength', $itemInfo['cartonLength']);
                    // $product->setCustomAttribute('cartonVolume', $itemInfo['cartonVolume']);
                    // $product->setCustomAttribute('cartonGrossWeight', $itemInfo['cartonGrossWeight']);
                    // $product->setCustomAttribute('cartonQuantity', $itemInfo['cartonQuantity']);
                    // $product->setCustomAttribute('individualPackagingAfterPrinting', $itemInfo['individualPackagingAfterPrinting']);
                    $product->setCustomAttribute('uom', $itemInfo['uom']);
                    $color_attr = $product->getResource()->getAttribute('color');
                    $colortattribute = $this->helperApi->getattributedetails('color');
                    if (in_array($product->getColor(), $colortattribute))
                    {
                        if ($color_attr->usesSource()) {
                            $colour_opt = $color_attr->getSource()->getOptionId($itemInfo['color']);
                            // if ($available_color != $colour_opt) {
                              $product->setData('color', $colour_opt);
                            //   $prod->getResource()->saveAttribute($product, 'color');
                            // }
                        }
                    }else{
                        $newcolorid = $this->helperApi->createOrGetId('color', $itemInfo['color']);
                        $colour_opt = $color_attr->getSource()->getOptionId($itemInfo['color']);
                        $product->setData('color', $colour_opt);
                    }
                    $sizeattribute = $this->helperApi->getattributedetails('size');
                    $size_attr = $product->getResource()->getAttribute('size');
                    if (in_array($product->getSize(), $sizeattribute))
                    {
                        if ($size_attr->usesSource()) {
                            $size_opt = $size_attr->getSource()->getOptionId($itemInfo['size']);
                              $product->setData('size', $size_opt);
                        }

                    }
                    
                    $product->setStockData(
                    array(
                        'use_config_manage_stock' => 100, 
                        'manage_stock' => 1, 
                        'min_sale_qty' => 1, 
                        'max_sale_qty' => 2, 
                        'is_in_stock' => 1,
                        'qty' => $itemInfo['quantity']
                        )
                    );
                    $product->save();
                    
                    
                    // $categoryIds = array('2','3');
                    // $category = $objectManager->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
                    // $category->assignProductToCategories($sku, $categoryIds);
                    $setproductImage1 = $this->Imagefromidrive($product, $itemInfo['productImage'], $visible = false, 'image');
                    $setproductImage2 = $this->Imagefromidrive($product, $itemInfo['productImage'], $visible = false, 'small_image');
                    $setproductImage3 = $this->Imagefromidrive($product, $itemInfo['productImage'], $visible = false, 'thumbnail');
                    // $setproductImage2 = $this->Imagefromidrive($product, $itemInfo['Product Image1'], $visible = false, 'small_image');
                    // $setproductImage3 = $this->Imagefromidrive($product, $itemInfo['Product Image2'], $visible = false, 'thumbnail');
     
                    if ($product->getId())
                    {
                        if($itemInfo['parentProduct']){
                            $simpleID = $product->getId();
                            $this->AssignSimpletoConfig($itemInfo,$simpleID);
                        }
                        return  "Product Created";
                    }
            
                    } catch (Exception $e) {

                        return $e->getMessage();
                    }
                }else {
                    // $product->setData('store_id', 0);
                    
                    $websiteId = $this->storeManager->getStore()->getWebsiteId();
                    $productId = $IsProductexist->getId();
                    $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
                    $product = $this->productFactroy->create()->load($productId);
                    // $product->addAttributeUpdate("name", "Product Name", $storeId);
                    // $product->setSku($sku);
                    $product->setName($itemInfo['productName']); 
                    $product->setStatus($itemInfo['active']);   
                    $product->setPrice($itemInfo['salePrice']);  
                    $product->setShortDescription($itemInfo['description']);
                    $product->setDescription($itemInfo['description']);
                    $product->setUom($itemInfo['uom']);
                    $product->setCategoryIds($categoryArray);
                    // $product->setCustomAttribute('dimensions', $itemInfo['dimensions']);
                    // $product->setCustomAttribute('dimensionsWidth', $itemInfo['dimensionsWidth']);
                    // $product->setCustomAttribute('dimensionsLength', $itemInfo['dimensionsLength']);
                    // $product->setCustomAttribute('dimensionsVolume', $itemInfo['dimensionsVolume']);
                    // $product->setCustomAttribute('dimensionsDepth', $itemInfo['dimensionsDepth']);
                    // $product->setCustomAttribute('grossWeight', $itemInfo['grossWeight']);
                    // $product->setCustomAttribute('netWeight', $itemInfo['netWeight']);
                    // $product->setCustomAttribute('cartonHeight', $itemInfo['cartonHeight']);
                    // $product->setCustomAttribute('cartonWidth', $itemInfo['cartonWidth']);
                    // $product->setCustomAttribute('cartonLength', $itemInfo['cartonLength']);
                    // $product->setCustomAttribute('cartonVolume', $itemInfo['cartonVolume']);
                    // $product->setCustomAttribute('cartonGrossWeight', $itemInfo['cartonGrossWeight']);
                    // $product->setCustomAttribute('cartonQuantity', $itemInfo['cartonQuantity']);
                    // $product->setCustomAttribute('individualPackagingAfterPrinting', $itemInfo['individualPackagingAfterPrinting']);
                    // $product->setData('uom', $itemInfo['uom']);
                    // $product->setCustomAttribute('uom', $itemInfo['uom']);
                    $color_attr = $product->getResource()->getAttribute('color');
                    $colortattribute = $this->helperApi->getattributedetails('color');
                    if (in_array($product->getColor(), $colortattribute))
                    {
                        if ($color_attr->usesSource()) {
                            $colour_opt = $color_attr->getSource()->getOptionId($itemInfo['color']);
                            // if ($available_color != $colour_opt) {
                              $product->setData('color', $colour_opt);
                            //   $prod->getResource()->saveAttribute($product, 'color');
                            // }
                        }
                    }else{
                        $newcolorid = $this->helperApi->createOrGetId('color', $itemInfo['color']);
                        $colour_opt = $color_attr->getSource()->getOptionId($itemInfo['color']);
                        $product->setData('color', $colour_opt);
                    }
                    $sizeattribute = $this->helperApi->getattributedetails('size');
                    $size_attr = $product->getResource()->getAttribute('size');
                    if (in_array($product->getSize(), $sizeattribute))
                    {
                        if ($size_attr->usesSource()) {
                            $size_opt = $size_attr->getSource()->getOptionId($itemInfo['size']);
                            $product->setData('size', $size_opt);
                        }
                    }else {
                        $newsizeid = $this->helperApi->createOrGetId('size', $itemInfo['size']);
                        $size_opt = $size_attr->getSource()->getOptionId($itemInfo['size']);
                        $product->setData('size', $size_opt);
                    }
                    $product->save();
                    if($itemInfo['ParentProduct']){
                        $simpleID = $product->getId();
                        $this->AssignSimpletoConfig($itemInfo,$simpleID);
                    }
                    $setproductImage1 = $this->Imagefromidrive($product, $itemInfo['productImage'], $visible = false, 'image');
                    $setproductImage2 = $this->Imagefromidrive($product, $itemInfo['productImage'], $visible = false, 'small_image');
                    $setproductImage3 = $this->Imagefromidrive($product, $itemInfo['productImage'], $visible = false, 'thumbnail');
                    return "Product Updated Successfully";
                }
            }else {
                $data = ["status"=>"Product has empty attributes,Please verify", "message"=> $productStatus];
                $model = $this->_Productmasterlog->create();
                $model->addData([
                    "jsonresponse" => json_encode($requestData),
                    "message" => json_encode($data),
                    "typeofapi" => 'ProductSync',
                    "status" => 'Failed',
                    "requesteid" => 'requesteid'
                    ]);
                $saveData = $model->save();
                throw new \Magento\Framework\Webapi\Exception(    __(json_encode( $data)),0,\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            }
    }

    public function AssignSimpletoConfig($itemInfo,$simpleID){

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $IsProductexist = $this->getProductBySku($itemInfo['parentProduct']);
        $productId = $IsProductexist->getId();
        // echo $productId;
        // die();
        $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $configurable_product = $this->productFactroy->create()->load($productId);
        // $configurable_product->setSku($itemInfo['sku']);
        $configurable_product->setName($itemInfo['productName']);
        $configurable_product->setAttributeSetId(4);
        $configurable_product->setStatus($itemInfo['active']);
        $configurable_product->setVisibility(4); 
        $configurable_product->setTypeId('configurable');
        $configurable_product->setPrice($itemInfo['salePrice']);
        $configurable_product->setUom($itemInfo['uom']);
        $configurable_product->setWebsiteIds(array(1));
        // $configurable_product->setCategoryIds($categoryArray);
        // $configurable_product->setUrlKey(strtolower($sku));
        $configurable_product->setStockData(
                    array(
                        'use_config_manage_stock' => 100, 
                        'manage_stock' => 1, 
                        'min_sale_qty' => 1, 
                        'max_sale_qty' => 2, 
                        'is_in_stock' => 1,
                        'qty' => $itemInfo['quantity']
                        )
                    );
        $size_attr_id = $configurable_product->getResource()->getAttribute('size')->getId();
        $color_attr_id = $configurable_product->getResource()->getAttribute('color')->getId();
        $configurable_product->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id, $size_attr_id], $configurable_product);
        $configurableAttributesData = $configurable_product->getTypeInstance()->getConfigurableAttributesAsArray($configurable_product);
        $configurable_product->setConfigurableAttributesData($configurableAttributesData);
        $configurableProductsData = [];
        $configurable_product->setConfigurableProductsData($configurableProductsData);
        $children = $configurable_product
        ->getTypeInstance()
        ->getChildrenIds($configurable_product->getId());
        $simpleItem = [];
        if($children){
            foreach($children[0] as $childId){
                $simpleItem[] = $childId;
            }
        }
        $simpleItem[] = $simpleID;
        $associatedProductIds = $simpleItem; // Add Your Associated Product Ids.
        $configurable_product->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
        $configurable_product->setCanSaveConfigurableAttributes(true);
        $configurable_product->save();

    }
    public function ValidateData($itemInfo){
        $data = [];
        $Optional = ['productHoverImage','productImage2','productImage1','productImage','dimensions','dimensionsWidth','dimensionsLength','dimensionsVolume','dimensionsDepth','grossWeight','netWeight','cartonHeight','cartonWidth','cartonLength','cartonVolume','cartonGrossWeight','cartonQuantity','individualPackagingAfterPrinting'];
        foreach($Optional as $optionalfields){
            unset($itemInfo[$optionalfields]);
        }
        if($itemInfo['productType'] == 'parent'){
            unset($itemInfo["color"]);
            unset($itemInfo["size"]);
            unset($itemInfo["printingData"]);
        }
        if($itemInfo['productType'] == 'simple'){
            unset($itemInfo["variants"]);
        }
        foreach($itemInfo as $itemkey =>  $itemvalue){
            $dataItems = [];
            if($itemkey == 'sku'){
                $dataItems['sku'] =  $itemvalue;
                array_push($data, $dataItems);
            }
            if (empty($itemvalue) && $itemvalue != 0){
                $dataItems['message'] = "Empty Data";
                $dataItems['status'] = true;
                $dataItems['attribute'] = $itemkey;
                array_push($data, $dataItems);
            } 
            if($itemkey == 'printingData' && $itemInfo['printingAllowed'] == 'Yes') {              
                foreach($itemvalue as $printdata){
                    foreach($printdata as $printkey => $printvalues){
                        if (empty($printvalues)){
                            $dataItems['message'] = "Empty Data";
                            $dataItems['status'] = true;
                            $dataItems['attribute'] = $printkey;
                            array_push($data, $dataItems);
                        }
                        if($printkey == 'printing_types' ) {
                            foreach($printvalues as  $typeofprintvalue){
                                foreach($typeofprintvalue as  $typekey => $typevalue){
                                if (empty($typevalue)){
                                    $dataItems['message'] = "Empty Data";
                                    $dataItems['status'] = true;
                                    $dataItems['attribute'] = $typekey;
                                    array_push($data, $dataItems);
                                }
                            }
                            }
                        }

                    }
                }
            }
        }
        return $data;
    }
    public function GetCategoryId($eachcategory){
        // get the current stores root category
        $categoryName = $eachcategory;
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
        return $cate->getId();
        if (!$cate->getId()) {
            $category->setPath($parentCategory->getPath())
                ->setParentId($parentId)
                ->setName($categoryName)
                ->setIsactive(true);
            $category->save();
        }

        return $category;
    }
    public function  getDownloadImage($type,$file){
        $path = str_replace("index.php","",$_SERVER["SCRIPT_FILENAME"]);        
        $import_location = $path.'media/catalog/';
        if (!file_exists($import_location)){
            mkdir($import_location, 0755);
        }
        $import_location = $path.'media/catalog/'.$type.'/';
        if (!file_exists($import_location)){
            mkdir($import_location, 0755);
        }

        // todo check if last character has /

        $file_source = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_imageurl',Mage::app()->getStore()).$file;
        $file_target = $import_location."/".basename($file);

        $file_path = "";
        if (($file != '') and (!file_exists($file_target))){
            $rh = fopen($file_source, 'rb');
            $wh = fopen($file_target, 'wb');
            if ($rh===false || $wh===false) {
                // error reading or opening file
                $file_path = "";
            }
            while (!feof($rh)) {
                if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                    $file_path = $file_target;
                }
            }
            fclose($rh);
            fclose($wh);
        }
        if (file_exists($file_target)){
            if ($type == 'category'){
                $file_path = $file;
            }else{
                $file_path = $file_target;
            }           
        }

        return $file_path;
    } 
    public function getactiveCategoryCollection()
    {
        // $collection = $this->_categoryCollectionFactory->create();
        // $collection->addAttributeToSelect('*');        
        // $collection->addIsactiveFilter();
        // return $collection;

	return $storeCategories;
    }
    public function getProductBySku($sku) 
    { 
        // return $this->productRepository->get($sku); 
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            $product = false;
        }
        return $product;
    } 
    public function Imagefromidrive($product, $imageUrl, $visible = true, $imageType = [])
    {
        $tmpDir = $this->getMediaDirTmpDir();
        $this->file->checkAndCreateFolder($tmpDir);
        $newFileName = $tmpDir . baseName($imageUrl);
        $result = $this->file->read($imageUrl, $newFileName);
        if ($result) {
            $product->addImageToMediaGallery($newFileName, $imageType, true, false);
            $product->save();
        }
        // return $newFileName;
    }
    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

}