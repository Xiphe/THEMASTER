<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once '../THETOOLS.php';

use Xiphe as X;

class IsBrowser extends PHPUnit_Framework_TestCase {

    public function testClassExists() {
        $this->assertTrue(class_exists('\Xiphe\THETOOLS'));
    }

    public function testBrowserClassMissingException() {
    	try {
    		X\THETOOLS::get_browser();
    	} catch (X\THETOOLSException $e) {
    		return;
    	}

    	$this->fail('Browser Class missing exception has not been raised.');
    }

    public function testBrowserClassAutoLoading() {
     	require_once '../vendor/autoload.php';

    	try {
    		X\THETOOLS::get_browser();
    	} catch (X\THETOOLSException $e) {
    		$this->fail('PHPQuery autoloading failed.');
    	}
    }

    public function testBrowserClassName() {
    	$this->assertSame('Ikimea\Browser\Browser', get_class(X\THETOOLS::get_browserObj()));
    }

    public function testBrowserGetters() {
    	$this->_fakeMozillaFirefox();

    	$this->assertSame('Firefox', X\THETOOLS::get_browser());
    	$this->assertSame('17.2', X\THETOOLS::get_browserVersion());
    	$this->assertSame('Gecko', X\THETOOLS::get_layoutEngine());
    }

    public function testIsBrowserSingle() {
    	$this->_fakeMozillaFirefox();

    	/* basic */
    	$this->assertTrue(X\THETOOLS::is_browser('ff'));
    	$this->assertFalse(X\THETOOLS::is_browser('ie'));

    	/* Version equals */
    	$this->assertFalse(X\THETOOLS::is_browser('ff==17'));
    	$this->assertTrue(X\THETOOLS::is_browser('ff==17.2'));
    	$this->assertFalse(X\THETOOLS::is_browser('ff==17.0'));
    	$this->assertFalse(X\THETOOLS::is_browser('ff==17.5'));

    	/* Version not equals */
    	$this->assertTrue(X\THETOOLS::is_browser('ff!=17'));
    	$this->assertFalse(X\THETOOLS::is_browser('ff!=17.2'));
    	$this->assertTrue(X\THETOOLS::is_browser('ff!=17.0'));
    	$this->assertTrue(X\THETOOLS::is_browser('ff!=16'));
    	
    	/* Version gt/lt */
    	$this->assertFalse(X\THETOOLS::is_browser('ff>18'));
    	$this->assertTrue(X\THETOOLS::is_browser('ff>17'));
    	$this->assertFalse(X\THETOOLS::is_browser('ff<17'));
    	$this->assertTrue(X\THETOOLS::is_browser('ff<17.3'));

    	/* Version Wildcards */
    	$this->assertTrue(X\THETOOLS::is_browser('ff==17.*'));
    	$this->assertFalse(X\THETOOLS::is_browser('ff==16.*'));
    	$this->assertTrue(X\THETOOLS::is_browser('ff!=16.*'));
    }

    public function testBrowserMultipleTrue() {
    	$this->_fakeMozillaFirefox();

    	$this->assertTrue(X\THETOOLS::is_browser('gc||ff==17.2||ie'));
    }

    public function testBrowserMultipleFalse() {
    	$this->_fakeMozillaFirefox();

    	$this->assertFalse(X\THETOOLS::is_browser('gc||ie'));
    }

    public function testBrowserTypes() {
    	$this->_fakeMozillaFirefox();

    	$this->assertTrue(X\THETOOLS::is_browser('desktop'));
    	$this->assertTrue(X\THETOOLS::is_browser('no-phone'));
    	$this->assertFalse(X\THETOOLS::is_browser('phone'));
    	$this->assertFalse(X\THETOOLS::is_browser('mobile'));

    	$this->_fakeIPhone();
    	$this->assertTrue(X\THETOOLS::is_browser('phone'));
    	$this->assertTrue(X\THETOOLS::is_browser('mobile'));
    	$this->assertFalse(X\THETOOLS::is_browser('no-phone'));
    	$this->assertFalse(X\THETOOLS::is_browser('desktop'));
    }

    private function _fakeMozillaFirefox() {
    	X\THETOOLS::unset_browserObj();
    	$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:17.0) Gecko/20100101 Firefox/17.2';
    }

    private function _fakeIPhone() {
    	X\THETOOLS::unset_browserObj();
    	$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A498b Safari/419.3';
    }
}