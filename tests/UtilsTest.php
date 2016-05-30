<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require_once dirname(dirname(__FILE__)) . '/utils/wpsparql_utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase
{

  public function setUp()
  {

  }

  public function tearDown()
  {
      // undo stuff here
  }

  public function testIsValidUrl()
  {
    $this->assertTrue(wpsparql_is_valid_url('http://www.google.com'));
    $this->assertTrue(wpsparql_is_valid_url('https://pp.opendevelopmentmekong.net/topics/agriculture-and-fishing'));
    $this->assertFalse(wpsparql_is_valid_url('https://bla*foo.com'));
  }

}
