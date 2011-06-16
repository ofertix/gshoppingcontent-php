<?php

require_once('GShoppingContent.php');

class GSC_TestProductCollection extends PHPUnit_Framework_TestCase {

    public function testDefaultXml() {
        $m = new GSC_ProductList();
        $this->assertContains('<feed', $m->toXML());
    }

    public function testAddProduct() {
        $l = new GSC_ProductList();
        $p = new GSC_Product();
        $p->setTitle('z');
        $l->addProduct($p);
        $this->assertContains('<title>z</title>', $l->toXML());
    }
}
 
class GSC_TestProduct extends PHPUnit_Framework_TestCase {
    public function testDefaultXml() {
        $m = new GSC_Product();
        $this->assertContains('<entry', $m->toXML());
    }

    public function testSetTitle() {
        $m = new GSC_Product();
        $m->setTitle('z');
        $this->assertContains('<title>z</title>', $m->toXML());
    }

    public function testAddFeature() {
        $m = new GSC_Product();
        $m->addFeature('z');
        $this->assertContains('<scp:feature>z</scp:feature>', $m->toXML());
    }

    public function testAddSecondFeature() {
        $m = new GSC_Product();
        $m->addFeature('z');
        $m->addFeature('y');
        $this->assertContains('<scp:feature>z</scp:feature>', $m->toXML());
        $this->assertContains('<scp:feature>y</scp:feature>', $m->toXML());
    }

    public function testClearFeatures() {
        $m = new GSC_Product();
        $m->addFeature('z');
        $m->addFeature('y');
        $m->clearAllFeatures();
        $this->assertNotContains('<scp:feature>', $m->toXML());
    }
}
?>
