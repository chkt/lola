<?php

namespace test\common\projection;

use eve\common\factory\IBaseFactory;
use lola\common\projection\IProjector;
use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjector;
use lola\common\projection\AProjectorFactory;
use lola\common\factory\AStatelessInjectorFactory;



final class AProjectorFactoryTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockFactory(IBaseFactory $base = null, string $name = 'foo') {
		if (is_null($base)) $base = $this->_mockInterface(IBaseFactory::class);

		return $this
			->getMockBuilder(AProjectorFactory::class)
			->setConstructorArgs([ $base, $name ])
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
		$this->assertEquals([ 'core:baseFactory' ], AProjectorFactory::getDependencyConfig($this->_produceAccessor()));
	}


	public function test_produceInstance() {
		$data = [
			'foo' => 1,
			'bar' => 2
		];

		$projector = $this->_mockInterface(IProjector::class);
		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('produce')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $config) use ($data, $projector) {
				$this->assertEquals('bar', $qname);
				$this->assertArrayHasKey(0, $config);
				$this->assertEquals($data, $config[0]);

				return $projector;
			});

		$factory = $this->_mockFactory($base, 'bar');

		$this->assertSame($projector, $factory->produce($this->_produceAccessor($data)));
	}
}
