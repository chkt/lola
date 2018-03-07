<?php

namespace test\model;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\TraversableAccessor;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;
use lola\model\IResource;
use lola\model\IResourceQuery;
use lola\model\IModelFactory;
use lola\model\AResourceModelFactory;
use lola\type\StructuredData;



final class AResourceModelFactoryTest
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

	private function _mockModelFactory() {
		$data = $this->_mockInterface(ITraversableAccessor::class);
		$factory = $this->_mockInterface(IModelFactory::class);

		$factory
			->method('produceModelData')
			->willReturn($data);

		return $factory;
	}

	private function _mockResourceFactory(IInjector $injector = null, ICoreFactory $baseFactory = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($baseFactory)) $baseFactory = $this->_mockInterface(ICoreFactory::class);

		$factory = $this
			->getMockBuilder(AResourceModelFactory::class)
			->setConstructorArgs([
				$baseFactory,
				$injector,
				IModelFactory::class,
				IResource::class,
				IResourceQuery::class
			])
			->getMockForAbstractClass();

		return $factory;
	}

	private function _mockResource(int $mode = AResourceModelFactory::MODE_NONE) {
		$resource = $this
			->getMockBuilder(IResource::class)
			->getMock();

		if ($mode === AResourceModelFactory::MODE_CREATE) $resource
			->expects($this->once())
			->method('create')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturnSelf();
		else if ($mode === AResourceModelFactory::MODE_READ) $resource
			->expects($this->once())
			->method('read')
			->with($this->isInstanceOf(IResourceQuery::class))
			->willReturnSelf();

		return $resource;
	}

	private function _mockInjector(int $mode = AResourceModelFactory::MODE_NONE) {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		if ($mode === AResourceModelFactory::MODE_CREATE) {
			$injector
				->expects($this->at(0))
				->method('produce')
				->with(
					$this->equalTo(IModelFactory::class),
					$this->equalTo([
						'mode' => AResourceModelFactory::MODE_CREATE
					])
				)
				->willReturn($this->_mockModelFactory());

			$injector
				->expects($this->at(1))
				->method('produce')
				->with(
					$this->equalTo(IResource::class),
					$this->equalTo([])
				)
				->willReturn($this->_mockResource($mode));
		}
		else if ($mode === AResourceModelFactory::MODE_READ) $injector
			->expects($this->once())
			->method('produce')
			->with($this->equalTo(IResource::class))
			->willReturn($this->_mockResource($mode));

		return $injector;
	}


	private function _produceSampleData() : array {
		return [
			'foo' => 1,
			'bar' => 2,
			'baz' => [
				'foo' => 3,
				'bar' => 4
			]
		];
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$factory = $this->_mockResourceFactory();

		$this->assertInstanceOf(AStatelessInjectorFactory::class, $factory);
	}

	public function testDependencyConfig() {
		$this->assertEquals([
			'core:coreFactory',
			'injector:'
		], AResourceModelFactory::getDependencyConfig($this->_produceAccessor()));
	}


	public function test_produceInstance_create() {
		$injector = $this->_mockInjector(AResourceModelFactory::MODE_CREATE);
		$factory = $this->_mockResourceFactory($injector);

		$data = [
			'mode' => AResourceModelFactory::MODE_CREATE
		];

		$this->assertInstanceOf(IResource::class, $factory->produce($this->_produceAccessor($data)));
	}

	public function test_produceInstance_read() {
		$injector = $this->_mockInjector(AResourceModelFactory::MODE_READ);
		$baseFactory = $this->_mockInterface(ICoreFactory::class);

		$baseFactory
			->expects($this->once())
			->method('newInstance')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $args) {
				return $this->_mockInterface(IResourceQuery::class, $args);
			});

		$factory = $this->_mockResourceFactory($injector, $baseFactory);

		$data = [
			'mode' => AResourceModelFactory::MODE_READ,
			'map' => []
		];

		$this->assertInstanceOf(IResource::class, $factory->produce($this->_produceAccessor($data)));
	}

	public function test_produceInstance_pass() {
		$resource = $this->_mockResource();
		$factory = $this->_mockResourceFactory();

		$data = [
			'mode' => AResourceModelFactory::MODE_PASS,
			'resource' => $resource
		];

		$this->assertSame($resource, $factory->produce($this->_produceAccessor($data)));
	}

	public function test_produceInstance_other() {
		$factory = $this->_mockResourceFactory();

		$data = [
			'mode' => AResourceModelFactory::MODE_NONE
		];

		$this->expectException(\ErrorException::class);

		$factory->produce($this->_produceAccessor($data));
	}
}
