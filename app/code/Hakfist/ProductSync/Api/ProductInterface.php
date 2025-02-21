<?php

namespace Hakfist\ProductSync\Api;

interface ProductInterface
{
    /**
     * GET for Post api
     * @param string $value
     * @return string
     */
    public function getPost($value);
}