<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once '../THETOOLS.php';

use Xiphe as X;

class PHPQueryTest extends PHPUnit_Framework_TestCase {

    public function testClassExists() {
        $this->assertTrue(class_exists('\Xiphe\THETOOLS'));
    }

    public function testPHPQueryMissinException() {
    	try {
    		X\THETOOLS::initPhpQuery();
    	} catch (X\THETOOLSException $e) {
    		return;
    	}

    	$this->fail('PHPQuery missing exception has not been raised.');
    }

     public function testPHPQueryInit() {
     	require_once '../vendor/autoload.php';

    	try {
    		X\THETOOLS::initPhpQuery();
    	} catch (X\THETOOLSException $e) {
    		$this->fail('PHPQuery autoloading failed.');
    	}
    }

    public function testPHPQuery() {
    	$test = X\THETOOLS::pq('<div id="foo">bar</div>');
    	
    	$this->assertSame('phpQueryObject', get_class($test));

    	$this->assertSame('<div id="foo">bar</div>', $test->htmlOuter());
    }
}