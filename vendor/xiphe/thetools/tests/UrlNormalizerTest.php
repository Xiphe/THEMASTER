<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once '../THETOOLS.php';

use Xiphe as X;

class UrlNormalizerTest extends PHPUnit_Framework_TestCase {

	public function testClassExists() {
        $this->assertTrue(class_exists('\Xiphe\THETOOLS'));
    }

    public function testNormalisation1() {
    	$this->assertSame('http://example.org/?test', X\THETOOLS::normalizeUrl('http://example.org?test'));
    }
}