<?php
/**
 * Example to update a product.
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

// Now enter some product data
$product = buildMockProduct("SKU123", "US", "en");

// Finally send the data to the API
$entry = $client->insertProduct($product);
echo 'Inserted: ' . $entry->getTitle() . "\n";
echo 'Price: ' . $entry->getPrice() . "\n\n";

// Update the price and send updated product
$entry->setPrice("20", "usd");
$updatedEntry = $client->updateProduct($entry);

echo 'Inserted: ' . $updatedEntry->getTitle() . "\n";
echo 'Price: ' . $updatedEntry->getPrice() . "\n\n";

/**
 * Credentials - Enter your own values
 *
 * @author afshar@google.com
**/
class Credentials {
    public static function get() {
        return array(
            "merchantId" => "7842698",
            "email" => "aafshar@gmail.com",
            "password" => "subhanul",
        );
    }
}

?>
