<?php

require_once('MockRequestResource.php');

use PHPUnit\Framework\TestCase;

use lola\io\http\HttpClient;
use lola\io\http\HttpDriver;
use test\io\http\MockRequestResource;



class HttpClientTest
extends TestCase
{

	private $_driver;


	public function __construct() {
		parent::__construct();

		$request = new MockRequestResource();
		$driver = new HttpDriver();
		$driver->setRequestResource($request);

		$this->_driver = $driver;
	}


	public function testIsIP4() {
		$client = new HttpClient($this->_driver);

		$this->assertTrue($client->isIP4());

		$client->setIP('::1');

		$this->assertFalse($client->isIP4());
	}

	public function testIsIP6() {
		$client = new HttpClient($this->_driver);

		$this->assertFalse($client->isIP6());

		$client->setIP('::1');

		$this->assertTrue($client->isIP6());
	}

	public function testGetIP() {
		$client = new HttpClient($this->_driver);

		$this->assertEquals($client->getIP(), '127.0.0.1');
	}

	public function testSetIP() {
		$client = new HttpClient($this->_driver);

		$this->assertEquals($client->setIP('::1'), $client);
		$this->assertEquals($client->getIP(), '::1');
	}

	public function testGetUA() {
		$client = new HttpClient($this->_driver);

		$this->assertEquals($client->getUA(), 'Mozilla/5.0');
	}

	public function testSetUA() {
		$client = new HttpClient($this->_driver);

		$this->assertEquals($client->setUA('foo'), $client);
		$this->assertEquals($client->getUA(), 'foo');
	}

	public function testGetTime() {
		$client = new HttpClient($this->_driver);

		$this->assertEquals($client->getTime(), 2);
	}

	public function testSetTime() {
		$client = new HttpClient($this->_driver);

		$this->assertEquals($client->setTime(3), $client);
		$this->assertEquals($client->gettime(), 3);
	}
}
