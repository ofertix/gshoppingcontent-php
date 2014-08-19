<?php
/**
 * Example to get all subaccounts or a subset.
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

// Get all accounts
$allAccounts = $client->getAccounts();
echo 'Total Number of Accounts: ' . count($allAccounts->getAccounts()) . "\n";

// Get list containing first account
$maxResults = "1";
$feed = $client->getAccounts($maxResults);
echo 'Total Results: ' . $feed->getTotalResults() . "\n";
$accounts = $feed->getAccounts();
$account = $accounts[0];
echo 'First Account: ' . $account->getTitle() . "\n";

// Get list containing second account (and so on, paging through results)
$maxResults = "1";
$startIndex = "2";
$nextFeed = $client->getAccounts($maxResults, $startIndex);
$accounts = $nextFeed->getAccounts();
$account = $accounts[0];
echo 'Second Account: ' . $account->getTitle() . "\n";

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
