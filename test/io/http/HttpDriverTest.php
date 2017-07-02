<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjector;
use lola\inject\IInjectable;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpMessageFactory;
use lola\io\http\HttpDriver;
use lola\io\http\HttpMessage;
use lola\io\mime\IMimePayload;



class HttpDriverTest
extends TestCase
{

	private function _mockMessageFactory() : IHttpMessageFactory {
		$ins = $this
			->getMockBuilder(IHttpMessageFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getMessage')
			->with()
			->willReturnCallback(function() {
				return new HttpMessage('');
			});

		return $ins;
	}

	private function _mockInjector() : IInjector {
		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->logicalOr(
				$this->equalTo(\lola\io\http\RemoteRequestFactory::class)
			))
			->willReturnCallback(function(string $qname) {
				switch($qname) {
					case \lola\io\http\RemoteRequestFactory::class : return $this->_mockMessageFactory();
				}
			});

		return $ins;
	}

	private function _produceDriver() {
		$injector = $this->_mockInjector();

		return new HttpDriver($injector);
	}


	public function testGetDependencyConfig() {
		$driver = $this->_produceDriver();

		$this->assertEquals(['injector:'], HttpDriver::getDependencyConfig([]));
		$this->assertInstanceOf(IInjectable::class, $driver);
	}

	public function testUseRequest() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpRequest', $driver->useRequest());
	}

	public function testUseRequestPayload() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\mime\MimePayload', $driver->useRequestPayload());
	}

	public function testUseClient() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpClient', $driver->useClient());
	}

	public function testUseReply() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReply', $driver->useReply());
	}

	public function testUseReplyPayload() {
		$driver = $this->_produceDriver();
		$reply =& $driver->useReply();
		$payload =& $driver->useReplyPayload();

		$this->assertInstanceOf(IMimePayload::class, $payload);

		$reply
			->setMime('application/json')
			->setEncoding('utf-8')
			->setBody('{"foo":"bar"}');

		$this->assertEquals([
			'foo' => 'bar'
		], $payload->get());
	}

	public function testUseCookies() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpCookies', $driver->useCookies());
	}

	public function testUseConfig() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpConfig', $driver->useConfig());
	}

	public function testSetConfig() {
		$driver = $this->_produceDriver();
		$config = new \lola\io\http\HttpConfig();

		$this->assertEquals($driver, $driver->setConfig($config));
		$this->assertEquals($config, $driver->useConfig());
	}


	public function testUseRequestMessage() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(IHttpMessage::class, $driver->useRequestMessage());
	}

	public function testSetRequestMessage() {
		$driver = $this->_produceDriver();
		$message = new HttpMessage('');

		$this->assertEquals($driver, $driver->setRequestMessage($message));
		$this->assertSame($message, $driver->useRequestMessage());
	}


	public function testUseReplyResource() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReplyResource', $driver->useReplyResource());
	}

	public function testSetReplyResource() {
		$driver = $this->_produceDriver();
		$resource = new \lola\io\http\HttpReplyResource();

		$this->assertEquals($driver, $driver->setReplyResource($resource));
		$this->assertEquals($resource, $driver->useReplyResource());
	}

	public function testUseReplyTransform() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf('\lola\io\http\HttpReplyTransform', $driver->useReplyTransform());
	}

	public function testSetReplyTransform() {
		$driver = $this->_produceDriver();
		$transform = new \lola\io\http\HttpReplyTransform;

		$this->assertEquals($driver, $driver->setReplyTransform($transform));
		$this->assertEquals($transform, $driver->useReplyTransform());
	}

	public function testSendReply() {
		$driver = $this->_produceDriver();

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
