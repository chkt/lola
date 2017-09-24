<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use eve\access\TraversableAccessor;
use eve\inject\IInjector;
use lola\type\AProjectorFactory;
use lola\common\factory\AStatelessInjectorFactory;



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

	private function _mockFactory(IInjector $injector = null, string $name = 'foo') {
		if (is_null($injector)) $injector = $this->_mockInjector();

		return $this
			->getMockBuilder(AProjectorFactory::class)
			->setConstructorArgs([ $injector, $name ])
			->getMockForAbstractClass();
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(AStatelessInjectorFactory::class, $factory);
	}

	public function testDependencyConfig() {
		$this->assertEquals([ 'injector:' ], AProjectorFactory::getDependencyConfig($this->_produceAccessor()));
	}


	public function test_produceInstance() {
		$data = [
			'foo' => 1,
			'bar' => 2
		];

		$injector = $this->_mockInjector(function(string $qname, array $config) use ($data) {
			$this->assertEquals('bar', $qname);
			$this->assertEquals($data, $config);

			return 'baz';
		});
		$factory = $this->_mockFactory($injector, 'bar');

		$this->assertEquals('baz', $factory->produce($this->_produceAccessor($data)));
	}
}
