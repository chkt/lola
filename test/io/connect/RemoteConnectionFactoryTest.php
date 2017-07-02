<?php

namespace test\io\connect;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\io\connect\IConnection;
use lola\io\connect\AConnectionFactory;
use lola\io\connect\RemoteConnectionFactory;



final class RemoteConnectionFactoryTest
extends TestCase
{

	use PHPMock;


	private function _mock_filter_input() {
		$fn = $this->getFunctionMock('\lola\io\connect', 'filter_input');
		$fn
			->expects($this->exactly(4))
			->with($this->equalTo(INPUT_SERVER), $this->logicalOr(
				$this->equalTo('HTTPS'),
				$this->equalTo('REMOTE_ADDR'),
				$this->equalTo('SERVER_NAME'),
				$this->equalTo('SERVER_ADDR')
			))
			->willReturnCallback(function(int $source, string $prop) {
				switch ($prop) {
					case 'HTTPS' : return 'off';
					case 'REMOTE_ADDR' : return '127.0.0.1';
					case 'SERVER_NAME' : return 'localhost';
					case 'SERVER_ADDR' : return '::1';
				}
			});
	}


	private function _produceFactory() {
		return new RemoteConnectionFactory();
	}


	public function testInheritance() {
		$factory = $this->_produceFactory();

		$this->assertInstanceOf(AConnectionFactory::class, $factory);
	}

	public function testGetConnection() {
		$this->_mock_filter_input();

		$instance = $this
			->_produceFactory()
			->getConnection();

		$this->assertInstanceOf(IConnection::class, $instance);
		$this->assertEquals($_SERVER['REQUEST_TIME'], $instance->getInt(IConnection::CONNECTION_TIME));
		$this->assertFalse($instance->getBool(IConnection::CONNECTION_TLS));
		$this->assertEquals('127.0.0.1', $instance->getString(IConnection::CLIENT_IP));
		$this->assertEquals('localhost', $instance->getString(IConnection::HOST_NAME));
		$this->assertEquals('::1', $instance->getString(IConnection::HOST_IP));
	}
}
