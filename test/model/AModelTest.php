<?php

namespace test\model;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use lola\common\projection\IProjector;
use lola\model\IResource;
use lola\model\AModel;



final class AModelTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockResource(ITraversableAccessor $data = null) {
		if (is_null($data)) $data = $this->_mockInterface(ITraversableAccessor::class);

		$resource = $this->_mockInterface(IResource::class);

		$resource
			->method('getData')
			->with()
			->willReturn($data);

		return $resource;
	}

	private function _mockProjector() {
		$projector = $this
			->getMockBuilder(IProjector::class)
			->getMock();

		$projector
			->method('setSource')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturnSelf();

		return $projector;
	}

	private function _mockModel(IResource $resource = null, IProjector $projector = null) {
		if (is_null($resource)) $resource = $this->_mockResource();
		if (is_null($projector)) $projector = $this->_mockProjector();

		return $this
			->getMockBuilder(AModel::class)
			->setConstructorArgs([ $resource, $projector])
			->getMockForAbstractClass();
	}


	public function testInheritance() {
		$model = $this->_mockModel();

		$this->assertInstanceOf(\lola\model\IModel::class, $model);
		$this->assertInstanceOf(\lola\common\projection\IFilterProjectable::class, $model);
		$this->assertInstanceOf(\eve\common\projection\IProjectable::class, $model);
	}

	public function testGetProjection() {
		$projection = [
			'foo' => 1,
			'bar' => 2
		];

		$resource = $this->_mockResource();
		$projector = $this->_mockProjector();

		$projector
			->expects($this->any())
			->method('getProjection')
			->with($this->logicalOr(
				$this->isType('array'),
				$this->isNull()
			))
			->willReturn($projection);

		$model = $this->_mockModel($resource, $projector);

		$this->assertEquals($projection, $model->getProjection());
	}
}
