<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

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
    $alive = false;
    $db = new SparQL\Connection('invalid');
    try{
      $alive = $db->alive(1000);
    }catch(Exception $e){
      $failed = true;
    }

    $this->assertTrue($failed);
    $this->assertFalse($alive);
  }

  public function testInvalidUrlConnection()
  {
    // Connecting to invalid endpoint should fail
    $failed = false;
    $alive = false;
    $db = new SparQL\Connection('http://rdf.ecs.soton.ac.uk/sparql/aaa');
    try{
      $alive = $db->alive(1000);
    }catch(Exception $e){
      $failed = true;
    }

    $this->assertFalse($failed);
    $this->assertFalse($alive);
  }

  public function testValidConnection()
  {
    // Connecting to invalid endpoint should fail
    $failed = false;
    $alive = false;
    $db = new SparQL\Connection('http://rdf.ecs.soton.ac.uk/sparql/');
    try{
      $alive = $db->alive(1000);
    }catch(Exception $e){
      $failed = true;
    }

    $this->assertFalse($failed);
    $this->assertTrue($alive);
  }

}
