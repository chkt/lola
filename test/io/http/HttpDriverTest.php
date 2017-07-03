<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjector;
use lola\inject\IInjectable;
use lola\type\AStateTransform;
use lola\io\connect\IConnection;
use lola\io\connect\IConnectionFactory;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpMessageFactory;
use lola\io\http\HttpDriver;
use lola\io\http\HttpMessage;
use lola\io\mime\IMimePayload;



class HttpDriverTest
extends TestCase
{

	private function _mockConnectionFactory() : IConnectionFactory {
		$ins = $this
			->getMockBuilder(IConnectionFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getConnection')
			->with()
			->willReturnCallback(function() {
				return new \lola\io\connect\Connection();
			});

		return $ins;
	}

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
				$this->equalTo(\lola\io\connect\RemoteConnectionFactory::class),
				$this->equalTo(\lola\io\http\RemoteRequestFactory::class),
				$this->equalTo(\lola\io\http\RemoteReplyFactory::class)
			))
			->willReturnCallback(function(string $qname) {
				switch($qname) {
					case \lola\io\connect\RemoteConnectionFactory::class : return $this->_mockConnectionFactory();
					case \lola\io\http\RemoteRequestFactory::class : return $this->_mockMessageFactory();
					case \lola\io\http\RemoteReplyFactory::class : return new $qname();
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


	public function testUseConnection() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(IConnection::class, $driver->useConnection());
	}

	public function testSetConnection() {
		$driver = $this->_produceDriver();
		$connection = new \lola\io\connect\Connection();

		$this->assertSame($driver, $driver->setConnection($connection));
		$this->assertSame($connection, $driver->useConnection());
	}


	public function testUseRequestMessage() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(IHttpMessage::class, $driver->useRequestMessage());
	}

	public function testSetRequestMessage() {
		$driver = $this->_produceDriver();
		$message = new HttpMessage('');

		$this->assertSame($driver, $driver->setRequestMessage($message));
		$this->assertSame($message, $driver->useRequestMessage());
	}


	public function testUseReplyMessage() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(IHttpMessage::class, $driver->useReplyMessage());
	}

	public function testSetReplyMessage() {
		$driver = $this->_produceDriver();
		$message = new HttpMessage('');

		$this->assertSame($driver, $driver->setReplyMessage($message));
		$this->assertSame($message, $driver->useReplyMessage());
	}


	public function testSendReply() {
		$driver = $this->_produceDriver();

		$transform = $this
			->getMockBuilder(AStateTransform::class)
			->setMethods(['setTarget', 'process'])
			->getMockForAbstractClass();

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
