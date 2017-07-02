<?php

namespace test\io\connect;

use lola\io\connect\IConnection;
use PHPUnit\Framework\TestCase;

use lola\io\connect\IConnectionFactory;
use lola\io\connect\AConnectionFactory;
use lola\io\connect\Connection;



final class AConnectionFactoryTest
extends TestCase
{

	private function _mockFactory() : IConnectionFactory {
		$factory = $this
			->getMockBuilder(AConnectionFactory::class)
			->getMockForAbstractClass();


		$factory
			->expects($this->any())
			->method('_produceInstance')
			->with()
			->willReturnCallback(function() {
				return new Connection();
			});

		return $factory;
	}


	public function testInheritance() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(IConnectionFactory::class, $factory);
	}

	public function testGetConnection() {
		$factory = $this->_mockFactory();
		$connection = $factory->getConnection();

		$this->assertInstanceOf(IConnection::class, $connection);
		$this->assertSame($connection, $factory->getConnection());
	}
}
