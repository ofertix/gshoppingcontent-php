<?php
/**
 * Example to use the warnings parameter.
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
$product = buildMockProduct("SKU124", "US", "en");

// Finally send the data to the API
$warnings = true;
$dryRun = false;
$entry = $client->insertProduct($product, $warnings, $dryRun);
echo 'Inserted: ' . $entry->getTitle() . "\n\n";

$warnings = $entry->getWarnings(); // A DOMNodeList
$count = $warnings->length;
for($pos=0; $pos<$count; $pos++) {
    $warning = $warnings->item($pos);
    echo 'Warning ' . ($pos + 1) . "\n";
    echo 'Code: ' . $entry->getWarningCode($warning) . "\n";
    echo 'Domain: ' . $entry->getWarningDomain($warning) . "\n";
    echo 'Location: ' . $entry->getWarningLocation($warning) . "\n";
    echo 'Message: ' . $entry->getWarningMessage($warning) . "\n\n";
}

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
