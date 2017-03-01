<?php

namespace test\model\collection;

use PHPUnit\Framework\TestCase;

use test\model\collection\MockModel;
use lola\inject\IInjector;
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
			->with($this->equalTo(MockModel::class), $this->isType('array'))
			->willReturnCallback(function($name, array $config) {
				$this->assertArrayHasKey('mode', $config);
				$this->assertEquals(AResourceDependencyFactory::MODE_PASS, $config['mode']);
				$this->assertArrayHasKey('resource', $config);
				$this->assertInstanceOf(IResource::class, $config['resource']);

				return new $name();
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

		$collection = $this->_mockCollection($injector, $resource, MockModel::class);

		$item0 =& $collection->useIndex(0);

		$this->assertInstanceOf(MockModel::class, $item0);

		$item1 =& $collection->useIndex(0);

		$this->assertInstanceOf(MockModel::class, $item1);
		$this->assertSame($item1, $item0);
	}
}
