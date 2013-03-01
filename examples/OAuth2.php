<?php
/**
 * Example to authorize and authenticate with .
 *
 * Copyright 2013 Google, Inc
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
 * @version 1.4
 * @author Burcu Dogan (jbd@google.com)
 * @copyright Google Inc, 2013
 * @package GShoppingContent
 */

// import our library
require_once('GShoppingContent.php');

// Your merchant center account id.
const ACCOUNT_ID = '1234567';

// CLIENT_ID, CLIENT_SECRET, APPLICATION_REDIRECT_URL are avaible on
// Google APIs Console (https://code.google.com/apis/console/)
const CLIENT_ID = 'YOUR CLIENT ID';
const CLIENT_SECRET = 'YOUR CLIENT SECRET';
const APPLICATION_REDIRECT_URI = 'YOUR REDIRECT URI';
const USER_AGENT = 'content-api-example';

$authToken = new GSC_OAuth2Token(
    CLIENT_ID, CLIENT_SECRET, APPLICATION_REDIRECT_URI, USER_AGENT);

// generate a consent page url
$authorizeUrl = $authToken->generateAuthorizeUrl(
  APPLICATION_REDIRECT_URI, 'force');
echo "Please visit: $authorizeUrl\n";

// Your browser will be redirected to
// http://your_redirection_url/?code=[CODE]
// Copy the code and enter it after the prompt.

// read the code from console
$code = readline('Please enter the code: ');
$authToken->getAccessToken($code);

// Create a client for our merchant and log in
$client = new GSC_Client(ACCOUNT_ID);
$client->setToken($authToken);

// retrieve all product items
$productFeed = $client->getProducts();
$products = $productFeed->getProducts();
foreach ($products as $product) {
  echo $product->getTitle() . "\n";
}
