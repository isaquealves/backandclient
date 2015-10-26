<?php

namespace App\Tests;

require_once __DIR__.'/../vendor/autoload.php';

use App\ApplicationClient;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;


 class ApplicationClientTest extends \PHPUnit_Framework_TestCase
 {
     private $applicationClient;

     public function setUp()
     {
        $this->applicationClient = new ApplicationClient();
     }

     public function testGetToken()
     {
         $tokenData = $this->applicationClient->getToken();
         $this->assertTrue(array_key_exists('access_token',$tokenData));
         $this->assertTrue(array_key_exists('token_type',$tokenData));

     }

 }
