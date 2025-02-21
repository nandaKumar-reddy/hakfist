<?php
namespace Hakfist\ProductSync\Api;
interface PostManagementInterface {


    /**
     * GET for Post api
     * @param string $value
     * @return string
     */

    public function customGetMethod($value);

    /**
     * SET form data
     * @param mixed $productarray
     * @return mixed
     */

    public function customPostMethod($productarray);

}