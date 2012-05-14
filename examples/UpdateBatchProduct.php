<?php
/**
 * Example to update a product in batch.
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
 * @version 1.2
 * @author dhermes@google.com
 * @copyright Google Inc, 2011
 * @package GShoppingContent
 */

// import our library
require_once('GShoppingContent.php');
require_once('BuildMockProduct.php');

// Get the user credentials
$creds = Credentials::get();

// Create a client for our merchant and log in
$client = new GSC_Client($creds["merchantId"]);
$client->login($creds["email"], $creds["password"]);

// Insert a product so we can update it in a batch request
$product = buildMockProduct("SKU123", "US", "en");

$product = $client->insertProduct($product);
echo('Inserted: ' . $product->getTitle() . "\n");

// Update the price and add updated product to batch
$product->setPrice("20", "usd");
$product->setBatchOperation("update");
$batch = new GSC_ProductList();
$batch->addProduct($product);

// Finally send the data to the API
$feed = $client->batch($batch);
$products = $feed->getProducts();
$operation = $products[0];
echo('Updated: ' . $operation->getTitle() . "\n");
echo('Status: ' . $operation->getBatchStatus() . "\n");

/**
 * Credentials - Enter your own values
 *
 * @author afshar@google.com
**/
class Credentials {
    public static function get() {
        return array(
            "merchantId" => "7852698",
            "email" => "jsmith@gmail.com",
            "password" => "XXXXXX",
        );
    }
}

?>
