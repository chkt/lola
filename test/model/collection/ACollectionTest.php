<?php

namespace test\model\collection;

use PHPUnit\Framework\TestCase;

use eve\inject\IInjector;
use lola\model\IModel;
use lola\model\IResource;
use lola\model\AResourceModelFactory;
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
		IInjector $injector = null,
		IResourceCollection $resourceCollection = null,
		string $modelName = null
	) : ACollection {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($resourceCollection)) $resourceCollection = $this->_mockResourceCollection();
		if (is_null($modelName)) $modelName = 'foo';

		$collection = $this
			->getMockBuilder(ACollection::class)
			->setConstructorArgs([
				$injector,
				$resourceCollection,
				$modelName
			])
			->getMockForAbstractClass();

		return $collection;
	}


	public function testInheritance() {
		$collection = $this->_mockCollection();

		$this->assertInstanceOf(\lola\model\collection\ICollection::class, $collection);
		$this->assertInstanceOf(\eve\common\projection\IProjectable::class, $collection);
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
				$this->assertEquals(AResourceModelFactory::MODE_PASS, $config['mode']);
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
			->expects($this->any())
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


	public function testHasItems() {
		$injector = $this->_mockInjector();
		$resource = $this->_mockResourceCollection();

		$live = true;
		$num = 3;

		$resource
			->expects($this->any())
			->method('isLive')
			->with()
			->willReturnReference($live);

		$resource
			->expects($this->any())
			->method('getLength')
			->with()
			->willReturnReference($num);

		$collection = $this->_mockCollection($injector, $resource, 'foo');

		$this->assertEquals(true, $collection->hasItems());

		$num = 0;

		$this->assertEquals(false, $collection->hasItems());

		$live = false;
		$resource
			->expects($this->any())
			->method('getLength')
			->with()
			->willThrowException(new \ErrorException());

		$this->assertEquals(false, $collection->hasItems());
	}


	public function testGetLength() {
		$injector = $this->_mockInjector();
		$resource = $this->_mockResourceCollection();

		$resource
			->expects($this->once())
			->method('getLength')
			->with()
			->willReturn(2);

		$collection = $this->_mockCollection($injector, $resource, 'foo');

		$this->assertEquals(2, $collection->getLength());
	}

	public function testGetLength_error() {
		$injector = $this->_mockInjector();
		$resource = $this->_mockResourceCollection();

		$resource
			->expects($this->once())
			->method('getLength')
			->with()
			->willThrowException(new \ErrorException());

		$this->expectException(\ErrorException::class);

		$collection = $this->_mockCollection($injector, $resource, 'foo');
		$collection->getLength();
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
