<?php

require_once('GShoppingContent.php');

class GSC_TestClient extends PHPUnit_Framework_TestCase {

    public function testDeleteError() {
        $creds = Credentials::get();

        // Create a client for our merchant and log in
        $client = new GSC_Client($creds["merchantId"]);
        $client->login($creds["email"], $creds["password"]);

        $product = new GSC_Product();
        $type = "application/atom+xml";
        $bogusId = "BOGUS_ID_123456789";
        $link = $client->getProductUri($bogusId, "US", "en");
        $product->setEditLink($link, $type);

        try {
            $client->deleteProduct($product);
        } catch (Exception $e) {
            $this->assertEquals('Delete request failed.', $e->getMessage());
        }
    }

    public function testGetProductUri() {
        $creds = Credentials::get();
        $client = new GSC_Client($creds["merchantId"]);

        $expected = 'https://content.googleapis.com/content/v1/' .
                    $creds["merchantId"] .
                    '/items/products/schema/online:en:US:SKU123';
        $link = $client->getProductUri('SKU123', 'US', 'en');
        $this->assertEquals($expected, $link);
    }

    public function testSubaccountDoesntExist() {
        $creds = Credentials::get();
        $client = new GSC_Client($creds["merchantId"]);
        $client->login($creds["email"], $creds["password"]);

        $errors = $client->getAccount("1");
        $errors = $errors->getErrors();
        $error = $errors[0];
        $this->assertEquals('ResourceNotFoundException', $error->getCode());
    }
}

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
