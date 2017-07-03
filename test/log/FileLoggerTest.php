<?php

namespace test\log;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\inject\IInjector;
use lola\io\connect\IConnection;
use lola\io\connect\Connection;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpMessage;
use lola\io\http\HttpDriver;
use lola\log\ILogger;
use lola\log\FileLogger;
use test\io\http\MockDriver;
use lola\ctrl\AReplyController;



final class FileLoggerTest
extends TestCase
{

	use PHPMock;


	private function _mockInjector() : IInjector {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->expects($this->any())
			->method('produce')
			->with($this->logicalOr(
				$this->equalTo(\lola\io\http\RemoteReplyFactory::class)
			))
			->willReturnCallback(function (string $qname) {
				return new $qname;
			});

		return $injector;
	}

	private function _produceConnection() : IConnection {
		$data = [];

		return new Connection($data);
	}

	private function _produceRequestMessage() : IHttpMessage {
		return new HttpMessage('GET / HTTP/1.1');
	}

	private function _produceDriver(IInjector $injector = null) : IHttpDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$driver = new HttpDriver($injector);
		$connection = $this->_produceConnection();
		$request = $this->_produceRequestMessage();

		$driver
			->setConnection($connection)
			->setRequestMessage($request);

		return $driver;
	}

	private function _produceLogger() : ILogger {
		return new Filelogger();
	}


	private function _mock_error_log(string $message) {
		$this
			->getFunctionMock('\lola\log', 'error_log')
			->expects($this->exactly(1))
			->with($this->isType('string'))
			->willReturnCallback(function($string) use ($message) {
				$this->assertEquals($message, $string);

				return true;
			});
	}


	public function testLogRequest() {
		$this->_mock_error_log("\033[35m\033[1m<\033[0m \033[1mPOST\033[0m \033[32m\033[1m/path/to/resource\033[0m ");

		$logger = $this->_produceLogger();
		$driver = $this->_produceDriver();
		$request =& $driver->useRequest();

		$request
			->setMethod('POST')
			->setPath('/path/to/resource');

		$logger->logRequest($request);
	}

	public function testLogReply() {
		$this->_mock_error_log("\033[35m\033[1m>\033[0m \033[1m302 - Found\033[0m REDIRECT \033[32m\033[1m/path/to/resource\033[0m ");

		$logger = $this->_produceLogger();
		$driver = $this->_produceDriver();
		$reply =& $driver->useReply();

		$reply
			->setCode('302')
			->setRedirectTarget('/path/to/resource');

		$logger->logReply($reply);
	}

	public function testLogClient() {
		$this->_mock_error_log("\033[35m\033[1m~\033[0m Mozilla/5.0 ");

		$logger = $this->_produceLogger();
		$driver = $this->_produceDriver();
		$client =& $driver->useClient();

		$client
			->setUA('Mozilla/5.0')
			->setIP('::1');

		$logger->logClient($client);
	}

	public function testLogCtrlState() {
		$this->_mock_error_log("\033[35m\033[1m<\033[0m \033[1mPOST\033[0m \033[32m\033[1m/path/to/resource\033[0m \033[35m\033[1m>\033[0m \033[1m302 - Found\033[0m REDIRECT \033[32m\033[1m/path/to/res\033[0m ");

		$logger = $this->_produceLogger();
		$driver = $this->_produceDriver();

		$ctrl = $this
			->getMockBuilder(AReplyController::class)
			->setConstructorArgs([& $driver ])
			->getMockForAbstractClass();

		$ctrl
			->useRequest()
			->setMethod('POST')
			->setPath('/path/to/resource')
			->useClient()
			->setUA('Mozilla/5.0')
			->setIP('::1');

		$ctrl
			->useReply()
			->setCode('302')
			->setRedirectTarget('/path/to/res');

		$logger->logCtrlState($ctrl);
	}
}
