<?php

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

class SparqlTest extends PHPUnit_Framework_TestCase
{

  public function setUp()
  {

  }

  public function tearDown()
  {
      // undo stuff here
  }

  public function testInvalidConnection()
  {
    // Connecting to invalid endpoint should fail
    $failed = false;
    $db = new SparQL\Connection('invalid');
    try{
      $db->alive(1000);
    }catch(Exception $e){
      $failed = true;
    }

    $this->assertTrue($failed);
  }

  public function testValidConnection()
  {
    // Connecting to invalid endpoint should fail
    $failed = false;
    $db = new SparQL\Connection('http://rdf.ecs.soton.ac.uk/sparql/');
    try{
      $db->alive(1000);
    }catch(Exception $e){
      $failed = true;
    }

    $this->assertFalse($failed);
  }

}
