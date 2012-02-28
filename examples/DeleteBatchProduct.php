<?php
/**
 * Example to insert a product.
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
 * @version 1.1
 * @author afshar@google.com, dhermes@google.com
 * @copyright Google Inc, 2011
 * @package GShoppingContent
 */

// import our library
require_once('GShoppingContent.php');

// Get the user credentials
$creds = Credentials::get();

// Create a client for our merchant and log in
$client = new GSC_Client($creds["merchantId"]);
$client->login($creds["email"], $creds["password"]);

// Iinsert a product so we can delete it
$id = "SKU124";
$country = "US";
$language = "en";
$product = new GSC_Product();
$product->setSKU($id);
$product->setTargetCountry($country);
$product->setContentLanguage($language);

$product->setCondition("new");
$product->setTitle("Noname XX500-21P Ethernet Switch - 21 Port - 10/100/1000 Base-T");
$product->setProductLink("http://www.example.com/sku124");
$product->setPrice("11", "usd");
$product->setDescription("21 Port - 10/100/1000 Base-T, very fast.");
$product->setGoogleProductCategory("Electronics &gt; Networking &gt; Hubs &amp; Switches");
$product->setAvailability("in stock");
$product->addTax("US", "CA", "4.25", "true");

$product = $client->insertProduct($product);
echo('Inserted: ' . $product->getTitle() . "\n");

// Create a dummy <atom:entry> element to include as a batch delete
$dummyDeleteEntry = new GSC_Product();
$dummyDeleteEntry->setBatchOperation("delete");

// set the atom id element as specified by the batch protocol
$editLink = $client->getProductUri($id, $country, $language);
$dummyDeleteEntry->setAtomId($editLink);

$batch = new GSC_ProductList();
$batch->addProduct($dummyDeleteEntry);

// Finally send the data to the API
$feed = $client->batch($batch);
$products = $feed->getProducts();
$operation = $products[0];
echo('Deleted: ' . $operation->getTitle() . "\n");
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
