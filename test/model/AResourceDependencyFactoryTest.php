<?php

namespace test\model;

use PHPUnit\Framework\TestCase;

use lola\type\StructuredData;
use lola\inject\IInjector;
use lola\model\IResource;
use lola\model\IResourceQuery;
use lola\model\IModelFactory;
use lola\model\AResourceDependencyFactory;



final class AResourceDependencyFactoryTest
extends TestCase
{

	public function testProduceCreate() {
		$config = [
			'mode' => AResourceDependencyFactory::MODE_CREATE
		];

		$data = [
			'foo' => 1,
			'bar' => 2,
			'baz' => [
				'foo' => 3,
				'bar' => 4
			]
		];

		$factory = $this
			->getMockBuilder(IModelFactory::class)
			->getMock();

		$factory
			->expects($this->once())
			->method('produceModelData')
			->with()
			->willReturn($data);

		$resource = $this
			->getMockBuilder(IResource::class)
			->getMock();

		$resource
			->expects($this->once())
			->method('create')
			->with($this->isInstanceOf(StructuredData::class))
			->willReturnSelf();

		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->expects($this->at(0))
			->method('produce')
			->with(
				$this->equalTo(IModelFactory::class),
				$this->equalTo($config)
			)
			->willReturn($factory);

		$injector
			->expects($this->at(1))
			->method('produce')
			->with(
				$this->equalTo(IResource::class),
				$this->equalTo([])
			)
			->willReturn($resource);

		$ins = $this
			->getMockBuilder(AResourceDependencyFactory::class)
			->setConstructorArgs([
				& $injector,
				IModelFactory::class,
				IResource::class,
				IResourceQuery::class
			])
			->getMockForAbstractClass();

		$ins->setConfig($config);

		$this->assertInstanceOf(IResource::class, $ins->produce());
	}
}
