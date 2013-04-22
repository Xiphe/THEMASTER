<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once '../THETOOLS.php';

use Xiphe as X;

class FilterDataBy extends PHPUnit_Framework_TestCase {

    public function testClassExists() {
        $this->assertTrue(class_exists('\Xiphe\THETOOLS'));
    }
    
    public function testFilterDataBy() {
        $data = $this->_getFilterExample();

        $this->assertSame(
            array(
                'string' => 'foo',
                'url' => 'http://example.org/',
                'email' => 'someone@example.org',
                'int' => 4,
                'float' => 4.3,
                'bool' => true
            ),
            X\THETOOLS::filter_data_by('prefix', $data, array(
                'string' => 's',
                'url' => 'u',
                'email' => 'e',
                'int' => 'i',
                'float' => 'f',
                'bool' => 'b'
            ))
        );
    }

    public function testFilterDataByNegative() {
        $data = $this->_getFilterExample();
        
        $test = X\THETOOLS::filter_data_by('prefix', $data, array(
            'string' => 'b',
            'url' => 'e',
            'email' => 'i',
            'int' => 'u',
            'float' => 's',
            'bool' => 'f'
        ));

        $this->assertSame(
            array(
                'string' => false,
                'url' => 'httpexample.org',
                'email' => false,
                'int' => "4",
                'float' => "4.3",
                'bool' => 1.0
            ),
            $test
        );
    }

    public function testFilterRequestDataBy() {
        $_REQUEST = array('foo_bar' => 'a', 'bar' => 'asd');

        $this->assertSame(
            array(
                'bar' => 'a'
            ),
            X\THETOOLS::filter_requestDataBy('foo', array('bar' => 's'))
        );
    }

    public function testFilterPostDataBy() {
        $_POST = array('foo_bar' => 13, 'bar' => 'asd');

        $this->assertSame(
            array(
                'bar' => 13
            ),
            X\THETOOLS::filter_postDataBy('foo', array('bar' => 'i'))
        );
    }

    public function testFilterGetDataBy() {
        $_GET = array('foo_bar' => 'http://google.de/', 'bar' => 'asd');

        $this->assertSame(
            array(
                'bar' => 'http://google.de/'
            ),
            X\THETOOLS::filter_getDataBy('foo', array('bar' => 'u'))
        );
    }

    private function _getFilterExample() {
        return array(
            'prefix_string' => 'foo',
            'prefix_url' => 'http://example.org/',
            'prefix_email' => 'someone@example.org',
            'prefix_int' => 4,
            'prefix_float' => 4.3,
            'prefix_bool' => true,
            'noprefix' => 'data'
        );
    }
}
?>