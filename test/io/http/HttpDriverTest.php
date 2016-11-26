<?php

use PHPUnit\Framework\TestCase;
use lola\io\http\HttpDriver;



class HttpDriverTest
extends TestCase
{

	public function testUseRequest() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpRequest', $driver->useRequest());
	}

	public function testUsePayload() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\payload\HttpPayload', $driver->usePayload());
	}

	public function testUseClient() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpClient', $driver->useClient());
	}

	public function testUseReply() {
		$driver = new HttpDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReply', $driver->useReply());
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
			->expects($this->any())
			->method('setTarget')
			->with($this->isInstanceOf('\lola\io\http\HttpDriver'))
			->will($this->returnValue($transform));

		$transform
			->expects($this->any())
			->method('process')
			->will($this->returnValue($transform));

		$driver
			->setReplyTransform($transform)
			->sendReply();
	}
}
