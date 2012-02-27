<?php

/**
 * PHP library for interacting with Google Content API for Shopping.
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
 * @example examples/InsertProduct.php Inserting a product
 * @example examples/InsertBatchProduct.php Making a batch request
 **/


/**
 * URI for ClientLogin requests.
 *
 * @global string the URI for client login crap
 * @name CLIENTLOGIN_URI
 * @package GShoppingContent
 **/
const CLIENTLOGIN_URI = 'https://www.google.com/accounts/ClientLogin';

/**
 * Service name for ClientLogin.
 **/
const CLIENTLOGIN_SVC = 'structuredcontent';

/**
 * User Agent string for all requests.
 **/
const USER_AGENT = 'scapi-php';

/**
 * Base API URI.
 **/
const BASE = 'https://content.googleapis.com/content/v1/';



/**
 * HTTP Response
 *
 * Wraps the CURL response and information data of the response.
 *
 * @package GShoppingContent
 * @version 1.1
 * @author afshar@google.com, dhermes@google.com
 **/
class  _GSC_Response
{

    /**
     * HTTP response body.
     *
     * @var string
     **/
    public $body;

    /**
     * HTTP response code.
     *
     * @var int
     **/
    public $code;

    /**
     * Http response content type.
     *
     * @var string
     **/
    public $content_type;

    /**
     * Create a new _GSC_Response instance.
     *
     * @param array $info The info result from CURL after making a request.
     * @param string $body The response body.
     * @author afshar@google.com
     **/
    function __construct($info, $body)
    {
        $this->code = $info['http_code'];
        $this->content_type = $info['content_type'];
        $this->body = $body;
    }

}


/**
 * HTTP client
 *
 * A thin wrapper around CURL to ease the repetitive tasks such as adding
 * Authorization headers.
 *
 * This class is entirely static, and all functions are designed to be used
 * statically. It maintains no state.
 *
 * @package GShoppingContent
 * @version 1.1
 * @author afshar@google.com, dhermes@google.com
 * @copyright Google Inc, 2011
 **/
class _GSC_Http
{
    /**
     * Make an HTTP GET request with a Google Authorization header.
     *
     * @param string $uri The URI to post to.
     * @param string $auth The authorization token.
     * @return _GSC_Response The response to the request.
     **/
    public static function get($uri, $auth) {
        $ch = self::ch();
        $headers = array(
            'Content-Type: application/atom+xml',
            'Authorization: ' . $auth
        );
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return self::req($ch);
    }

    /**
     * Post fields as an HTTP form.
     *
     * @param string $uri The URI to post to.
     * @param array $fields The form fields to post.
     * @return _GSC_Response The response to the request.
     **/
    public static function postForm($uri, $fields)
    {
        $ch = self::ch();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        return self::req($ch);
    }

    /**
     * Make an HTTP POST request with a Google Authorization header.
     *
     * @param string $uri The URI to post to.
     * @param string $data The data to post.
     * @param string $auth The authorization token.
     * @return _GSC_Response The response to the request.
     **/
    public static function post($uri, $data, $auth) {
        $ch = self::ch();
        $headers = array(
            'Content-Type: application/atom+xml',
            'Authorization: ' . $auth
        );
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return self::req($ch);
    }

    /**
     * Make an HTTP PUT request with a Google Authorization header.
     *
     * @param string $uri The URI to post to.
     * @param string $data The data to post.
     * @param string $auth The authorization token.
     * @return _GSC_Response The response to the request.
     **/
    public static function put($uri, $data, $auth) {
        $ch = self::ch();
        $headers = array(
            'Content-Type: application/atom+xml',
            'Authorization: ' . $auth
        );
        curl_setopt($ch, CURLOPT_URL, $uri);
        // For string data, use CURLOPT_CUSTOMREQUEST instead of CURLOPT_POST
        // Can also use memory as file-like object as described in:
        // gen-x-design.com/archives/making-restful-requests-in-php/
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return self::req($ch);
    }

    /**
     * Make an HTTP DELETE request with a Google Authorization header.
     *
     * @param string $uri The URI to post to.
     * @param string $auth The authorization token.
     * @return _GSC_Response The response to the request.
     **/
    public static function delete($uri, $auth) {
        $ch = self::ch();
        $headers = array(
            'Content-Type: application/atom+xml',
            'Authorization: ' . $auth
        );
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return self::req($ch);
    }

    /**
     * Make an HTTP request and create a response.
     *
     * @param CURL $ch The curl session.
     * @return _GSC_Response The response to the request.
     **/
    public static function req($ch) {
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return new _GSC_Response($info, $output);
    }

    /**
     * Create and initialize a CURL session.
     *
     * @return CURL The curl session.
     **/
    private static function ch() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        return $ch;
    }
}


/**
 * Handles making ClientLogin requests to authenticate and authorize.
 *
 * @package GShoppingContent
 * @version 1.1
 **/
class _GSC_ClientLogin
{
    /**
     * Log in to ClientLogin.
     *
     * @static
     * @param string $email Google account email address.
     * @param string $password Google account password.
     * @return string The Auth token from ClientLogin.
     * @author afshar@google.com
     **/
    public static function login($email, $password)
    {
        $fields = array(
            'Email' => $email,
            'Passwd' => $password,
            'service' => CLIENTLOGIN_SVC,
            'source' => USER_AGENT,
            'accountType' => 'GOOGLE'
        );
        $resp = _GSC_Http::postForm(CLIENTLOGIN_URI, $fields);
        $tokens = array();
        foreach (explode("\n", $resp->body) as $line) {
            $line = chop($line);
            if ($line) {
                list($key, $val) = explode('=', $line, 2);
                $tokens[$key] = $val;
            }
        }
        return $tokens['Auth'];
    }
}


/**
 * Base class for client errors.
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
 **/
class _GSC_ClientError extends Exception { }


/**
 * Client for making requests to the Google Content API for Shopping.
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com, dhermes@google.com
 **/
class GSC_Client
{

    /**
     * Authorization token for the user.
     *
     * @var string
     **/
    private $token;

    /**
     * Create a new client for the merchant.
     *
     * @return GSC_Client The newliy created client.
     * @author afshar@google.com
     **/
    public function __construct($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * Check that this client has been authorized and has a token.
     *
     * @throws _GSC_ClientError if there is no token.
     * @return void
     */
    private function checkToken() {
        if ($this->token == null) {
            throw new _GSC_ClientError('Client is not authenticated.');
        }
    }

    /**
     * Log in with ClientLogin and set the auth token.
     *
     * @param string $email Google account email address.
     * @param string $password Google account password.
     * @return void
     **/
    public function login($email, $password) {
        $this->token = _GSC_ClientLogin::login($email, $password);
    }

    /**
     * Get all products.
     *
     * @param string $maxResults The max results desired. Defaults to null.
     * @param string $startToken The start token for the query. Defaults to null.
     * @return _GSC_Response The HTTP response.
     */
    public function getProducts($maxResults=null, $startToken=null) {
        $feedUri = $this->getFeedUri();

        $queryParams = array();
        if ($maxResults != null) {
            array_push($queryParams, 'max-results=' . $maxResults);
        }
        if ($startToken != null) {
            array_push($queryParams, 'start-token=' . $startToken);
        }

        if (count($queryParams) > 0) {
            $feedUri .= '?' . join('&', $queryParams);
        }

        $resp = _GSC_Http::get(
            $feedUri,
            $this->getTokenHeader()
        );
        return _GSC_AtomParser::parse($resp->body);
    }

    /**
     * Get a product from a link.
     *
     * @param string $link The edit link for the product.
     * @return _GSC_Response The HTTP response.
     */
    public function getFromLink($link) {
        $resp = _GSC_Http::get(
            $link,
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parse($resp->body);
    }

    /**
     * Get a product.
     *
     * @param string $id The product id.
     * @param string $country The country specific to the product.
     * @param string $language The language specific to the product.
     * @return _GSC_Response The HTTP response.
     */
    public function getProduct($id, $country, $language) {
        $link = $this->getProductUri($id, $country, $language);
        return $this->getFromLink($link);
    }

    /**
     * Insert a product.
     *
     * @param GSC_Product $product The product to insert.
     * @param boolean $warnings A boolean to determine if the warnings should be
     *                          included. Defaults to false.
     * @param boolean $dryRun A boolean to determine if the dry-run should be
     *                        included. Defaults to false.
     * @return _GSC_Response The HTTP response.
     */
    public function insertProduct($product, $warnings=false, $dryRun=false) {
        $feedUri = $this->appendQueryParams(
            $this->getFeedUri(),
            $warnings,
            $dryRun
        );

        $resp = _GSC_Http::post(
            $feedUri,
            $product->toXML(),
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parse($resp->body);
    }

    /**
     * Update a product.
     *
     * @param GSC_Product $product The product to update.
     *                    Must have rel='edit' set.
     * @param boolean $warnings A boolean to determine if the warnings should be
     *                          included. Defaults to false.
     * @param boolean $dryRun A boolean to determine if the dry-run should be
     *                        included. Defaults to false.
     * @return _GSC_Response The HTTP response.
     */
    public function updateProduct($product, $warnings=false, $dryRun=false) {
        $productUri = $this->appendQueryParams(
            $product->getEditLink(),
            $warnings,
            $dryRun
        );

        $resp = _GSC_Http::put(
            $productUri,
            $product->toXML(),
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parse($resp->body);
    }

    /**
     * Send a delete request to a link.
     *
     * @param string $link The edit link for the product.
     * @return _GSC_Response The HTTP response.
     */
    public function deleteFromLink($link) {
        $resp = _GSC_Http::delete(
            $link,
            $this->getTokenHeader()
          );

        if ($resp->code != 200) {
            throw new _GSC_ClientError('Delete request failed.');
        }
    }

    /**
     * Delete a product.
     *
     * @param GSC_Product $product The product to delete.
     *                    Must have rel='edit' set.
     * @param boolean $warnings A boolean to determine if the warnings should be
     *                          included. Defaults to false.
     * @param boolean $dryRun A boolean to determine if the dry-run should be
     *                        included. Defaults to false.
     * @return _GSC_Response The HTTP response.
     */
    public function deleteProduct($product, $warnings=false, $dryRun=false) {
        $productUri = $this->appendQueryParams(
            $product->getEditLink(),
            $warnings,
            $dryRun
        );

        $this->deleteFromLink($productUri);
    }

    /**
     * Make a batch request.
     *
     * @param GSC_ProductList $products The list of products to batch.
     * @param boolean $warnings A boolean to determine if the warnings should be
     *                          included. Defaults to false.
     * @param boolean $dryRun A boolean to determine if the dry-run should be
     *                        included. Defaults to false.
     * @return GSC_ProductList The returned results from the batch.
     **/
    public function batch($products, $warnings=false, $dryRun=false) {
        $batchUri = $this->appendQueryParams(
            $this->getBatchUri(),
            $warnings,
            $dryRun
        );

        $resp = _GSC_Http::post(
            $batchUri(),
            $products->toXML(),
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parse($resp->body);
    }

    /**
     * Get all subaccounts.
     *
     * @param string $maxResults The max results desired. Defaults to null.
     * @param string $startIndex The start index for the query. Defaults to null.
     * @return _GSC_Response The HTTP response.
     */
    public function getAccounts($maxResults=null, $startIndex=null) {
        $accountsUri = $this->getManagedAccountsUri();

        $queryParams = array();
        if ($maxResults != null) {
            array_push($queryParams, 'max-results=' . $maxResults);
        }
        if ($startIndex != null) {
            array_push($queryParams, 'start-index=' . $startIndex);
        }

        if (count($queryParams) > 0) {
            $accountsUri .= '?' . join('&', $queryParams);
        }

        $resp = _GSC_Http::get(
            $accountsUri,
            $this->getTokenHeader()
        );
        return _GSC_AtomParser::parseManagedAccounts($resp->body);
    }

    /**
     * Get a subaccount.
     *
     * @param string $accountId The account id.
     * @return _GSC_Response The HTTP response.
     */
    public function getAccount($accountId) {
        $resp = _GSC_Http::get(
            $this->getManagedAccountsUri($accountId),
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parseManagedAccounts($resp->body);
    }

    /**
     * Insert a subaccount.
     *
     * @param GSC_ManagedAccount $account The account to insert.
     * @return GSC_ManagedAccount The inserted account from the response.
     */
    public function insertAccount($account) {
        $resp = _GSC_Http::post(
            $this->getManagedAccountsUri(),
            $account->toXML(),
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parseManagedAccounts($resp->body);
    }

    /**
     * Update a subaccount.
     *
     * @param GSC_ManagedAccount $account The account to update.
     *                                    Must have rel='edit' set.
     * @return _GSC_Response The HTTP response.
     */
    public function updateAccount($account) {
        $resp = _GSC_Http::put(
            $account->getEditLink(),
            $account->toXML(),
            $this->getTokenHeader()
          );
        return _GSC_AtomParser::parseManagedAccounts($resp->body);
    }

    /**
     * Delete a subaccount.
     *
     * @param GSC_ManagedAccount $account The account to delete.
     *                                    Must have rel='edit' set.
     * @return _GSC_Response The HTTP response.
     */
    public function deleteAccount($account) {
        $this->deleteFromLink($account->getEditLink());
    }

    /**
     * Create a URI for the feed for this merchant.
     *
     * @return string The feed URI.
     **/
    public function getFeedUri() {
        return BASE . $this->merchantId . '/items/products/schema/';
    }

    /**
     * Create a URI for an individual product.
     *
     * @param string $id The product id.
     * @param string $country The country specific to the product.
     * @param string $language The language specific to the product.
     * @return string The product URI.
     **/
    public function getProductUri($id, $country, $language) {
        return sprintf(
            '%sonline:%s:%s:%s',
            $this->getFeedUri(),
            $language,
            $country,
            $id
        );
    }

    /**
     * Create a URI for the batch feed for this merchant.
     *
     * @return string The batch feed URI.
     **/
    public function getBatchUri() {
        return $this->getFeedUri() . 'batch';
    }

    /**
     * Create a URI for the managed accounts feed for this merchant.
     *
     * @param string $accountId The account id. Defaults to null.
     * @return string The managedaccounts URI.
     **/
    public function getManagedAccountsUri($accountId=null) {
        $result = BASE . $this->merchantId . '/managedaccounts';
        if ($accountId != null) {
            $result .= '/' . $accountId;
        }
        return $result;
    }

    /**
     * Build a URI with warnings and dry-run query parameters.
     *
     * @param string $uri The URI to have parameters appended to.
     * @param boolean $warnings A boolean to determine if the warnings should be
     *                          included. Defaults to false.
     * @param boolean $dryRun A boolean to determine if the dry-run should be
     *                        included. Defaults to false.
     * @return string The URI with parameters included
     **/
    public function appendQueryParams($uri, $warnings=false, $dryRun=false) {
        $queryParams = array();
        if ($warnings) {
            array_push($queryParams, 'warnings');
        }
        if ($dryRun) {
            array_push($queryParams, 'dry-run');
        }

        if (count($queryParams) > 0) {
            $uri .= '?' . join('&', $queryParams);
        }

        return $uri;
    }

    /**
     * Create a header from the authorization token.
     *
     * @return string The authorization header.
     **/
    public function getTokenHeader() {
        $this->checkToken();
        return 'GoogleLogin auth=' . $this->token;
    }

}


/**
 * Namespaces used by GSC
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com, dhermes@google.com
**/
class _GSC_Ns {
    /**
     * Atom namespace.
     **/
    const atom = 'http://www.w3.org/2005/Atom';

    /**
     * Atom Publishing Protocol namespace.
     **/
    const app = 'http://www.w3.org/2007/app';

    /**
     * OpenSearch namespace.
     **/
    const openSearch = 'http://a9.com/-/spec/opensearch/1.1/';

    /**
     * Google Data namespace.
     **/
    const gd = 'http://schemas.google.com/g/2005';

    /**
     * GData Batch namespace.
     **/
    const batch = 'http://schemas.google.com/gdata/batch';

    /**
     * Structured Content namespace.
     **/
    const sc = 'http://schemas.google.com/structuredcontent/2009';

    /**
     * Structured Content Products namespace.
     **/
    const scp = 'http://schemas.google.com/structuredcontent/2009/products';
}


/**
 * Tags used by GSC.
 *
 * Each tag is available as an array of two elements, the namespace and the tag
 * name'
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
**/
class _GSC_Tags {
    /**
     * The <batch:operation> tag.
     *
     * @var array
     * @see GSC_Product::setBatchOperation(), GSC_Product::getBatchOperation()
     **/
    public static $operation = array(_GSC_Ns::batch, 'operation');

    /**
     * The <batch:status> tag.
     *
     * @var array
     * @see GSC_Product::getBatchStatus()
     **/
    public static $status = array(_GSC_Ns::batch, 'status');

    /**
     * The <atom:entry> tag.
     *
     * @var array
     * @see GSC_Product::createModel(), _GSC_AtomParser::parse()
     **/
    public static $entry = array(_GSC_Ns::atom, 'entry');

    /**
     * The <atom:title> tag.
     *
     * @var array
     * @see _GSC_AtomElement::setTitle(), _GSC_AtomElement::getTitle()
     **/
    public static $title = array(_GSC_Ns::atom, 'title');

    /**
     * The <atom:id> tag.
     *
     * @var array
     * @see _GSC_AtomElement::getAtomId(), _GSC_AtomElement::setAtomId()
     **/
    public static $atomId = array(_GSC_Ns::atom, 'id');

    /**
     * The <atom:content> tag.
     *
     * @var array
     * @see _GSC_AtomElement::setDescription(),
     *      _GSC_AtomElement::getDescription()
     **/
    public static $content = array(_GSC_Ns::atom, 'content');

    /**
     * <atom:link> element
     *
     * @var array
     * @see GSC_Product::setProductLink(), GSC_Product::getProductLink()
     **/
    public static $link = array(_GSC_Ns::atom, 'link');

    /**
     * <atom:published> element
     *
     * @var array
     * @see _GSC_AtomElement::getPublished()
     **/
    public static $published = array(_GSC_Ns::atom, 'published');

    /**
     * <atom:updated> element
     *
     * @var array
     * @see _GSC_AtomElement::getUpdated()
     **/
    public static $updated = array(_GSC_Ns::atom, 'updated');

    /**
     * <atom:author> element
     *
     * @var array
     * @see _GSC_AtomElement::getAtomAuthor()
     **/
    public static $atomAuthor = array(_GSC_Ns::atom, 'author');

    /**
     * <atom:name> element
     *
     * @var array
     * @see _GSC_AtomElement::getAuthorName()
     **/
    public static $name = array(_GSC_Ns::atom, 'name');

    /**
     * <atom:email> element
     *
     * @var array
     * @see _GSC_AtomElement::getAuthorEmail()
     **/
    public static $email = array(_GSC_Ns::atom, 'email');

    /**
     * <gd:errors> element
     *
     * @var array
     * @see GSC_Errors
     **/
    public static $errors = array(_GSC_Ns::gd, 'errors');

    /**
     * <gd:error> element
     *
     * @var array
     * @see GSC_Errors::getErrors()
     **/
    public static $error = array(_GSC_Ns::gd, 'error');

    /**
     * <gd:domain> element
     *
     * @var array
     * @see GSC_ErrorElement::getDomain()
     **/
    public static $domain = array(_GSC_Ns::gd, 'domain');

    /**
     * <gd:code> element
     *
     * @var array
     * @see GSC_ErrorElement::getCode()
     **/
    public static $code = array(_GSC_Ns::gd, 'code');

    /**
     * <gd:location> element
     *
     * @var array
     * @see GSC_ErrorElement::getLocation(), GSC_ErrorElement::getLocationType()
     **/
    public static $location = array(_GSC_Ns::gd, 'location');

    /**
     * <gd:internalReason> element
     *
     * @var array
     * @see GSC_ErrorElement::getInternalReason()
     **/
    public static $internalReason = array(_GSC_Ns::gd, 'internalReason');

    /**
     * <gd:debugInfo> element
     *
     * @var array
     * @see GSC_ErrorElement::getDebugInfo()
     **/
    public static $debugInfo = array(_GSC_Ns::gd, 'debugInfo');

    /**
     * <gd:etag> element
     *
     * @var array
     **/
    public static $etag = array(_GSC_Ns::gd, 'etag');

    /**
     * <gd:kind> element
     *
     * @var array
     **/
    public static $kind = array(_GSC_Ns::gd, 'kind');

    /**
     * <gd:fields> element
     *
     * @var array
     **/
    public static $fields = array(_GSC_Ns::gd, 'fields');

    /**
     * <openSearch:startIndex> element
     *
     * @var array
     * @see _GSC_AtomElement::getStartIndex()
     **/
    public static $startIndex = array(_GSC_Ns::openSearch, 'startIndex');

    /**
     * <openSearch:totalResults> element
     *
     * @var array
     * @see _GSC_AtomElement::getTotalResults()
     **/
    public static $totalResults = array(_GSC_Ns::openSearch, 'totalResults');

    /**
     * <app:edited> element
     *
     * @var array
     * @see GSC_ManagedAccount::getEdited()
     **/
    public static $edited = array(_GSC_Ns::app, 'edited');

    /**
     * <app:control> element
     *
     * @var array
     * @see GSC_Product::add*Destination(), GSC_Product::clearAllDestinations()
     **/
    public static $control = array(_GSC_Ns::app, 'control');

    /**
     * <sc:required_destination> element
     *
     * @var array
     * @see GSC_Product::addRequiredDestination(), GSC_Product::clearAllDestinations()
     **/
    public static $required_destination = array(_GSC_Ns::sc, 'required_destination');

    /**
     * <sc:validate_destination> element
     *
     * @var array
     * @see GSC_Product::addValidateDestination(), GSC_Product::clearAllDestinations()
     **/
    public static $validate_destination = array(_GSC_Ns::sc, 'validate_destination');

    /**
     * <sc:excluded_destination> element
     *
     * @var array
     * @see GSC_Product::addExcludedDestination(), GSC_Product::clearAllDestinations()
     **/
    public static $excluded_destination = array(_GSC_Ns::sc, 'excluded_destination');

    /**
     * <sc:status> element
     *
     * @var array
     * @see GSC_Product::getDestinationStatus()
     **/
    public static $destinationStatus = array(_GSC_Ns::sc, 'status');

    /**
     * <sc:id> element
     *
     * @var array
     * @see GSC_Product::setSKU(), GSC_Product::getSKU()
     **/
    public static $id = array(_GSC_Ns::sc, 'id');

    /**
     * <sc:attribute> element
     *
     * @var array
     * @see GSC_Product::setAttribute(), GSC_Product::getAttribute(),
     *      GSC_Product::getAttributeType(), GSC_Product::getAttributeUnit()
     **/
    public static $attribute = array(_GSC_Ns::sc, 'attribute');

    /**
     * <sc:group> element
     *
     * @var array
     * @see GSC_Product::setGroup(), GSC_Product::getGroup(),
     *      GSC_Product::getGroups()
     **/
    public static $group = array(_GSC_Ns::sc, 'group');

    /**
     * <sc:warnings> element
     *
     * @var array
     * @see GSC_Product::getWarnings()
     **/
    public static $warnings = array(_GSC_Ns::sc, 'warnings');

    /**
     * <sc:warning> element
     *
     * @var array
     * @see GSC_Product::getWarnings()
     **/
    public static $warning = array(_GSC_Ns::sc, 'warning');

    /**
     * <sc:code> element
     *
     * @var array
     * @see GSC_Product::getWarningCode()
     **/
    public static $warningCode = array(_GSC_Ns::sc, 'code');

    /**
     * <sc:domain> element
     *
     * @var array
     * @see GSC_Product::getWarningDomain()
     **/
    public static $warningDomain = array(_GSC_Ns::sc, 'domain');

    /**
     * <sc:location> element
     *
     * @var array
     * @see GSC_Product::getWarningLocation()
     **/
    public static $warningLocation = array(_GSC_Ns::sc, 'location');

    /**
     * <sc:message> element
     *
     * @var array
     * @see GSC_Product::getWarningMessage()
     **/
    public static $message = array(_GSC_Ns::sc, 'message');

    /**
     * <sc:disapproved> element
     *
     * @var array
     * @see GSC_Product::getDisapproved()
     **/
    public static $disapproved = array(_GSC_Ns::sc, 'disapproved');

    /**
     * <sc:adult> element
     *
     * @var array
     * @see GSC_Product::setAdult(), GSC_Product::getAdult()
     **/
    public static $adult = array(_GSC_Ns::sc, 'adult');

    /**
     * <sc:target_country> element
     *
     * @var array
     * @see GSC_Product::setTargetCountry(), GSC_Product::getTargetCountry()
     **/
    public static $target_country = array(_GSC_Ns::sc, 'target_country');

    /**
     * <sc:content_language> element
     *
     * @var array
     * @see GSC_Product::setContentLanguage(), GSC_Product::getContentLanguage()
     **/
    public static $content_language = array(_GSC_Ns::sc, 'content_language');

    /**
     * <sc:image_link> element
     *
     * @var array
     * @see GSC_Product::addImageLink(), GSC_Product::clearAllImageLinks()
     **/
    public static $image_link = array(_GSC_Ns::sc, 'image_link');

    /**
     * <sc:additional_image_link> element
     *
     * @var array
     * @see GSC_Product::addAdditionalImageLink(), GSC_Product::clearAllAdditionalImageLinks()
     **/
    public static $additional_image_link = array(_GSC_Ns::sc, 'additional_image_link');

    /**
     * <sc:expiration_date> element
     *
     * @var array
     * @see GSC_Product::setExpirationDate(), GSC_Product::getExpirationDate()
     **/
    public static $expiration_date = array(_GSC_Ns::sc, 'expiration_date');

    /**
     * <sc:account_status> element
     *
     * @var array
     * @see GSC_ManagedAccount::getAccountStatus()
     **/
    public static $account_status = array(_GSC_Ns::sc, 'account_status');

    /**
     * <sc:adult_content> element
     *
     * @var array
     * @see GSC_ManagedAccount::setAdultContent(),
     *      GSC_ManagedAccount::getAdultContent()
     **/
    public static $adult_content = array(_GSC_Ns::sc, 'adult_content');

    /**
     * <sc:internal_id> element
     *
     * @var array
     * @see GSC_ManagedAccount::setInternalId(),
     *      GSC_ManagedAccount::getInternalId()
     **/
    public static $internal_id = array(_GSC_Ns::sc, 'internal_id');

    /**
     * <sc:reviews_url> element
     *
     * @var array
     * @see GSC_ManagedAccount::setReviewsUrl(),
     *      GSC_ManagedAccount::getReviewsUrl()
     **/
    public static $reviews_url = array(_GSC_Ns::sc, 'reviews_url');

    /**
     * <sc:feed_file_name> element
     *
     * @var array
     * @see GSC_Datafeed::setFeedFileName(), GSC_Datafeed::getFeedFileName()
     **/
    public static $feed_file_name = array(_GSC_Ns::sc, 'feed_file_name');

    /**
     * <sc:attribute_language> element
     *
     * @var array
     * @see GSC_Datafeed::setAttributeLanguage(),
     *      GSC_Datafeed::getAttributeLanguage()
     **/
    public static $attribute_language = array(_GSC_Ns::sc, 'attribute_language');

    /**
     * <sc:file_format> element
     *
     * @var array
     * @see GSC_Datafeed::setFileFormat(), GSC_Datafeed::getFileFormat()
     **/
    public static $file_format = array(_GSC_Ns::sc, 'file_format');

    /**
     * <sc:encoding> element
     *
     * @var array
     * @see GSC_Datafeed::setEncoding(), GSC_Datafeed::getEncoding()
     **/
    public static $encoding = array(_GSC_Ns::sc, 'encoding');

    /**
     * <sc:delimiter> element
     *
     * @var array
     * @see GSC_Datafeed::setDelimiter(), GSC_Datafeed::getDelimiter()
     **/
    public static $delimiter = array(_GSC_Ns::sc, 'delimiter');

    /**
     * <sc:use_quoted_fields> element
     *
     * @var array
     * @see GSC_Datafeed::setUseQuotedFields(),
     *      GSC_Datafeed::getUseQuotedFields()
     **/
    public static $use_quoted_fields = array(_GSC_Ns::sc, 'use_quoted_fields');

    /**
     * <sc:feed_type> element
     *
     * @var array
     * @see GSC_Datafeed::getFeedType()
     **/
    public static $feed_type = array(_GSC_Ns::sc, 'feed_type');

    /**
     * <sc:processing_status> element
     *
     * @var array
     * @see GSC_Datafeed::getProcessingStatus()
     **/
    public static $processing_status = array(_GSC_Ns::sc, 'processing_status');

    /**
     * <scp:price> element
     *
     * @var array
     * @see GSC_Product::setPrice(), GSC_Product::getPrice(), GSC_Product::getPriceUnit()
     **/
    public static $price = array(_GSC_Ns::scp, 'price');

    /**
     * <scp:condition> element
     *
     * @var array
     * @see GSC_Product::setCondition(), GSC_Product::getCondition()
     **/
    public static $condition = array(_GSC_Ns::scp, 'condition');

    /**
     * <scp:shipping> element
     *
     * @var array
     * @see GSC_Product::addShipping(), GSC_Product::clearAllShippings()
     **/
    public static $shipping = array(_GSC_Ns::scp, 'shipping');

    /**
     * <scp:shipping_country> element
     *
     * @var array
     * @see GSC_Product::addShipping(), GSC_Product::clearAllShippings()
     **/
    public static $shipping_country = array(_GSC_Ns::scp, 'shipping_country');

    /**
     * <scp:shipping_region> element
     *
     * @var array
     * @see GSC_Product::addShipping(), GSC_Product::clearAllShippings()
     **/
    public static $shipping_region = array(_GSC_Ns::scp, 'shipping_region');

    /**
     * <scp:shipping_price> element
     *
     * @var array
     * @see GSC_Product::addShipping(), GSC_Product::clearAllShippings()
     **/
    public static $shipping_price = array(_GSC_Ns::scp, 'shipping_price');

    /**
     * <scp:shipping_service> element
     *
     * @var array
     * @see GSC_Product::addShipping(), GSC_Product::clearAllShippings()
     **/
    public static $shipping_service = array(_GSC_Ns::scp, 'shipping_service');

    /**
     * <scp:tax> element
     *
     * @var array
     * @see GSC_Product::addTax(), GSC_Product::clearAllTaxes()
     **/
    public static $tax = array(_GSC_Ns::scp, 'tax');

    /**
     * <scp:tax_country> element
     *
     * @var array
     * @see GSC_Product::addTax(), GSC_Product::clearAllTaxes()
     **/
    public static $tax_country = array(_GSC_Ns::scp, 'tax_country');

    /**
     * <scp:tax_region> element
     *
     * @var array
     * @see GSC_Product::addTax(), GSC_Product::clearAllTaxes()
     **/
    public static $tax_region = array(_GSC_Ns::scp, 'tax_region');

    /**
     * <scp:tax_rate> element
     *
     * @var array
     * @see GSC_Product::addTax(), GSC_Product::clearAllTaxes()
     **/
    public static $tax_rate = array(_GSC_Ns::scp, 'tax_rate');

    /**
     * <scp:tax_ship> element
     *
     * @var array
     * @see GSC_Product::addTax(), GSC_Product::clearAllTaxes()
     **/
    public static $tax_ship = array(_GSC_Ns::scp, 'tax_ship');

    /**
     * <scp:age_group> element
     *
     * @var array
     * @see GSC_Product::setAgeGroup(), GSC_Product::getAgeGroup()
     **/
    public static $age_group = array(_GSC_Ns::scp, 'age_group');

    /**
     * <scp:author> element
     *
     * @var array
     * @see GSC_Product::setAuthor(), GSC_Product::getAuthor()
     **/
    public static $author = array(_GSC_Ns::scp, 'author');

    /**
     * <scp:availability> element
     *
     * @var array
     * @see GSC_Product::setAvailability(), GSC_Product::getAvailability()
     **/
    public static $availability = array(_GSC_Ns::scp, 'availability');

    /**
     * <scp:brand> element
     *
     * @var array
     * @see GSC_Product::setBrand(), GSC_Product::getBrand()
     **/
    public static $brand = array(_GSC_Ns::scp, 'brand');

    /**
     * <scp:color> element
     *
     * @var array
     * @see GSC_Product::setColor(), GSC_Product::getColor()
     **/
    public static $color = array(_GSC_Ns::scp, 'color');

    /**
     * <scp:edition> element
     *
     * @var array
     * @see GSC_Product::setEdition(), GSC_Product::getEdition()
     **/
    public static $edition = array(_GSC_Ns::scp, 'edition');

    /**
     * <scp:feature> element
     *
     * @var array
     * @see GSC_Product::addFeature(), GSC_Product::clearAllFeatures()
     **/
    public static $feature = array(_GSC_Ns::scp, 'feature');

    /**
     * <scp:featured_product> element
     *
     * @var array
     * @see GSC_Product::setFeaturedProduct(), GSC_Product::getFeaturedProduct()
     **/
    public static $featured_product = array(_GSC_Ns::scp, 'featured_product');

    /**
     * <scp:manufacturer> element
     *
     * @var array
     * @see GSC_Product::setManufacturer(), GSC_Product::getManufacturer()
     **/
    public static $manufacturer = array(_GSC_Ns::scp, 'manufacturer');

    /**
     * <scp:mpn> element
     *
     * @var array
     * @see GSC_Product::setMpn(), GSC_Product::getMpn()
     **/
    public static $mpn = array(_GSC_Ns::scp, 'mpn');

    /**
     * <scp:online_only> element
     *
     * @var array
     * @see GSC_Product::setOnlineOnly(), GSC_Product::getOnlineOnly()
     **/
    public static $online_only = array(_GSC_Ns::scp, 'online_only');

    /**
     * <scp:gtin> element
     *
     * @var array
     * @see GSC_Product::setGtin(), GSC_Product::getGtin()
     **/
    public static $gtin = array(_GSC_Ns::scp, 'gtin');

    /**
     * <scp:product_type> element
     *
     * @var array
     * @see GSC_Product::setProductType(), GSC_Product::getProductType()
     **/
    public static $product_type = array(_GSC_Ns::scp, 'product_type');

    /**
     * <scp:product_review_average> element
     *
     * @var array
     * @see GSC_Product::setProductReviewAverage(), GSC_Product::getProductReviewAverage()
     **/
    public static $product_review_average = array(_GSC_Ns::scp, 'product_review_average');

    /**
     * <scp:quantity> element
     *
     * @var array
     * @see GSC_Product::setQuantity(), GSC_Product::getQuantity()
     **/
    public static $quantity = array(_GSC_Ns::scp, 'quantity');

    /**
     * <scp:shipping_weight> element
     *
     * @var array
     * @see GSC_Product::setShippingWeight(), GSC_Product::getShippingWeight()
     **/
    public static $shipping_weight = array(_GSC_Ns::scp, 'shipping_weight');

    /**
     * <scp:size> element
     *
     * @var array
     * @see GSC_Product::addSize(), GSC_Product::clearAllSizes()
     **/
    public static $size = array(_GSC_Ns::scp, 'size');

    /**
     * <scp:year> element
     *
     * @var array
     * @see GSC_Product::setYear(), GSC_Product::getYear()
     **/
    public static $year = array(_GSC_Ns::scp, 'year');

    /**
     * <scp:channel> element
     *
     * @var array
     * @see GSC_Product::setsetChannel(), GSC_Product::getsetChannel()
     **/
    public static $channel = array(_GSC_Ns::scp, 'channel');

    /**
     * <scp:gender> element
     *
     * @var array
     * @see GSC_Product::setGender(), GSC_Product::getGender()
     **/
    public static $gender = array(_GSC_Ns::scp, 'gender');

    /**
     * <scp:genre> element
     *
     * @var array
     * @see GSC_Product::setGenre(), GSC_Product::getGenre()
     **/
    public static $genre = array(_GSC_Ns::scp, 'genre');

    /**
     * <scp:item_group_id> element
     *
     * @var array
     * @see GSC_Product::setItemGroupId(), GSC_Product::getItemGroupId()
     **/
    public static $item_group_id = array(_GSC_Ns::scp, 'item_group_id');

    /**
     * <scp:google_product_category> element
     *
     * @var array
     * @see GSC_Product::setGoogleProductCategory(), GSC_Product::getGoogleProductCategory()
     **/
    public static $google_product_category = array(_GSC_Ns::scp, 'google_product_category');

    /**
     * <scp:material> element
     *
     * @var array
     * @see GSC_Product::setMaterial(), GSC_Product::getMaterial()
     **/
    public static $material = array(_GSC_Ns::scp, 'material');

    /**
     * <scp:pattern> element
     *
     * @var array
     * @see GSC_Product::setPattern(), GSC_Product::getPattern()
     **/
    public static $pattern = array(_GSC_Ns::scp, 'pattern');

    /**
     * <scp:adwords_grouping> element
     *
     * @var array
     * @see GSC_Product::setAdwordsGrouping(), GSC_Product::getAdwordsGrouping()
     **/
    public static $adwords_grouping = array(_GSC_Ns::scp, 'adwords_grouping');

    /**
     * <scp:adwords_labels> element
     *
     * @var array
     * @see GSC_Product::setAdwordsLabels(), GSC_Product::getAdwordsLabels()
     **/
    public static $adwords_labels = array(_GSC_Ns::scp, 'adwords_labels');

    /**
     * <scp:adwords_redirect> element
     *
     * @var array
     * @see GSC_Product::setAdwordsRedirect(), GSC_Product::getAdwordsRedirect()
     **/
    public static $adwords_redirect = array(_GSC_Ns::scp, 'adwords_redirect');

    /**
     * <scp:adwords_queryparam> element
     *
     * @var array
     * @see GSC_Product::setAdwordsQueryparam(), GSC_Product::getAdwordsQueryparam()
     **/
    public static $adwords_queryparam = array(_GSC_Ns::scp, 'adwords_queryparam');
}


/**
 * Atom Parser
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com, dhermes@google.com
 **/
class _GSC_AtomParser {

    /**
     * Parse some XML into a DOM Element.
     *
     * @param string $xml The XML to parse.
     * @return DOMElement A DOM element appropriate to the XML.
     **/
    public function _xmlToDOM($xml) {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        return $doc;
    }

    /**
     * Parse some XML into our data model.
     *
     * @param string $xml The XML to parse.
     * @return _GSC_AtomElement An Atom element appropriate to the XML.
     **/
    public static function parse($xml) {
        $doc = _GSC_AtomParser::_xmlToDOM($xml);
        $root = $doc->documentElement;
        if ($root->tagName == 'entry') {
            return new GSC_Product($doc, $root);
        }
        else if ($root->tagName == 'feed') {
            return new GSC_ProductList($doc, $root);
        }
        else if ($root->tagName == 'errors') {
            return new GSC_Errors($doc, $root);
        }
    }

    /**
     * Parse some XML into our data model for the managedaccounts feed.
     *
     * @param string $xml The XML to parse.
     * @return _GSC_AtomElement An Atom element appropriate to the XML.
     **/
    public static function parseManagedAccounts($xml) {
        $doc = _GSC_AtomParser::_xmlToDOM($xml);
        $root = $doc->documentElement;
        if ($root->tagName == 'entry') {
            return new GSC_ManagedAccount($doc, $root);
        }
        else if ($root->tagName == 'feed') {
            return new GSC_ManagedAccountList($doc, $root);
        }
        else if ($root->tagName == 'errors') {
            return new GSC_Errors($doc, $root);
        }
    }

}


/**
 * The base implementation for retrieving and setting values from a chunk of
 * XML.
 *
 * This class, and concrete implementations will store no internal state. Their
 * entire data is stored in the $model as XML, and is controlled using the owner
 * $doc.
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com, dhermes@google.com
 **/
abstract class _GSC_AtomElement
{
    /**
     * DOMDocument for saving model to XML and creating elements with
     * no parents. Defaults to the return value of createDoc.
     *
     * @var DOMDocument
     **/
    public $doc;

    /**
     * Base DOMElement for the _GSC_AtomElement being built. Defaults
     * to the return value of createModel.
     *
     * @var DOMElement
     **/
    public $model;

    /**
     * Create a new _GSC_AtomElement
     *
     * The data for this element can come from one of two places. Either some
     * XML from the API, or created from scratch. If the $model and the $doc are
     * not provided, empty versions are created. The default $model creation
     * should be controlled by overriding _GSC_AtomElement::createModel().
     *
     * @param DOMDocument $doc An existing DOM Document.
     * @param DOMElement $model An existing DOM Element.
     * @return _GSC_AtomElement
     **/
    function __construct($doc=null, $model=null) {
        // ternerahay!
        $this->doc = $doc ? $doc : $this->createDoc();
        $this->model = $model ? $model : $this->createModel();
    }

    /**
     * Get the first element of a tag type.
     *
     * @param array $tag The tag describing the attribute we seek.
     * @param DOMElement $parent An optional parent element to define where
     *                           to search. Defaults to null and is replaced
     *                           by $this->model if set to null.
     * @return Element.
     **/
    protected function getFirst($tag, $parent=null) {
        $el = $parent ? $parent : $this->model;
        $list = $el->getElementsByTagNameNS($tag[0], $tag[1]);
        if ($list->length > 0) {
            $el = $list->item(0);
            return $el;
        }
        else {
            return null;
        }
    }

    /**
     * Get the first element of a tag type or create it if it doesn't exist.
     *
     * @param array $tag The tag describing the attribute we seek.
     * @param DOMElement $el An optional parent element to be passed in to
     *                       getFirst. Defaults to null.
     * @return DOMElement that was created.
     **/
    protected function getCreateFirst($tag, $parent=null) {
        $el = $parent ? $parent : $this->model;
        $child = $this->getFirst($tag, $parent);
        if ($child == null) {
            $child = $this->create($tag);
            $el->appendChild($child);
            return $child;
        }
        else {
            return $child;
        }
    }

    /**
     * Get the value of the first element matching the tag.
     *
     * @param array $tag The tag describing the attribute we seek.
     * @param DOMElement $el An optional parent element to be passed in to
     *                       getFirst. Defaults to null.
     * @return string Node value of the first element matching the tag, or
     *                empty string if no match.
     **/
    protected function getFirstValue($tag, $el=null) {
        $child = $this->getFirst($tag, $el);
        if ($child) {
            return $child->nodeValue;
        }
        else {
            return '';
        }
    }

    /**
     * Set the value of the first element matching the tag.
     *
     * @param array $tag The tag describing the attribute we seek to find
     *                   or create.
     * @param array $val The value we want to set.
     * @param DOMElement $parent An optional parent element to be passed in to
     *                           getCreateFirst. Defaults to null.
     * @return DOMElement The element that was changed or created.
     **/
    protected function setFirstValue($tag, $val, $parent=null) {
        $child = $this->getCreateFirst($tag, $parent);
        $child->nodeValue = $val;
        return $child;
    }

    /**
     * Get all elements matching the tag.
     *
     * @param array $tag The tag describing the attribute.
     * @param DOMElement $parent An optional parent element. Defaults to null.
     * @return DOMNodeList A list of all matching DOMElements.
     **/
    function getAll($tag, $parent=null) {
        $el = $parent ? $parent : $this->model;
        $list = $el->getElementsByTagNameNS($tag[0], $tag[1]);
        return $list;
    }

    /**
     * Delete all elements matching the tag.
     *
     * @param array $tag The tag describing the attribute.
     * @param DOMElement $parent An optional parent element. Defaults to null.
     * @return void
     **/
    function deleteAll($tag, $parent=null) {
        $el = $parent ? $parent : $this->model;
        $list = $el->getElementsByTagNameNS($tag[0], $tag[1]);
        $count = $list->length;
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            $el->removeChild($child);
        }
    }

    /**
     * Get the first atom link attribute with a specified rel= value.
     *
     * @param string $rel The value of rel= we seek to find.
     * @return DOMElement The atom link attribute matching the rel value,
     *                    else null if there is no match.
     **/
    function getLink($rel) {
        $list = $this->model->getElementsByTagNameNS(_GSC_Ns::atom, 'link');
        $count = $list->length;
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            if ($child->getAttribute('rel') == $rel) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Get the edit link.
     *
     * @return string The edit link.
     **/
    function getEditLink() {
        $el = $this->getLink('edit');
        if ($el == null) {
            return '';
        }
        else {
            return $el->getAttribute('href');
        }
    }

    /**
     * Set the edit link.
     *
     * @param string $link The edit link to add.
     * @param string $type The type of the added link.
     * @return DOMElement The element that was changed or created.
     **/
    function setEditLink($link, $type) {
        $el = $this->getLink('edit');
        if ($el == null) {
            $el = $this->create(_GSC_Tags::$link);
            $el->setAttribute('rel', 'edit');
            $this->model->appendChild($el);
        }

        $el->setAttribute('href', $link);
        $el->setAttribute('type', $type);
    }

    /**
     * Get the atom ID.
     *
     * @return string The atom ID.
     **/
    function getAtomId() {
        return $this->getFirstValue(_GSC_Tags::$atomId);
    }

    /**
     * Set the atom ID.
     *
     * @param string $atomId The atom ID to set.
     * @return DOMElement The element that was changed.
     **/
    function setAtomId($atomId) {
        return $this->setFirstValue(_GSC_Tags::$atomId, $atomId);
    }

    /**
     * Get the published date.
     *
     * @return string The published date.
     **/
    function getPublished() {
        return $this->getFirstValue(_GSC_Tags::$published);
    }

    /**
     * Get the updated date.
     *
     * @return string The updated date.
     **/
    function getUpdated() {
        return $this->getFirstValue(_GSC_Tags::$updated);
    }

    /**
     * Get the atom author.
     *
     * @return string The atom author.
     **/
    function getAtomAuthor() {
        return $this->getFirst(_GSC_Tags::$atomAuthor);
    }

    /**
     * Get the author name.
     *
     * @return string The author name.
     **/
    function getAuthorName() {
        $author = $this->getAtomAuthor();
        return $this->getFirstValue(_GSC_Tags::$name, $author);
    }

    /**
     * Get the author email.
     *
     * @return string The author email.
     **/
    function getAuthorEmail() {
        $author = $this->getAtomAuthor();
        return $this->getFirstValue(_GSC_Tags::$email, $author);
    }
    /**
     * Get the title.
     *
     * @return string The title.
     **/
    public function getTitle() {
        return $this->getFirstValue(_GSC_Tags::$title);
    }

    /**
     * Set the title.
     *
     * @param string $title The title to set.
     * @return DOMElement The element that was changed.
     **/
    public function setTitle($title) {
        return $this->setFirstValue(_GSC_Tags::$title, $title);
    }

    /**
     * Get the description.
     *
     * @return string The description.
     **/
    function getDescription() {
        return $this->getFirstValue(_GSC_Tags::$content);
    }

    /**
     * Set the description.
     *
     * @param string $description The description to set.
     * @return DOMElement The element that was changed.
     **/
    function setDescription($description) {
        $el = $this->setFirstValue(_GSC_Tags::$content, $description);
        $el->setAttribute('type', 'text');
        return $el;
    }

    /**
     * Get the start index of search results.
     *
     * @return string The start index of search results.
     **/
    function getStartIndex() {
        return $this->getFirstValue(_GSC_Tags::$startIndex);
    }

    /**
     * Get the total number of search results.
     *
     * @return string The total number of search results.
     **/
    function getTotalResults() {
        return $this->getFirstValue(_GSC_Tags::$totalResults);
    }

    /**
     * Get the time of last edit.
     *
     * @return string The time of the last edit.
     **/
    function getEdited() {
        return $this->getFirstValue(_GSC_Tags::$edited);
    }

    /**
     * Create a default DOMDocument for creating DOMElements.
     *
     * @return DOMDocument The default DOM factory document.
     **/
    function createDoc() {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        return $doc;
    }

    /**
     * Get a string representation of the XML DOM in the model.
     *
     * @return string The XML in $this->model as string.
     **/
    function toXML() {
        return $this->doc->saveXML($this->model);
    }

    /**
     * Use the DOC factory to create a DOMElement corresponding to the tag.
     *
     * @param array $tag The tag describing the attribute we seek.
     * @param string $content The value to be placed in the created attribute.
     *                        Defaults to null.
     * @return DOMElement The DOM Element holding the created attribute.
     **/
    function create($tag, $content=null) {
        return $this->doc->createElementNS($tag[0], $tag[1], $content);
    }

    /**
     * Create a default DOM Element for the atom element being built.
     *
     * @return DOMElement The default DOM element parent for the atom element.
     **/
    abstract function createModel();
}


/**
 * GSC_Product
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com, dhermes@google.com
 **/
class GSC_Product extends _GSC_AtomElement {

    /**
     * Get a named generic attribute as a DOMElement.
     *
     * @param string $attributeName The generic attribute name.
     * @return DOMElement The DOM Element containing the generic attribute,
     *                    if it exists, else null.
     **/
    public function _getAttributeElement($attributeName) {
        $list = $this->getAll(_GSC_Tags::$attribute);
        $count = $list->length;

        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);

            if ($child->getAttribute('name') == $attributeName) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Get the value of a named generic attribute.
     *
     * @param string $attributeName The generic attribute name.
     * @return string The value of the generic attribute.
     **/
    public function getAttribute($attributeName) {
        $child = $this->_getAttributeElement($attributeName);
        if ($child == null) {
            return null;
        } else {
            return $child->nodeValue;
        }
    }

    /**
     * Get the type of a named generic attribute.
     *
     * @param string $attributeName The generic attribute name.
     * @return string The type of the generic attribute.
     **/
    public function getAttributeType($attributeName) {
        $child = $this->_getAttributeElement($attributeName);
        if ($child == null) {
            return null;
        } else {
            return $child->getAttribute('type');
        }
    }

    /**
     * Get the unit of a named generic attribute.
     *
     * @param string $attributeName The generic attribute name.
     * @return string The unit of the generic attribute.
     **/
    public function getAttributeUnit($attributeName) {
        $child = $this->_getAttributeElement($attributeName);
        if ($child == null) {
            return null;
        } else {
            return $child->getAttribute('unit');
        }
    }

    /**
     * Create a generic attribute DOM Element.
     *
     * @param string $value The generic attribute value.
     * @param string $attributeName The generic attribute name.
     * @param string $attributeType The generic attribute type.
     * @param string $unit The generic attribute units.
     * @return DOMElement The element (with no parent) that was created.
     **/
    public function _createAttribute($value, $attributeName, $attributeType=null, $unit=null) {
        $el = $this->create(_GSC_Tags::$attribute, $value);
        $el->setAttribute('name', $attributeName);

        if ($attributeType != null) {
            $el->setAttribute('unit', $unit);
        }

        if ($unit != null) {
            $el->setAttribute('unit', $unit);
        }

        return $el;
    }

    /**
     * Set the value of a named generic attribute.
     *
     * @param string $value The generic attribute value.
     * @param string $attributeName The generic attribute name.
     * @param string $attributeType The generic attribute type.
     * @param string $unit The generic attribute units.
     * @return DOMElement The element that was changed.
     **/
    public function setAttribute($value, $attributeName, $attributeType=null, $unit=null) {
        $el = $this->_createAttribute(
            $value,
            $attributeName,
            $attributeType,
            $unit
        );
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Get a list of all named generic groups.
     *
     * @return DOMElement DOM Element containing list of generic groups.
     **/
    public function getGroups() {
        $groupTag = _GSC_Tags::$group;
        return $this->model->getElementsByTagNameNS($groupTag[0], $groupTag[1]);
    }

    /**
     * Get the named generic group.
     *
     * @param string $groupName The generic group name.
     * @return DOMElement DOM Element of specific attribute in the case of
     *                    a match, else null.
     **/
    public function getGroup($groupName) {
        $groups = $this->getGroups();
        $count = $groups->length;

        for($pos=0; $pos<$count; $pos++) {
            $child = $groups->item($pos);
            if ($child->getAttribute('name') == $groupName) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Set the value of a named generic attribute.
     *
     * @param string $groupName The generic group name.
     * @param array $attributes The list of generic attributes in the group.
     * @return DOMElement The element that was changed.
     **/
    public function setGroup($groupName, $attributes) {
        $group = $this->getGroup($groupName);
        if ($group == null) {
            $group = $this->create(_GSC_Tags::$group);
            $group->setAttribute('name', $groupName);
            $this->model->appendChild($group);
        }
        $this->deleteAll(_GSC_Tags::$attribute, $group);

        foreach ($attributes as $attribute) {
            $group->appendChild($attribute);
        }
    }

    /**
     * Get the warnings.
     *
     * @return DOMNodeList The list of warnings as DOM Elements.
     **/
    public function getWarnings() {
        $appControl = $this->getFirst(_GSC_Tags::$control);
        $warnings = $this->getFirst(_GSC_Tags::$warnings, $appControl);
        return $this->getAll(_GSC_Tags::$warning, $warnings);
    }

    /**
     * Get the warning code.
     *
     * @param DOMElement $warning The DOM Element containing the warning.
     * @return string The warning code.
     **/
    public function getWarningCode($warning) {
        return $this->getFirstValue(_GSC_Tags::$warningCode, $warning);
    }

    /**
     * Get the warning domain.
     *
     * @param DOMElement $warning The DOM Element containing the warning.
     * @return string The warning domain.
     **/
    public function getWarningDomain($warning) {
        return $this->getFirstValue(_GSC_Tags::$warningDomain, $warning);
    }

    /**
     * Get the warning location.
     *
     * @param DOMElement $warning The DOM Element containing the warning.
     * @return string The warning location.
     **/
    public function getWarningLocation($warning) {
        return $this->getFirstValue(_GSC_Tags::$warningLocation, $warning);
    }

    /**
     * Get the warning message.
     *
     * @param DOMElement $warning The DOM Element containing the warning.
     * @return string The warning message.
     **/
    public function getWarningMessage($warning) {
        return $this->getFirstValue(_GSC_Tags::$message, $warning);
    }

    /**
     * Get the price of the product.
     *
     * @return string The price of the product.
     **/
    public function getPrice() {
        return $this->getFirstValue(_GSC_Tags::$price);
    }

    /**
     * Get the price currency of the product.
     *
     * @return string The price currency of the product.
     **/
    public function getPriceUnit() {
        $el = $this->getFirst(_GSC_Tags::$price);
        return $el->getAttribute('unit');
    }

    /**
     * Set the price of the product.
     *
     * @param string $price The price to set.
     * @param string $unit The currency of the price to set.
     * @return DOMElement The element that was changed.
     **/
    public function setPrice($price, $unit) {
        $el = $this->setFirstValue(_GSC_Tags::$price, $price);
        $el->setAttribute('unit', $unit);
        return $el;
    }

    /**
     * Get the SKU of the product.
     *
     * @return string The SKU of the product.
     **/
    function getSKU() {
        return $this->getFirstValue(_GSC_Tags::$id);
    }

    /**
     * Set the SKU of the product.
     *
     * @param string $sku The SKU to set.
     * @return DOMElement The element that was changed.
     **/
    function setSKU($sku) {
        $this->setFirstValue(_GSC_Tags::$id, $sku);
    }

    /**
     * Get the target country of the product.
     *
     * @return string The target country of the product.
     **/
    function getTargetCountry() {
        return $this->getFirstValue(_GSC_Tags::$target_country);
    }

    /**
     * Set the target country of the product.
     *
     * @param string $country The target country to set.
     * @return DOMElement The element that was changed.
     **/
    function setTargetCountry($country) {
        return $this->setFirstValue(_GSC_Tags::$target_country, $country);
    }

    /**
     * Get the content language of the product.
     *
     * @return string The target country of the product.
     **/
    function getContentLanguage($language) {
        return $this->getFirstValue(_GSC_Tags::$content_language);
    }

    /**
     * Set the content language of the product.
     *
     * @param string $language The language to set.
     * @return DOMElement The element that was changed.
     **/
    function setContentLanguage($language) {
        return $this->setFirstValue(_GSC_Tags::$content_language, $language);
    }

    /**
     * Get the condition of the product.
     *
     * @return string The condition of the product.
     **/
    function getCondition() {
        return $this->getFirstValue(_GSC_Tags::$condition);
    }

    /**
     * Set the condition of the product.
     *
     * @param string $condition The condition to set ('new', 'used', 'refurbished').
     * @return DOMElement The element that was changed.
     **/
    function setCondition($condition) {
        return $this->setFirstValue(_GSC_Tags::$condition, $condition);
    }

    /**
     * Get the Expiration Date for the product.
     *
     * @return string The expiration date in YYYY-MM-DD.
     **/
    public function getExpirationDate() {
        return $this->getFirstValue(_GSC_Tags::$expiration_date);
    }

    /**
     * Set the Expiration Date for the product.
     *
     * @param string $date The date to set in YYYY-MM-DD format.
     * @return DOMElement The element that was changed.
     **/
    public function setExpirationDate($date) {
        return $this->setFirstValue(_GSC_Tags::$expiration_date, $date);
    }

    /**
     * Get the link for the product.
     *
     * @return string The link for the product.
     **/
    function getProductLink() {
        $el = $this->getLink('alternate');
        if ($el == null) {
            return '';
        }
        else {
            return $el->getAttribute('href');
        }
    }

    /**
     * Set the Link for the product.
     *
     * @param string $link The product link to add.
     * @return DOMElement The element that was changed or created.
     **/
    function setProductLink($link) {
        $el = $this->getLink('alternate');
        if ($el == null) {
            $el = $this->create(_GSC_Tags::$link);
            $el->setAttribute('rel', 'alternate');
            $el->setAttribute('type', 'text/html');
            $this->model->appendChild($el);
        }

        $el->setAttribute('href', $link);
    }

    /**
     * Get the adult status for this product.
     *
     * @return string The adult status of the product.
     **/
    function getAdult() {
        return $this->getFirstValue(_GSC_Tags::$adult);
    }

    /**
     * Set the adult status for the product.
     *
     * @param string $adult The adult status of the product: 'true' or 'false'.
     * @return DOMElement The element that was changed.
     **/
    function setAdult($adult) {
        return $this->setFirstValue(_GSC_Tags::$adult, $adult);
    }

    /**
     * Get the Adwords Grouping of the product.
     *
     * @return string The Adwords Grouping of the product.
     **/
    public function getAdwordsGrouping() {
        return $this->getFirstValue(_GSC_Tags::$adwords_grouping);
    }

    /**
     * Set the Adwords Grouping of the product.
     *
     * @param string $adwords_grouping The Adwords Grouping to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAdwordsGrouping($adwords_grouping) {
        return $this->setFirstValue(_GSC_Tags::$adwords_grouping, $adwords_grouping);
    }

    /**
     * Get the Adwords Labels of the product.
     *
     * @return string The Adwords Label of the product.
     **/
    public function getAdwordsLabels() {
        return $this->getFirstValue(_GSC_Tags::$adwords_labels);
    }

    /**
     * Set the Adwords Labels of the product.
     *
     * @param string $adwords_labels The Adwords Labels to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAdwordsLabels($adwords_labels) {
        return $this->setFirstValue(_GSC_Tags::$adwords_labels, $adwords_labels);
    }

    /**
     * Get the Adwords Query Parameter of the product.
     *
     * @return string The Adwords Query Parameter of the product.
     **/
    public function getAdwordsQueryparam() {
        return $this->getFirstValue(_GSC_Tags::$adwords_queryparam);
    }

    /**
     * Set the Adwords Query Parameter of the product.
     *
     * @param string $adwords_queryparam The Adwords Query Parameter to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAdwordsQueryParam($adwords_queryparam) {
        return $this->setFirstValue(_GSC_Tags::$adwords_queryparam, $adwords_queryparam);
    }

    /**
     * Get the Adwords Redirect of the product.
     *
     * @return string The Adwords Redirect of the product.
     **/
    public function getAdwordsRedirect() {
        return $this->getFirstValue(_GSC_Tags::$adwords_redirect);
    }

    /**
     * Set the Adwords Redirect of the product.
     *
     * @param string $adwords_redirect The Adwords Redirect to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAdwordsRedirect($adwords_redirect) {
        return $this->setFirstValue(_GSC_Tags::$adwords_redirect, $adwords_redirect);
    }

    /**
     * Get the age group of the product.
     *
     * @return string The Age Group of the product.
     **/
    public function getAgeGroup() {
        return $this->getFirstValue(_GSC_Tags::$age_group);
    }

    /**
     * Set the age group of the product.
     *
     * @param string $age_group The age group to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAgeGroup($age_group) {
        return $this->setFirstValue(_GSC_Tags::$age_group, $age_group);
    }

    /**
     * Get the author of the product.
     *
     * @return string The Author of the product.
     **/
    public function getAuthor() {
        return $this->getFirstValue(_GSC_Tags::$author);
    }

    /**
     * Set the author of the product.
     *
     * @param string $author The author to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAuthor($author) {
        return $this->setFirstValue(_GSC_Tags::$author, $author);
    }

    /**
     * Get the brand of the product.
     *
     * @return string The brand of the product.
     **/
    public function getBrand() {
        return $this->getFirstValue(_GSC_Tags::$brand);
    }

    /**
     * Set the brand of the product.
     *
     * @param string $brand the brand to set.
     * @return DOMElement The element that was changed.
     **/
    public function setBrand($brand) {
        return $this->setFirstValue(_GSC_Tags::$brand, $brand);
    }

    /**
     * Get the availability of the product.
     *
     * @return string The availability of the product.
     **/
    public function getAvailability() {
        return $this->getFirstValue(_GSC_Tags::$availability);
    }

    /**
     * Set the availability of the product.
     *
     * @param string $availability the availability to set.
     * @return DOMElement The element that was changed.
     **/
    public function setAvailability($availability) {
        return $this->setFirstValue(_GSC_Tags::$availability, $availability);
    }

    /**
     * Get the color of the product.
     *
     * @return string The color of the product.
     **/
    public function getColor() {
        return $this->getFirstValue(_GSC_Tags::$color);
    }

    /**
     * Set the color of the product.
     *
     * @param string $color The color to set.
     * @return DOMElement The element that was changed.
     **/
    public function setColor($color) {
        return $this->setFirstValue(_GSC_Tags::$color, $color);
    }

    /**
     * Get the edition of the product.
     *
     * @return string The edition of the product.
     **/
    public function getEdition() {
        return $this->getFirstValue(_GSC_Tags::$edition);
    }

    /**
     * Set the edition of the product.
     *
     * @param string $edition The edition to set.
     * @return DOMElement The element that was changed.
     **/
    public function setEdition($edition) {
        return $this->setFirstValue(_GSC_Tags::$edition, $edition);
    }

    /**
     * Get the featured status of the product.
     *
     * @return string Whether the product is featured.
     **/
    public function getFeaturedProduct() {
        return $this->getFirstValue(_GSC_Tags::$featured_product);
    }

    /**
     * Set the featured status of the product.
     *
     * @param string $featured_product The featured status to set.
     * @return DOMElement The element that was changed.
     **/
    public function setFeaturedProduct($featured_product) {
        return $this->setFirstValue(_GSC_Tags::$featured_product, $featured_product);
    }

    /**
     * Get the genre of the product.
     *
     * @return string The genre of the product.
     **/
    public function getGenre() {
        return $this->getFirstValue(_GSC_Tags::$genre);
    }

    /**
     * Set the genre of the product.
     *
     * @param string $genre the genre to set.
     * @return DOMElement The element that was changed.
     **/
    public function setGenre($genre) {
        return $this->setFirstValue(_GSC_Tags::$genre, $genre);
    }

    /**
     * Get the manufacturer of the product.
     *
     * @return string The manufacturer of the product.
     **/
    public function getManufacturer() {
        return $this->getFirstValue(_GSC_Tags::$manufacturer);
    }

    /**
     * Set the manufacturer of the product.
     *
     * @param string $manufacturer The manufacturer to set.
     * @return DOMElement The element that was changed.
     **/
    public function setManufacturer($manufacturer) {
        return $this->setFirstValue(_GSC_Tags::$manufacturer, $manufacturer);
    }

    /**
     * Get the manufacturer's part number.
     *
     * @return string The manufacturer's part number.
     **/
    public function getMpn() {
        return $this->getFirstValue(_GSC_Tags::$mpn);
    }

    /**
     * Set the manufacturer's part number.
     *
     * @param $mpn The manufacturer's part number to set.
     * @return DOMElement The element that was changed.
     **/
    public function setMpn($mpn) {
        return $this->setFirstValue(_GSC_Tags::$mpn, $mpn);
    }

    /**
     * Get the online only status of the product.
     *
     * @return string The online only status of the product.
     **/
    public function getOnlineOnly() {
        return $this->getFirstValue(_GSC_Tags::$online_only);
    }

    /**
     * Set the online only status of the product.
     *
     * @param string $online_only The online only value to set.
     * @return DOMElement The element that was changed.
     **/
    public function setOnlineOnly($online_only) {
        return $this->setFirstValue(_GSC_Tags::$online_only, $online_only);
    }

    /**
     * Get the GTIN of the product.
     *
     * @return string The GTIN of the product.
     **/
    public function getGtin() {
        return $this->getFirstValue(_GSC_Tags::$gtin);
    }

    /**
     * Set the GTIN of the product.
     *
     * @param string $gtin The GTIN to set.
     * @return DOMElement The element that was changed.
     **/
    public function setGtin($gtin) {
        return $this->setFirstValue(_GSC_Tags::$gtin, $gtin);
    }

    /**
     * Get the product type.
     *
     * @return string The product type.
     **/
    public function getProductType() {
        return $this->getFirstValue(_GSC_Tags::$product_type);
    }

    /**
     * Set the product type.
     *
     * @param string $product_type The product type to set.
     * @return DOMElement The element that was changed.
     **/
    public function setProductType($product_type) {
        return $this->setFirstValue(_GSC_Tags::$product_type, $product_type);
    }

    /**
     * Get the product review average.
     *
     * @return string The product review average.
     **/
    public function getProductReviewAverage() {
        return $this->getFirstValue(_GSC_Tags::$product_review_average);
    }

    /**
     * Set the product review average.
     *
     * @param string $product_review_average The product review average to set.
     * @return DOMElement The element that was changed.
     **/
    public function setProductReviewAverage($product_review_average) {
        return $this->setFirstValue(_GSC_Tags::$product_review_average, $product_review_average);
    }

    /**
     * Get the product review count.
     *
     * @return string The product review count.
     **/
    public function getProductReviewCount() {
        return $this->getFirstValue(_GSC_Tags::$product_review_count);
    }

    /**
     * Set the product review count.
     *
     * @param string $product_review_count The product review count to set.
     * @return DOMElement The element that was changed.
     **/
    public function setProductReviewCount($product_review_count) {
        return $this->setFirstValue(_GSC_Tags::$product_review_count, $product_review_count);
    }

    /**
     * Get the quantity (inventory) of the product.
     *
     * @return string The quantity of the product.
     **/
    public function getQuantity() {
        return $this->getFirstValue(_GSC_Tags::$quantity);
    }

    /**
     * Set the quantity (inventory) of the product.
     *
     * @param string $quantity The quantity to set.
     * @return DOMElement The element that was changed.
     **/
    public function setQuantity($quantity) {
        return $this->setFirstValue(_GSC_Tags::$quantity, $quantity);
    }

    /**
     * Get the shipping weight of the product.
     *
     * @return string The shipping weight of the product.
     **/
    public function getShippingWeight() {
        return $this->getFirstValue(_GSC_Tags::$shipping_weight);
    }

    /**
     * Get the shipping weight unit of the product.
     *
     * @return string The shipping weight unit of the product.
     **/
    public function getShippingWeightUnit() {
        $el = $this->getFirst(_GSC_Tags::$shipping_weight);
        return $el->getAttribute('unit');
    }

    /**
     * Set the shipping weight of the product.
     *
     * @param string $shipping_weight The shipping weight to set.
     * @param string $unit The unit of the weight to set. Defaults to null.
     * @return DOMElement The element that was changed.
     **/
    public function setShippingWeight($shipping_weight, $unit=null) {
        $el = $this->setFirstValue(_GSC_Tags::$shipping_weight, $shipping_weight);
        if ($unit != null) {
            // In the name of backwards compatibility
            $el->setAttribute('unit', $unit);
            return $el;
        }
    }

    /**
     * Get the year of the product.
     *
     * @return string The year of the product.
     **/
    public function getYear() {
        return $this->getFirstValue(_GSC_Tags::$year);
    }

    /**
     * Set the year of the product.
     *
     * @param string $year The year to set.
     * @return DOMElement The element that was changed.
     **/
    public function setYear($year) {
        return $this->setFirstValue(_GSC_Tags::$year, $year);
    }

    /**
     * Get the image link.
     *
     * @return string The link to the main image for the product.
     **/
    public function getImageLink() {
        return $this->getFirstValue(_GSC_Tags::$image_link);
    }

    /**
     * Set the image link.
     *
     * @param string $image_link The image link to set.
     * @return DOMElement The element that was changed.
     **/
    public function setImageLink($image_link) {
        return $this->setFirstValue(_GSC_Tags::$image_link, $image_link);
    }

    /**
     * Get the channel of the product.
     *
     * @return string The channel of the product.
     **/
    public function getChannel() {
        return $this->getFirstValue(_GSC_Tags::$channel);
    }

    /**
     * Set the channel of the product.
     *
     * @param string $channel The channel to set.
     * @return DOMElement The element that was changed.
     **/
    public function setChannel($channel) {
        return $this->setFirstValue(_GSC_Tags::$channel, $channel);
    }

    /**
     * Get the gender of the product.
     *
     * @return string The gender of the product.
     **/
    public function getGender() {
        return $this->getFirstValue(_GSC_Tags::$gender);
    }

    /**
     * Set the gender of the product.
     *
     * @param string $gender The gender to set.
     * @return DOMElement The element that was changed.
     **/
    public function setGender($gender) {
        return $this->setFirstValue(_GSC_Tags::$gender, $gender);
    }

    /**
     * Get the item group id of the product.
     *
     * @return string The item group id of the product.
     **/
    public function getItemGroupId() {
        return $this->getFirstValue(_GSC_Tags::$item_group_id);
    }

    /**
     * Set the item group id of the product.
     *
     * @param string $item_group_id The item group id to set.
     * @return DOMElement The element that was changed.
     **/
    public function setItemGroupId($item_group_id) {
        return $this->setFirstValue(_GSC_Tags::$item_group_id, $item_group_id);
    }

    /**
     * Get the google product category of the product.
     *
     * @return string The google product category of the product.
     **/
    public function getGoogleProductCategory() {
        return $this->getFirstValue(_GSC_Tags::$google_product_category);
    }

    /**
     * Set the google product category of the product.
     *
     * @param string $google_product_category The google product category to set.
     * @return DOMElement The element that was changed.
     **/
    public function setGoogleProductCategory($google_product_category) {
        return $this->setFirstValue(_GSC_Tags::$google_product_category, $google_product_category);
    }

    /**
     * Get the material of the product.
     *
     * @return string The material of the product.
     **/
    public function getMaterial() {
        return $this->getFirstValue(_GSC_Tags::$material);
    }

    /**
     * Set the material of the product.
     *
     * @param string $material The material to set.
     * @return DOMElement The element that was changed.
     **/
    public function setMaterial($material) {
        return $this->setFirstValue(_GSC_Tags::$material, $material);
    }

    /**
     * Get the pattern of the product.
     *
     * @return string The pattern of the product.
     **/
    public function getPattern() {
        return $this->getFirstValue(_GSC_Tags::$pattern);
    }

    /**
     * Set the pattern of the product.
     *
     * @param string $pattern The pattern to set.
     * @return DOMElement The element that was changed.
     **/
    public function setPattern($pattern) {
        return $this->setFirstValue(_GSC_Tags::$pattern, $pattern);
    }

    /**
     * Add a shipping rule to the product.
     *
     * @param string $country The shipping country to set.
     * @param string $region The shipping region to set.
     * @param string $price The shipping price to set.
     * @param string $priceUnit The shipping price currency to set.
     * @param string $service The shipping service to set.
     * @return DOMElement The element that was created.
     **/
    function addShipping($country, $region, $price, $priceUnit, $service) {
        $el = $this->create(_GSC_Tags::$shipping);
        $this->setFirstValue(_GSC_Tags::$shipping_country, $country, $el);
        $this->setFirstValue(_GSC_Tags::$shipping_region, $region, $el);
        $priceEl = $this->setFirstValue(_GSC_Tags::$shipping_price, $price, $el);
        $priceEl->setAttribute('unit', $priceUnit);
        $this->setFirstValue(_GSC_Tags::$shipping_service, $service, $el);
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Clear all the shipping rules from this product.
     *
     * @return void
     **/
    function clearAllShippings() {
        $this->deleteAll(_GSC_Tags::$shipping);
    }

    /**
     * Add a tax rule to the product.
     *
     * @param string $country The tax country to set.
     * @param string $region The tax region to set.
     * @param string $rate The tax rate to set.
     * @param string $ship The tax on shipping to set.
     * @return DOMElement The element that was created.
     **/
    function addTax($country, $region, $rate, $ship) {
        $el = $this->create(_GSC_Tags::$tax);
        $this->setFirstValue(_GSC_Tags::$tax_country, $country, $el);
        $this->setFirstValue(_GSC_Tags::$tax_region, $region, $el);
        $this->setFirstValue(_GSC_Tags::$tax_rate, $rate, $el);
        $this->setFirstValue(_GSC_Tags::$tax_ship, $ship, $el);
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Clear all the tax rules from this product.
     *
     * @return void
     **/
    function clearAllTaxes() {
        $this->deleteAll(_GSC_Tags::$tax);
    }

    /**
     * Get the disapproved status of the item.
     *
     * @return DOMElement The disapproved status of the item.
     **/
    function getDisapproved() {
        return $this->getFirstValue(_GSC_Tags::$disapproved);
    }

    /**
     * Add a required destination to the product.
     *
     * @param string $destination The destination to add.
     * @return DOMElement The element that was created.
     **/
    function addRequiredDestination($destination) {
        $el = $this->getCreateFirst(_GSC_Tags::$control);
        $child = $this->create(_GSC_Tags::$required_destination);
        $child->setAttribute('dest', $destination);
        $el->appendChild($child);
        return $child;
    }

    /**
     * Add a validate destination to the product.
     *
     * @param string $destination The destination to add.
     * @return DOMElement The element that was created.
     **/
    function addValidateDestination($destination) {
        $el = $this->getCreateFirst(_GSC_Tags::$control);
        $child = $this->create(_GSC_Tags::$validate_destination);
        $child->setAttribute('dest', $destination);
        $el->appendChild($child);
        return $child;
    }

    /**
     * Add an excluded destination to the product.
     *
     * @param string $destination The destination to add.
     * @return DOMElement The element that was created.
     **/
    function addExcludedDestination($destination) {
        $el = $this->getCreateFirst(_GSC_Tags::$control);
        $child = $this->create(_GSC_Tags::$excluded_destination);
        $child.setAttribute('dest', $destination);
        return $child;
    }

    /**
     * Clear all the destinations from this product.
     *
     * @return void
     **/
    function clearAllDestinations() {
        $this->deleteAll(_GSC_Tags::$control);
    }

    /**
     * Get the status of insertion into a destination.
     *
     * @param string $destination The destination to be checked.
     * @return string The status of insertion into a destination.
     **/
    function getDestinationStatus($destination) {
        $control = $this->getFirst(_GSC_Tags::$control);

        $statuses = $this->getAll(_GSC_Tags::$destinationStatus, $control);
        $count = $statuses->length;
        for($pos=0; $pos<$count; $pos++) {
            $child = $statuses->item($pos);
            if ($child->getAttribute('dest') == $destination) {
                return $child->getAttribute('status');
            }
        }
        return '';
    }

    /**
     * Add an additional image link to the product.
     *
     * @param string $link The link to add.
     * @return DOMElement The element that was created.
     **/
    function addAdditionalImageLink($link) {
        $el = $this->create(_GSC_Tags::$additional_image_link, $link);
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Clear all the additional image links from this product.
     *
     * @return void
     **/
    function clearAllAdditionalImageLinks() {
        $this->deleteAll(_GSC_Tags::$additional_image_link);
    }

    /**
     * Add a feature to the product.
     *
     * @param string $feature The feature to add.
     * @return DOMElement The element that was created.
     **/
    function addFeature($feature) {
        $el = $this->create(_GSC_Tags::$feature, $feature);
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Clear all the features from this product.
     *
     * @return void
     **/
    function clearAllFeatures() {
        $this->deleteAll(_GSC_Tags::$feature);
    }

    /**
     * Add a size to the product.
     *
     * @param string $size The size to add.
     * @return DOMElement The element that was created.
     **/
    function addSize($size) {
        $el = $this->create(_GSC_Tags::$size, $size);
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Clear all the sizes from this product.
     *
     * @return void
     **/
    function clearAllSizes() {
        $this->deleteAll(_GSC_Tags::$size);
    }

    /**
     * Get the batch operation type of the product.
     *
     * @return string The operation type of the product.
     **/
    function getBatchOperation() {
        $el = $this->getFirst(_GSC_Tags::$operation);
        return $el->getAttribute('type');
    }

    /**
     * Set the batch operation type of the product.
     *
     * @param string $operation The operation to set.
     * @return DOMElement The element that was changed.
     **/
    function setBatchOperation($operation) {
        $el = $this->setFirstValue(_GSC_Tags::$operation, null);
        $el->setAttribute('type', $operation);
        return $el;
    }

    /**
     * Get the batch status code.
     *
     * @return string The status code for this batch operation
     **/
    function getBatchStatus() {
        $el = $this->getFirst(_GSC_Tags::$status);
        return $el->getAttribute('code');
    }

    /**
     * Get the content tag containing batch errors.
     *
     * @return DOMElement The content tag containing batch errors. If
     *                    no matching tag is found, returns null.
     **/
    function _getContentErrorTag() {
        $errorType = 'application/vnd.google.gdata.error+xml';

        $list = $this->getAll(_GSC_Tags::$content);
        $count = $list->length;
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            if ($child->getAttribute('type') == $errorType) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Get the errors element from a batch entry.
     *
     * @return GSC_Errors The errors element from a batch entry. If
     *                    no matching tag is found, returns null.
     **/
    function getErrorsFromBatch() {
        $content = $this->_getContentErrorTag();
        if ($content == null) {
            return null;
        }

        $errors = $this->getFirst(_GSC_Tags::$errors, $content);
        return new GSC_Errors($this->doc, $errors);
    }

    /**
     * Create the initial model when none is provided.
     *
     * @return void
     * @return DOMElement The element that was created.
     **/
    public function createModel() {
        $s = '<entry '.
             'xmlns="http://www.w3.org/2005/Atom" '.
             'xmlns:sc="http://schemas.google.com/structuredcontent/2009" '.
             'xmlns:scp="http://schemas.google.com/structuredcontent/2009/products" '.
             'xmlns:batch="http://schemas.google.com/gdata/batch" '.
             'xmlns:app="http://www.w3.org/2007/app" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


/**
 * GSC_ProductList
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author afshar@google.com, dhermes@google.com
 **/
class GSC_ProductList extends _GSC_AtomElement {

    /**
     * Add a product to this list.
     *
     * This method imports the DOM elements.
     *
     * @param GSC_Product $product The product to add to this list.
     * @return void
     **/
    public function addProduct($product) {
        $clone = $this->doc->importNode($product->model, true);
        $this->model->appendChild($clone);
    }

    /**
     * Get the list of products.
     *
     * @return array List of GSC_Products from the feed.
     **/
    public function getProducts() {
        $list = $this->getAll(_GSC_Tags::$entry);
        $count = $list->length;
        $products = array();
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            $product = new GSC_Product($this->doc, $child);
            array_push($products, $product);
        }
        return $products;
    }

    /**
     * Get a specified query param value.
     *
     * @param string $href The link to be parsed.
     * @param string $desiredKey The key to be parsed from the query parameters.
     * @return string The query parameter if it is contained in the link,
     *                else the empty string.
     **/
    function _parseQueryParam($href, $desiredKey) {
        if (substr_count($href, '?') != 1) {
            return '';
        }

        list($throwAway, $queryParams) = explode('?', $href, 2);
        $params = array($desiredKey => ''); // In case not found
        foreach (explode('&', $queryParams) as $param) {
            if ($param) {
                list($key, $val) = explode('=', $param, 2);
                $params[$key] = $val;
            }
        }
        return $params[$desiredKey];
    }

    /**
     * Get the start token from the feed (for paging).
     *
     * @return string The start token from the rel='next' link.
     **/
    public function getStartToken() {
        $el = $this->getLink('next');
        if ($el == null) {
            return '';
        }
        else {
            return $this->_parseQueryParam(
                $el->getAttribute('href'),
                'start-token'
            );
        }
    }

    /**
     * Get the request size.
     *
     * @return integer The request size in KB.
     **/
    public function getRequestSize() {
        $length = strlen($this->toXML());
        return (integer) ceil($length/1024);
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<feed '.
             'xmlns="http://www.w3.org/2005/Atom" '.
             'xmlns:sc="http://schemas.google.com/structuredcontent/2009" '.
             'xmlns:scp="http://schemas.google.com/structuredcontent/2009/products" '.
             'xmlns:batch="http://schemas.google.com/gdata/batch" '.
             'xmlns:app="http://www.w3.org/2007/app" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


/**
 * GSC_ManagedAccount
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author dhermes@google.com
 **/
class GSC_ManagedAccount extends _GSC_AtomElement {

    /**
     * Get the account status.
     *
     * @return string The account status.
     **/
    function getAccountStatus() {
        return $this->getFirstValue(_GSC_Tags::$account_status);
    }

    /**
     * Get the adult content.
     *
     * @return string The adult content.
     **/
    function getAdultContent() {
        return $this->getFirstValue(_GSC_Tags::$adult_content);
    }

    /**
     * Set the adult content.
     *
     * @param string $adult_content The adult content.
     * @return DOMElement The element that was changed.
     **/
    public function setAdultContent($adult_content) {
        return $this->setFirstValue(_GSC_Tags::$adult_content, $adult_content);
    }

    /**
     * Get the internal id.
     *
     * @return string The internal id.
     **/
    function getInternalId() {
        return $this->getFirstValue(_GSC_Tags::$internal_id);
    }

    /**
     * Set the internal id.
     *
     * @param string $internal_id The internal id.
     * @return DOMElement The element that was changed.
     **/
    public function setInternalId($internal_id) {
        return $this->setFirstValue(_GSC_Tags::$internal_id, $internal_id);
    }

    /**
     * Get the reviews url.
     *
     * @return string The url with reviews.
     **/
    function getReviewsUrl() {
        return $this->getFirstValue(_GSC_Tags::$reviews_url);
    }

    /**
     * Set the review url
     *
     * @param string $reviews_url The url with reviews.
     * @return DOMElement The element that was changed.
     **/
    public function setReviewsUrl($reviews_url) {
        return $this->setFirstValue(_GSC_Tags::$reviews_url, $reviews_url);
    }

    /**
     * Get the link for the subaccount.
     *
     * @return string The link for the subaccount.
     **/
    function getAccountLink() {
        $el = $this->getLink('alternate');
        if ($el == null) {
            return '';
        }
        else {
            return $el->getAttribute('href');
        }
    }

    /**
     * Set the Link for the subaccount.
     *
     * @param string $link The subaccount link to add.
     * @return DOMElement The element that was changed or created.
     **/
    function setAccountLink($link) {
        $el = $this->getLink('alternate');
        if ($el == null) {
            $el = $this->create(_GSC_Tags::$link);
            $el->setAttribute('rel', 'alternate');
            $el->setAttribute('type', 'text/html');
            $this->model->appendChild($el);
        }

        $el->setAttribute('href', $link);
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<entry '.
             'xmlns="http://www.w3.org/2005/Atom" '.
             'xmlns:app="http://www.w3.org/2007/app" '.
             'xmlns:sc="http://schemas.google.com/structuredcontent/2009" '.
             'xmlns:gd="http://schemas.google.com/g/2005" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


/**
 * GSC_ManagedAccountList
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author dhermes@google.com
 **/
class GSC_ManagedAccountList extends _GSC_AtomElement {

    /**
     * Get the list of accounts.
     *
     * @return array List of GSC_ManagedAccount from the feed.
     **/
    public function getAccounts() {
        $list = $this->getAll(_GSC_Tags::$entry);
        $count = $list->length;
        $accounts = array();
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            $product = new GSC_ManagedAccount($this->doc, $child);
            array_push($accounts, $account);
        }
        return $accounts;
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<feed '.
             'xmlns="http://www.w3.org/2005/Atom" '.
             'xmlns:app="http://www.w3.org/2007/app" '.
             'xmlns:sc="http://schemas.google.com/structuredcontent/2009" '.
             'xmlns:gd="http://schemas.google.com/g/2005" '.
             'xmlns:openSearch="http://a9.com/-/spec/opensearch/1.1/" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}



/**
 * GSC_Datafeed
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author dhermes@google.com
 **/
class GSC_Datafeed extends _GSC_AtomElement {

    /**
     * Get the target country of the product.
     *
     * @return string The target country of the product.
     **/
    function getTargetCountry() {
        return $this->getFirstValue(_GSC_Tags::$target_country);
    }

    /**
     * Set the target country of the product.
     *
     * @param string $country The target country to set.
     * @return DOMElement The element that was changed.
     **/
    function setTargetCountry($country) {
        return $this->setFirstValue(_GSC_Tags::$target_country, $country);
    }

    /**
     * Get the content language of the product.
     *
     * @return string The target country of the product.
     **/
    function getContentLanguage() {
        return $this->getFirstValue(_GSC_Tags::$content_language);
    }

    /**
     * Set the content language of the product.
     *
     * @param string $language The language to set.
     * @return DOMElement The element that was changed.
     **/
    function setContentLanguage($language) {
        return $this->setFirstValue(_GSC_Tags::$content_language, $language);
    }

    /**
     * Get the feed file name.
     *
     * @return string The feed file name.
     **/
    function getFeedFileName() {
        return $this->getFirstValue(_GSC_Tags::$feed_file_name);
    }

    /**
     * Set the feed file name.
     *
     * @param string $feed_file_name The feed file name.
     * @return DOMElement The element that was changed.
     **/
    function setFeedFileName($feed_file_name) {
        return $this->setFirstValue(
            _GSC_Tags::$feed_file_name,
            $feed_file_name
        );
    }

    /**
     * Get the attribute language.
     *
     * @return string The attribute language.
     **/
    function getAttributeLanguage() {
        return $this->getFirstValue(_GSC_Tags::$attribute_language);
    }

    /**
     * Set the attribute language.
     *
     * @param string $attribute_language The attribute language.
     * @return DOMElement The element that was changed.
     **/
    function setAttributeLanguage($attribute_language) {
        return $this->setFirstValue(
            _GSC_Tags::$attribute_language,
            $attribute_language
        );
    }

    /**
     * Get the file format.
     *
     * @return string The file format.
     **/
    function getFileFormat() {
        $el = $this->getFirst(_GSC_Tags::$file_format);
        return $el->getAttribute('format');
    }

    /**
     * Set the file format.
     *
     * @param string $format The file format.
     * @return DOMElement The element that was changed.
     **/
    function setFileFormat($format) {
        $el = $this->getCreateFirst(_GSC_Tags::$file_format);
        $el->setAttribute('format', $format);
        return $el;
    }

    /**
     * Get the encoding.
     *
     * @return string The encoding.
     **/
    function getEncoding() {
        $format = $this->getFirst(_GSC_Tags::$file_format);
        if ($format == null) {
            return null;
        } else {
            return $this->getFirstValue(_GSC_Tags::$encoding, $format);
        }
    }

    /**
     * Set the encoding.
     *
     * @param string $encoding The encoding.
     * @return DOMElement The element that was changed.
     **/
    function setEncoding($encoding) {
        $format = $this->getCreateFirst(_GSC_Tags::$file_format);
        return $this->setFirstValue(_GSC_Tags::$encoding, $encoding, $format);
    }

    /**
     * Get the delimiter.
     *
     * @return string The delimiter.
     **/
    function getDelimiter() {
        $format = $this->getFirst(_GSC_Tags::$file_format);
        if ($format == null) {
            return null;
        } else {
            return $this->getFirstValue(_GSC_Tags::$delimiter, $format);
        }
    }

    /**
     * Set the delimiter.
     *
     * @param string $delimiter The delimiter.
     * @return DOMElement The element that was changed.
     **/
    function setDelimiter($delimiter) {
        $format = $this->getCreateFirst(_GSC_Tags::$file_format);
        return $this->setFirstValue(_GSC_Tags::$delimiter, $delimiter, $format);
    }

    /**
     * Get the "use quoted fields" value.
     *
     * @return string The "use quoted fields" value.
     **/
    function getUseQuotedFields() {
        $format = $this->getFirst(_GSC_Tags::$file_format);
        if ($format) {
            return $this->getFirstValue(_GSC_Tags::$use_quoted_fields, $format);
        } else {
            return null;
        }
    }

    /**
     * Set the "use quoted fields" value.
     *
     * @param string $use_quoted_fields The "use quoted fields" value.
     * @return DOMElement The element that was changed.
     **/
    function setUseQuotedFields($use_quoted_fields) {
        $format = $this->getCreateFirst(_GSC_Tags::$file_format);
        return $this->setFirstValue(
            _GSC_Tags::$use_quoted_fields,
            $use_quoted_fields,
            $format
        );
    }

    /**
     * Get the feed type.
     *
     * @return string The feed type.
     **/
    function getFeedType() {
        return $this->getFirstValue(_GSC_Tags::$feed_type);
    }

    /**
     * Get the processing status.
     *
     * @return string The processing status.
     **/
    function getProcessingStatus() {
        $el = $this->getFirst(_GSC_Tags::$processing_status);
        if ($el) {
            return $el->getAttribute('status');
        } else {
            return '';
        }
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<entry '.
             'xmlns="http://www.w3.org/2005/Atom" '.
             'xmlns:app="http://www.w3.org/2007/app" '.
             'xmlns:sc="http://schemas.google.com/structuredcontent/2009" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


/**
 * GSC_DatafeedList
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author dhermes@google.com
 **/
class GSC_DatafeedList extends _GSC_AtomElement {

    /**
     * Get the list of datafeeds.
     *
     * @return array List of GSC_Datafeed from the feed.
     **/
    public function getDatafeeds() {
        $list = $this->getAll(_GSC_Tags::$entry);
        $count = $list->length;
        $datafeeds = array();
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            $datafeed = new GSC_Datafeed($this->doc, $child);
            array_push($datafeeds, $datafeed);
        }
        return $datafeeds;
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<feed '.
             'xmlns="http://www.w3.org/2005/Atom" '.
             'xmlns:app="http://www.w3.org/2007/app" '.
             'xmlns:sc="http://schemas.google.com/structuredcontent/2009" '.
             'xmlns:openSearch="http://a9.com/-/spec/opensearch/1.1/" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


/**
 * GSC_ErrorElement
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author dhermes@google.com
 **/
class GSC_ErrorElement extends _GSC_AtomElement {

    /**
     * Get the domain of the error.
     *
     * @return string The domain of the error.
     **/
    function getDomain() {
        return $this->getFirstValue(_GSC_Tags::$domain);
    }

    /**
     * Get the code of the error.
     *
     * @return string The code of the error.
     **/
    function getCode() {
        return $this->getFirstValue(_GSC_Tags::$code);
    }

    /**
     * Get the location of the error.
     *
     * @return string The location of the error.
     **/
    function getLocation() {
        return $this->getFirstValue(_GSC_Tags::$location);
    }

    /**
     * Get the location type of the error.
     *
     * @return string The location type of the error.
     **/
    public function getLocationType() {
        $el = $this->getFirst(_GSC_Tags::$location);
        if ($el) {
            return $el->getAttribute('type');
        } else {
            return '';
        }
    }

    /**
     * Get the internal reason of the error.
     *
     * @return string The internal reason of the error.
     **/
    function getInternalReason() {
        return $this->getFirstValue(_GSC_Tags::$internalReason);
    }

    /**
     * Get the debug info of the error.
     *
     * @return string The debug info of the error.
     **/
    function getDebugInfo() {
        return $this->getFirstValue(_GSC_Tags::$debugInfo);
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<error xmlns="http://schemas.google.com/g/2005" />';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


/**
 * GSC_Errors
 *
 * @package GShoppingContent
 * @version 1.1
 * @copyright Google Inc, 2011
 * @author dhermes@google.com
 **/
class GSC_Errors extends _GSC_AtomElement {

    /**
     * Get the list of errors.
     *
     * @return array List of GSC_ErrorElement's from the feed.
     **/
    public function getErrors() {
        $list = $this->getAll(_GSC_Tags::$error);
        $count = $list->length;
        $errors = array();
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item($pos);
            $error = new GSC_ErrorElement($this->doc, $child);
            array_push($errors, $error);
        }
        return $errors;
    }

    /**
     * Create the default model for this element
     *
     * @return DOMElement The newly created element.
     **/
    public function createModel() {
        $s = '<errors xmlns="http://schemas.google.com/g/2005" />';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}

?>
