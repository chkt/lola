<?php

namespace test\model\collection;


use PHPUnit\Framework\TestCase;

use eve\common\factory\IBaseFactory;
use eve\common\access\TraversableAccessor;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;
use lola\model\IResourceQuery;
use lola\model\collection\IResourceCollection;
use lola\model\collection\AResourceCollectionFactory;



final class AResourceCollectionFactoryTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => & $value) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;
			$ins->$prop =& $value;
		}

		return $ins;
	}

	private function _mockResource($mode = AResourceCollectionFactory::MODE_NONE) {
		$resource = $this
			->getMockBuilder(IResourceCollection::class)
			->getMock();

		if ($mode === AResourceCollectionFactory::MODE_READ) $resource
			->expects($this->once())
			->method('read')
			->with(
				$this->isInstanceOf(IResourceQuery::class),
				$this->isType('int'),
				$this->isType('int')
			)
			->willReturnSelf();

		return $resource;
	}

	private function _mockInjector(int $mode = AResourceCollectionFactory::MODE_NONE) {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		if ($mode === AResourceCollectionFactory::MODE_READ) $injector
			->expects($this->once())
			->method('produce')
			->with($this->equalTo('foo'))
			->willReturn($this->_mockResource($mode));

		return $injector;
	}

	private function _mockFactory(IInjector $injector = null, IBaseFactory $base = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($base)) $base = $this->_mockInterface(IBaseFactory::class);

		$factory = $this
			->getMockBuilder(AResourceCollectionFactory::class)
			->setConstructorArgs([ $base, $injector, 'foo', 'bar'])
			->getMockForAbstractClass();

		return $factory;
	}


	public function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}



	public function testInheritance() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(AStatelessInjectorFactory::class, $factory);
	}

	public function testDependencyConfig() {
		$this->assertEquals([
			'core:baseFactory',
			'injector:'
		], AResourceCollectionFactory::getDependencyConfig($this->_produceAccessor()));
	}


	public function test_produceInstance_read() {
		$data = [
			'mode' => AResourceCollectionFactory::MODE_READ
		];

		$injector = $this->_mockInjector(AResourceCollectionFactory::MODE_READ);
		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->expects($this->once())
			->method('newInstance')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $args) {
				$args['name'] = $qname;

				return $this->_mockInterface(IResourceQuery::class, $args);
			});

		$factory = $this->_mockFactory($injector, $base);

		$this->assertInstanceOf(IResourceCollection::class, $factory->produce($this->_produceAccessor($data)));
	}

	public function test_produceInstance_pass() {
		$resource = $this->_mockResource();
		$data = [
			'mode' => AResourceCollectionFactory::MODE_PASS,
			'resource' => $resource
		];

		$factory = $this->_mockFactory();

		$this->assertSame($resource, $factory->produce($this->_produceAccessor($data)));
	}

	public function test_produceInstance_other() {
		$data = [
			'mode' => AResourceCollectionFactory::MODE_NONE
		];

		$factory = $this->_mockFactory();

		$this->expectException(\ErrorException::class);

		$factory->produce($this->_produceAccessor($data));
	}
}
