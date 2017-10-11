<?php

require_once('test/io/http/MockDriver.php');

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use eve\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use lola\log\ILogger;
use lola\log\FileLogger;
use test\io\http\MockDriver;
use lola\ctrl\AReplyController;



final class FileLoggerTest
extends TestCase
{

	use PHPMock;


	private function _produceAccessor() :TraversableAccessor {
		$data = [];

		return new TraversableAccessor($data);
	}

	private function _produceLogger() : FileLogger {
		return new FileLogger();
	}


	public function testInheritance() {
		$logger = $this->_produceLogger();

		$this->assertInstanceOf(ILogger::class, $logger);
		$this->assertInstanceOf(IInjectableIdentity::class, $logger);
		$this->assertInstanceOf(IInjectable::class, $logger);
	}

	public function testDependencyConfig() {
		$this->assertEquals([], FileLogger::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, FileLogger::getInstanceIdentity($this->_produceAccessor()));
	}



	public function testLogRequest() {
		$this
			->getFunctionMock('\lola\log', 'error_log')
			->expects($this->exactly(1))
			->with($this->isType('string'))
			->willReturnCallback(function($string) {
				$this->assertEquals("\033[35m\033[1m<\033[0m \033[1mPOST\033[0m \033[32m\033[1m/path/to/resource\033[0m ", $string);

				return true;
			});

		$logger = new FileLogger();
		$driver = new MockDriver();
		$request =& $driver->useRequest();

		$request
			->setMethod('POST')
			->setPath('/path/to/resource');

		$logger->logRequest($request);
	}

	public function testLogReply() {
		$this
			->getFunctionMock('\lola\log', 'error_log')
			->expects($this->exactly(1))
			->with($this->isType('string'))
			->willReturnCallback(function ($string) {
				$this->assertEquals("\033[35m\033[1m>\033[0m \033[1m302 - Found\033[0m REDIRECT \033[32m\033[1m/path/to/resource\033[0m ", $string);

				return true;
			});

		$logger = new FileLogger();
		$driver = new MockDriver();
		$reply =& $driver->useReply();

		$reply
			->setCode('302')
			->setRedirectTarget('/path/to/resource');

		$logger->logReply($reply);
	}

	public function testLogClient() {
		$this
			->getFunctionMock('\lola\log', 'error_log')
			->expects($this->exactly(1))
			->with($this->isType('string'))
			->willReturnCallback(function($string) {
				$this->assertEquals("\033[35m\033[1m~\033[0m Mozilla/5.0 ", $string);

				return true;
			});

		$logger = new FileLogger();
		$driver = new MockDriver();
		$client =& $driver->useClient();

		$client
			->setUA('Mozilla/5.0')
			->setIP('::1');

		$logger->logClient($client);
	}

	public function testLogCtrlState() {
		$errorLog = $this->getFunctionMock('\lola\log', 'error_log');

		$errorLog
			->expects($this->at(0))
			->with($this->isType('string'))
			->willReturnCallback(function($string) {
				$this->assertEquals("\033[35m\033[1m<\033[0m \033[1mPOST\033[0m \033[32m\033[1m/path/to/resource\033[0m \033[35m\033[1m>\033[0m \033[1m302 - Found\033[0m REDIRECT \033[32m\033[1m/path/to/res\033[0m ", $string);

				return true;
			});

		$logger = new FileLogger();
		$driver = new MockDriver();

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

	public function testLogError() {
		$errorLog = $this->getFunctionMock('\lola\log', 'error_log');

		$errorLog
			->expects($this->at(0))
			->with($this->isType('string'))
			->willReturnCallback(function(string $string) {
				return $this->assertEquals("\033[31m\033[1m! ERROR\033[0m \"Foo is not a valid concept\" IN \033[31m/path/to/file.php\033[0m:13 ", $string);
			});

		$logger = $this->_produceLogger();

		$this->assertSame($logger, $logger->logError([
			'type' => E_ERROR,
			'message' => 'Foo is not a valid concept',
			'file' => '/path/to/file.php',
			'line' => 13
		]));
	}
}
