<?php

require_once('test/io/http/MockDriver.php');

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\log\FileLogger;
use test\io\http\MockDriver;
use lola\ctrl\AReplyController;



final class FileLoggerTest
extends TestCase
{

	use PHPMock;



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
}
