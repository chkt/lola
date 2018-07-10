<?php

use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use lola\io\http\HttpDriver;



class HttpDriverTest
extends TestCase
{

	private function _produceDriver() {
		return new HttpDriver();
	}

	private function _produceAccessor() {
		$data = [];

		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$driver =  $this->_produceDriver();

		$this->assertInstanceOf(IInjectable::class, $driver);
		$this->assertInstanceOf(IInjectableIdentity::class, $driver);
	}

	public function testDependencyConfig() {
		$this->assertEquals([], HttpDriver::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_DEFAULT, HttpDriver::getInstanceIdentity($this->_produceAccessor()));
	}


	public function testGetRequest() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpRequest', $driver->getRequest());
	}

	public function testUsePayload() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\payload\HttpPayload', $driver->usePayload());
	}

	public function testUseClient() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpClient', $driver->useClient());
	}

	public function testGetReply() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReply', $driver->getReply());
	}

	public function testUseCookies() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpCookies', $driver->useCookies());
	}

	public function testUseConfig() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpConfig', $driver->useConfig());
	}

	public function testSetConfig() {
		$driver = new HttpDriver();
		$config = new \lola\io\http\HttpConfig();

		$this->assertEquals($driver, $driver->setConfig($config));
		$this->assertEquals($config, $driver->useConfig());
	}

	public function testUseRequestResource() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpRequestResource', $driver->useRequestResource());
	}

	public function testSetRequestResource() {
		$driver = new HttpDriver();
		$resource = new \lola\io\http\HttpRequestResource();

		$this->assertEquals($driver, $driver->setRequestResource($resource));
		$this->assertEquals($resource, $driver->useRequestResource());
	}

	public function testUseReplyResource() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReplyResource', $driver->useReplyResource());
	}

	public function testSetReplyResource() {
		$driver = new HttpDriver();
		$resource = new \lola\io\http\HttpReplyResource();

		$this->assertEquals($driver, $driver->setReplyResource($resource));
		$this->assertEquals($resource, $driver->useReplyResource());
	}

	public function testUseReplyTransform() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReplyTransform', $driver->useReplyTransform());
	}

	public function testSetReplyTransform() {
		$driver = new HttpDriver();
		$transform = new \lola\io\http\HttpReplyTransform;

		$this->assertEquals($driver, $driver->setReplyTransform($transform));
		$this->assertEquals($transform, $driver->useReplyTransform());
	}

	public function testSendReply() {
		$driver = new HttpDriver();

		$transform = $this
			->getMockBuilder('\lola\io\http\HttpReplyTransform')
			->setMethods(['setTarget', 'process'])
			->getMock();

		$transform
			->expects($this->at(0))
			->method('setTarget')
			->with($this->isInstanceOf('\lola\io\http\HttpDriver'))
			->will($this->returnValue($transform));

		$transform
			->expects($this->at(1))
			->method('process')
			->will($this->returnValue($transform));

		$driver
			->setReplyTransform($transform)
			->sendReply();
	}
}
