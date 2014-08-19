<?php
/**
 * Mock product builder to help examples.
 *
 * Copyright 2011 Google, Inc
 *
 *   Licensed under the Apache License, Version 2.0 (the "License"); you may not
 *   use this file except in compliance with the License.  You may obtain a copy
 *   of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 *   WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
 *   License for the specific language governing permissions and limitations
 *   under the License.
 *
 * @version 1.3
 * @author dhermes@google.com
 * @copyright Google Inc, 2011
 * @package GShoppingContent
 */

// import our library
require_once('GShoppingContent.php');

/**
 * Build a mock product.
 *
 * @param string $id The mock product id.
 * @param string $country The mock product country.
 * @param string $language The mock product language.
 * @return GSC_Product The mock product we built.
 * @author dhermes@google.com
 **/
function buildMockProduct($id, $country, $language)
{
    $product = new GSC_Product();
    $product->setSKU($id);
    $product->setTargetCountry($country);
    $product->setContentLanguage($language);

    $product->setCondition("new");
    $title = "Noname XX500-42P Ethernet Switch - 42 Port - 10/100/1000 Base-T";
    $product->setTitle($title);
    $product->setProductLink("http://www.example.com/sku123");
    $product->setPrice("25", "usd");
    $product->setDescription("42 Port - 10/100/1000 Base-T, very fast.");
    $gpc = "Electronics &gt; Networking &gt; Hubs &amp; Switches";
    $product->setGoogleProductCategory($gpc);
    $product->setAvailability("in stock");
    $shippingService = "Speedy Shipping - Ground";
    $product->addShipping("US", "MA", "5.95", "USD", $shippingService);
    $product->addTax("US", "CA", "8.25", "true");

    return $product;
}

?>
