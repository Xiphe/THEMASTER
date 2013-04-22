<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once '../vendor/autoload.php';
 
class THEDEBUGTests extends PHPUnit_Framework_TestCase {

    public function testClassExists() {
        $this->assertTrue(class_exists('\Xiphe\THEDEBUG'));
    }
}