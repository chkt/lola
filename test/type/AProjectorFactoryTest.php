<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjector;
use lola\type\AProjectorFactory;



final class AProjectorFactoryTest
extends TestCase
{

	private function _mockInjector(callable $fn = null) {
		if (is_null($fn)) $fn = function() {};

		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->expects($this->any())
			->method('produce')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback($fn);

		return $injector;
	}

	private function _mockFactory(IInjector& $injector, string $name = 'foo') : AProjectorFactory {
		return $this
			->getMockBuilder(AProjectorFactory::class)
			->setConstructorArgs([& $injector, $name ])
			->getMockForAbstractClass();
	}


	public function testSetConfig() {
		$injector = $this->_mockInjector();
		$factory = $this->_mockFactory($injector);

		$this->assertEquals($factory, $factory->setConfig([]));
	}

	public function testProduce() {
		$config = [
			'foo' => 1,
			'bar' => 2
		];
		$class = 'foobarbaz';

		$injector = $this->_mockInjector(function(string $name, array $deps) use ($class, $config) {
			$this->assertEquals($class, $name);
			$this->assertEquals($config, $deps);

			return 'quux';
		});

		$factory = $this
			->_mockFactory($injector, $class)
			->setConfig($config);

		$this->assertEquals('quux', $factory->produce());
	}

	public function testProduce_exception() {
		$injector = $this->_mockInjector();
		$factory = $this->_mockFactory($injector);

		$this->expectException(\ErrorException::class);

		$factory->produce();
	}
}
