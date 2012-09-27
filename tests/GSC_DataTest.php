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
        $l->addEntry($p);
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

    public function testGenericAttribute() {
        $m = new GSC_Product();
        $m->setAttribute("3", "quantity", null, "kg");
        $this->assertContains(
            '<sc:attribute name="quantity" unit="kg">3</sc:attribute>',
            $m->toXML()
        );
        $m->setAttribute("4", "size");
        $this->assertContains(
            '<sc:attribute name="quantity" unit="kg">3</sc:attribute>',
            $m->toXML()
        );
        $this->assertContains(
            '<sc:attribute name="size">4</sc:attribute>',
            $m->toXML()
        );
    }

    public function testGenericGroup() {
        $m = new GSC_Product();
        $attr1 = $m->_createAttribute("3", "quantity", null, "kg");
        $attr2 = $m->_createAttribute("4", "size");
        $attrs = array($attr1, $attr2);
        $m->setGroup('sample', $attrs);
        $groupText = '<sc:group name="sample"><sc:attribute name="quantity" ' .
                     'unit="kg">3</sc:attribute><sc:attribute name="size">4' .
                     '</sc:attribute></sc:group>';

        $this->assertContains($groupText, $m->toXML());
    }

    public function testGenericGroup() {
        $m = new GSC_Product();
        $m->setAgeGroup('adult');
        $this->assertContains(
            '<scp:age_group>adult</scp:age_group>',
            $m->toXML()
        );
    }
}
?>
