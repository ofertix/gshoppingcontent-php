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
 * @version 1
 * @author afshar@google.com
 * @copyright Google Inc, 2011
 * @package GShoppingContent
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
 * @version 1
 * @author afshar@google.com
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
        print_r($info);
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
 * @version 1
 * @author afshar@google.com
 * @copyright Google Inc, 2011
 **/
class _GSC_Http
{
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
 * @version 1
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
 * @packaged default
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
 **/
class _GSC_ClientError extends Exception { }


/**
 * Client for making requests to the Google Content API for Shopping.
 *
 * @package GShoppingContent
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
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
        $this->token = ClientLogin::login($email, $password);
    }

    /**
     * Insert a product.
     *
     * @param GSC_Product $product The product to insert.
     * @return _GSC_Response The HTTP response.
     */
    public function insert($product) {
        return _GSC_Http::post(
            $this->getFeedUri(),
            $product->toXML(),
            $this->getTokenHeader()
        );
    }

    /**
     * Make a batch request.
     *
     * @param GSC_ProductList $products The list of products to batch.
     * @return GSC_ProductList The returned results from the batch.
     **/
    public function batch($products) {
        return _GSC_Http::post(
            $this->getBatchUri(),
            $products->toXML(),
            $this->getTokenHeader()
        );
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
     * Create a URI for the batch feed for this merchant.
     *
     * @return string The batch feed URI.
     **/
    public function getBatchUri() {
        return $this->getFeedUri() . 'batch';
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
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
**/
class _GSC_Ns {
    /**
     * Atom namespace.
     **/
    const atom = 'http://www.w3.org/2005/Atom';

    /**
     * Atom Publishing Protocol namespace.
     **/
    const app = 'http://app';

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
 * @version 1
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
     * The <atom:title> tag.
     *
     * @var array
     * @see GSC_Product::setTitle(), GSC_Product::getTitle()
     **/
    public static $title = array(_GSC_Ns::atom, 'title');

    /**
     * The <atom:content> tag.
     *
     * @var array
     * @see GSC_Product::setDescription(), GSC_Product::getDescription()
     **/
    public static $content = array(_GSC_Ns::atom, 'content');

    /**
     * <atom:link> element
     *
     * @var array
     **/
    public static $link = array(_GSC_Ns::atom, 'link');

    /**
     * <sc:id>
     **/
    public static $id = array(_GSC_Ns::sc, 'id');
    public static $adult = array(_GSC_Ns::sc, 'adult');
    public static $price = array(_GSC_Ns::scp, 'price');
    public static $target_country = array(_GSC_Ns::scp, 'target_country');
    public static $content_language = array(_GSC_Ns::scp, 'content_language');
    public static $condition = array(_GSC_Ns::scp, 'condition');
    public static $image_link = array(_GSC_Ns::scp, 'image_link');
    public static $expiration_date = array(_GSC_Ns::scp, 'expiration_date');
    public static $shipping = array(_GSC_Ns::scp, 'shipping');
    public static $shipping_country = array(_GSC_Ns::scp, 'shipping_country');
    public static $shipping_region = array(_GSC_Ns::scp, 'shipping_region');
    public static $shipping_price = array(_GSC_Ns::scp, 'shipping_price');
    public static $shipping_service = array(_GSC_Ns::scp, 'shipping_service');
    public static $tax = array(_GSC_Ns::scp, 'tax');
    public static $tax_country = array(_GSC_Ns::scp, 'tax_country');
    public static $tax_region = array(_GSC_Ns::scp, 'tax_region');
    public static $tax_rate = array(_GSC_Ns::scp, 'tax_rate');
    public static $tax_ship = array(_GSC_Ns::scp, 'tax_ship');
    public static $author = array(_GSC_Ns::scp, 'author');
    public static $availability = array(_GSC_Ns::scp, 'availability');
    public static $brand = array(_GSC_Ns::scp, 'brand');
    public static $color = array(_GSC_Ns::scp, 'color');
    public static $edition = array(_GSC_Ns::scp, 'edition');
    public static $feature = array(_GSC_Ns::scp, 'feature');
    public static $featured_product = array(_GSC_Ns::scp, 'featured_product');
    public static $manufacturer = array(_GSC_Ns::scp, 'manufacturer');
    public static $mpn = array(_GSC_Ns::scp, 'mpn');
    public static $online_only = array(_GSC_Ns::scp, 'online_only');
    public static $gtin = array(_GSC_Ns::scp, 'gtin');
    public static $product_type = array(_GSC_Ns::scp, 'product_type');
    public static $product_review_average = array(_GSC_Ns::scp, 'product_review_average');
    public static $quantity = array(_GSC_Ns::scp, 'quantity');
    public static $shipping_weight = array(_GSC_Ns::scp, 'shipping_weight');
    public static $size = array(_GSC_Ns::scp, 'size');
    public static $year = array(_GSC_Ns::scp, 'year');
    public static $adwords_grouping = array(_GSC_Ns::scp, 'adwords_grouping');
    public static $adwords_labels = array(_GSC_Ns::scp, 'adwords_labels');
    public static $adwords_redirect = array(_GSC_Ns::scp, 'adwords_redirect');
    public static $adwords_queryparam = array(_GSC_Ns::scp, 'adwords_queryparam');
    public static $control = array(_GSC_Ns::app, 'control');
    public static $required_destination = array(_GSC_Ns::sc, 'required_destination');
    public static $excluded_destination = array(_GSC_Ns::sc, 'excluded_destination');
}


/**
 * Atom Parser
 *
 * @package GShoppingContent
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
 **/
class _GSC_AtomParser {

    /**
     * Parse some XML into our data model.
     *
     * @param string $xml The XML to parse.
     * @return _GSC_AtomElement An Atom element appropriate to the XML.
     **/
    public static function parse($xml) {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $root = $doc->documentElement;
        print_r($root->tagName);
        if ($root->tagName == 'entry') {
            return new GSC_Product($doc, $root);
        }
        else if ($root->tagName == 'feed') {
            $coll = new GSC_ProductList($root);
        }
        return $doc;
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
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
 **/
abstract class _GSC_AtomElement
{
    public $doc;
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

    protected function getCreateFirst($tag, $parent=null) {
        $el = $parent ? $parent : $this->model;
        $child = $this->getFirst($tag, $parent);
        if ($child == null) {
            $child = $this->doc->createElementNS($tag[0], $tag[1], null);
            $el->appendChild($child);
            return $child;
        }
        else {
            return $child;
        }
    }

    protected function getFirstValue($tag, $el=null) {
        $child = $this->getFirst($tag, $el);
        if ($el) {
            return $child->nodeValue;
        }
        else {
            return '';
        }
    }

    protected function setFirstValue($tag, $val, $parent=null) {
        $child = $this->getCreateFirst($tag, $parent);
        $child->nodeValue = $val;
        return $child;
    }

    function deleteAll($tag, $parent=null) {
        $el = $parent ? $parent : $this->model;
        $list = $el->getElementsByTagNameNS($tag[0], $tag[1]);
        $count = $list->length;
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item(0);
            $el->removeChild($child);
        }
    }

    function getLink($rel) {
        $list = $this->model->getElementsByTagNameNS(_GSC_Ns::atom, 'link');
        $count = $list->length;
        for($pos=0; $pos<$count; $pos++) {
            $child = $list->item(0);
            if ($child->getAttribute('rel') == $rel) {
                return $child;
            }
        }
        return null;
    }

    function createDoc() {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        return $doc;
    }

    function toXML() {
        return $this->doc->saveXML($this->model);
    }

    function create($tag, $content=null) {
        return $this->doc->createElementNS($tag[0], $tag[1], $content);
    }


    abstract function createModel();
} 


/**
 * GSC_Product
 *
 * @package GShoppingContent
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
 **/
class GSC_Product extends _GSC_AtomElement {

    /**
     * Get the product title.
     *
     * @return string The product title.
     **/
    public function getTitle() {
        return $this->getFirstValue(_GSC_Tags::$title);
    }

    /**
     * Set the product title.
     *
     * @param string $title The title to set.
     * @return DOMElement The element that was changed.
     **/
    public function setTitle($title) {
        return $this->setFirstValue(_GSC_Tags::$title, $title);
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
     * Get the description of the product.
     *
     * @return string The description of the product.
     **/
    function getDescription() {
        return $this->getFirstValue(_GSC_Tags::$content);
    }

    /**
     * Set the description of the product.
     *
     * @param string $description The description to set.
     * @return DOMElement The element that was changed.
     **/
    function setDescription($description) {
        $this->setFirstValue(_GSC_Tags::$content, $description);
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
     * @param string $date The date to set in YYYY-MM-DD format.
     * @return DOMElement The element that was changed.
     **/
    function setProductLink($link) {
        $el = $this->getLink('alternate');
        if ($el == null) {
            $el = $this->create(_GSC_Tags::$link);
            $el->setAttribute('href', $link);
            $el->setAttribute('rel', 'alternate');
            $this->model->appendChild($el);
        }
        else {
            $el->setAttribute('href', $link);
        }
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
     * Set the shipping weight of the product.
     *
     * @param string $shipping_weight The shipping weight to set.
     * @return DOMElement The element that was changed.
     **/
    public function setShippingWeight($shipping_weight) {
        return $this->setFirstValue(_GSC_Tags::$shipping_weight, $shipping_weight);
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
     * Add an image link to the product.
     *
     * @param string $link The link to add.
     * @return DOMElement The element that was created.
     **/
    function addImageLink($link) {
        $el = $this->create(_GSC_Tags::$image_link, $link);
        $this->model->appendChild($el);
        return $el;
    }

    /**
     * Clear all the image links from this product.
     *
     * @return void
     **/
    function clearAllImageLinks() {
        $this->deleteAll(_GSC_Tags::$image_link);
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
             'xmlns:app="http://app" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }

}


/**
 * GSC_ProductList
 *
 * @package GShoppingContent
 * @version 1
 * @copyright Google Inc, 2011
 * @author afshar@google.com
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

    public function getProducts() {
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
             'xmlns:app="http://app" '.
             '/>';
        $this->doc->loadXML($s);
        return $this->doc->documentElement;
    }
}


?>
