<?php
/**
 * Example to insert a product.
 */

// import our library
require_once('GShoppingContent.php');

// Get the user credentials
$creds = Credentials::get();

// Create a client for our merchant and log in
$client = new GSC_Client($creds["merchantId"]);
$client->login($creds["email"], $creds["password"]);

// Now enter some product data
$product = new GSC_Product();
$product->setTitle("Dijji Digital Camera");
$product->setPrice("199.99", "usd");
$product->setAdult("false");

// Finally send the data to the API



/**
 * Credentials
 *
 * @package default
 * @author Me
**/
class Credentials {
    private static function input($prompt) {
        fwrite(STDOUT, $prompt . " >");
        return fgets(STDIN);
    }

    public static function get() {
        return array(
            "merchantId" => self::input("Enter your Merchant Id"),
            "email" => self::input("Enter your email address"),
            "password" => self::input("Enter your password (not stored)"),
        );
    }
}


?>
