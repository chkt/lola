<?php

namespace test\model\collection;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjector;
use lola\model\IModel;
use lola\model\IResource;
use lola\model\AResourceDependencyFactory;
use lola\model\collection\ACollection;
use lola\model\collection\IResourceCollection;



final class ACollectionTest
extends TestCase
{

	private function _mockInjector() {
		return $this
			->getMockBuilder(IInjector::class)
			->getMock();
	}

	private function _mockResource() {
		return $this
			->getMockBuilder(IResource::class)
			->getMock();
	}

	private function _mockResourceCollection() {
		return $this
			->getMockBuilder(IResourceCollection::class)
			->getMock();
	}

	private function _mockModel() {
		return $this
			->getMockBuilder(IModel::class)
			->getMock();
	}

	private function _mockCollection(
		IInjector& $injector,
		IResourceCollection& $collection,
		string $modelName
	) : ACollection {
		return $this
			->getMockBuilder(ACollection::class)
			->setConstructorArgs([
				& $injector,
				& $collection,
				$modelName
			])
			->getMockForAbstractClass();
	}


	public function test_useItem() {
		$injector = $this->_mockInjector();

		$injector
			->expects($this->exactly(1))
			->method('produce')
			->with($this->equalTo('foo'), $this->isType('array'))
			->willReturnCallback(function(string $name, array $config) : IModel {
				$this->assertEquals('foo', $name);
				$this->assertArrayHasKey('mode', $config);
				$this->assertEquals(AResourceDependencyFactory::MODE_PASS, $config['mode']);
				$this->assertArrayHasKey('resource', $config);
				$this->assertInstanceOf(IResource::class, $config['resource']);

				return $this->_mockModel();
			});

		$resource = $this->_mockResourceCollection();

		$resource
			->expects($this->exactly(1))
			->method('useItem')
			->with($this->equalTo(0))
			->willReturn($this->_mockResource());

		$resource
			->expects($this->exactly(1))
			->method('getLength')
			->with()
			->willReturn(1);

		$collection = $this->_mockCollection($injector, $resource, 'foo');

		$item0 =& $collection->useIndex(0);

		$this->assertInstanceOf(IModel::class, $item0);

		$item1 =& $collection->useIndex(0);

		$this->assertInstanceOf(IModel::class, $item1);
		$this->assertSame($item1, $item0);
	}


	public function testGetProjection() {
		$injector = $this->_mockInjector();

		$injector
			->expects($this->once())
			->method('produce')
			->with($this->equalTo('foo'), $this->isType('array'))
			->willReturnCallback(function() : IModel {
				$model = $this->_mockModel();

				$model
					->expects($this->once())
					->method('getProjection')
					->with($this->isType('array'))
					->willReturn([
						'foo' => 1,
						'bar' => 2
					]);

				return $model;
			});

		$resource = $this->_mockResourceCollection();

		$resource
			->expects($this->at(0))
			->method('getLength')
			->with()
			->willReturn(1);

		$resource
			->expects($this->at(1))
			->method('useItem')
			->with()
			->willReturnCallback(function() {
				return $this->_mockResource();
			});

		$collection = $this->_mockCollection($injector, $resource, 'foo');

		$this->assertEquals([[
			'foo' => 1,
			'bar' => 2
		]], $collection->getProjection());
	}
}
