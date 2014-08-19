<?php
/**
 * Example to update a subaccount.
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

// Get the user credentials
$creds = Credentials::get();

// Create a client for our merchant and log in
$client = new GSC_Client($creds["merchantId"]);
$client->login($creds["email"], $creds["password"]);

// Now enter some account data
$account = new GSC_ManagedAccount();
$account->setTitle("ABC Store");
$account->setDescription("Description of ABC Store");
$account->setAccountLink("http://abcstore.example.com/new");
$account->setAdultContent("no");
$account->setInternalId("BCDE");
$account->setReviewsUrl("http://my.site.com/reviews?mo=user-rating");

$insertedAccount = $client->insertAccount($account);
echo 'Inserted: ' . $insertedAccount->getAtomId() . "\n";
echo 'Original Title: ' . $insertedAccount->getTitle() . "\n";

// Now update the title
$insertedAccount->setTitle("DEF Store");
$updatedAccount = $client->updateAccount($insertedAccount);
echo 'Updated: ' . $updatedAccount->getAtomId() . "\n";
echo 'Updated Title: ' . $updatedAccount->getTitle() . "\n";

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
