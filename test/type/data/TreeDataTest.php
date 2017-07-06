<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\IProjectable;
use lola\type\data\IKeyMutator;
use lola\type\data\IItemAccessor;
use lola\type\data\ITreeAccessor;
use lola\type\data\TreeData;
use lola\type\data\TreeBranchException;
use lola\type\data\TreePropertyException;



final class TreeDataTest
extends TestCase
{

	private function _produceSampleData() : array {
		return [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
	}

	private function _mockChildClass(array& $data = null) {
		if (is_null($data)) $data = $this->_produceSampleData();

		$ins = $this
			->getMockBuilder(TreeData::class)
			->setConstructorArgs([ & $data ])
			->setMockClassName('TreeDataChildMock')
			->setMethods(['_produceInstance'])
			->getMock();

		$ins
			->expects($this->any())
			->method('_produceInstance')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return $this->_mockChildClass($data);
			});

		return $ins;
	}


	private function _produceData(array& $data = null) : TreeData {
		if (is_null($data)) $data = $this->_produceSampleData();

		return new TreeData($data);
	}


	public function testInheritance() {
		$data = new TreeData();

		$this->assertInstanceOf(IProjectable::class, $data);
		$this->assertInstanceOf(IKeyMutator::class, $data);
		$this->assertInstanceOf(IItemAccessor::class, $data);
		$this->assertInstanceOf(ITreeAccessor::class, $data);
	}


	public function testIsBranch() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = new TreeData($items);

		$this->assertFalse($data->isBranch('foo'));
		$this->assertTrue($data->isBranch('bar'));
		$this->assertFalse($data->isBranch('bar.foo'));
		$this->assertFalse($data->isBranch('baz'));
	}

	public function testIsLeaf() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = new TreeData($items);

		$this->assertTrue($data->isLeaf('foo'));
		$this->assertFalse($data->isLeaf('bar'));
		$this->assertTrue($data->isLeaf('bar.foo'));
		$this->assertFalse($data->isLeaf('baz'));
	}


	public function testHasKey() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = new TreeData($items);

		$this->assertTrue($data->hasKey('foo'));
		$this->assertFalse($data->hasKey('foo.bar'));
		$this->assertTrue($data->hasKey('bar'));
		$this->assertTrue($data->hasKey('bar.foo'));
		$this->assertFalse($data->hasKey('bar.bar'));
		$this->assertFalse($data->hasKey('baz'));
	}

	public function testRemoveKey() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = new TreeData($items);

		$this->assertEquals($data->removeKey('foo'), $data);
		$this->assertFalse($data->hasKey('foo'));
		$this->assertEquals($data->removeKey('foo.bar'), $data);
		$this->assertEquals($data->removeKey('bar.foo'), $data);
		$this->assertFalse($data->hasKey('bar.foo'));
		$this->assertTrue($data->hasKey('bar'));
		$this->assertEquals($data->removeKey('baz'), $data);
	}


	public function testGetBranch() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = new TreeData($items);

		$b = $data->getBranch('bar');
		$this->assertInstanceOf(TreeData::class, $b);
		$this->assertTrue($b->hasKey('foo'));
		$this->assertTrue($b->isLeaf('foo'));
		$this->assertEquals(2, $b->useItem('foo'));
	}

	public function testGetBranch_inheritance() {
		$data = $this->_mockChildClass();

		$b = $data->getBranch('bar');
		$this->assertInstanceOf(get_class($data), $b);
	}

	public function testGetBranch_noKey() {
		$data = $this->_produceData();

		$this->expectException(TreePropertyException::class);

		$data->getBranch('baz');
	}

	public function testGetBranch_noBranch() {
		$data = $this->_produceData();

		$this->expectException(TreeBranchException::class);

		$data->getBranch('foo');
	}


	public function testSetBranch() {
		$items = [
			'bar' => 2
		];
		$data = new TreeData($items);

		$bItems = [
			'foo' => 1
		];
		$b = new TreeData($bItems);

		$this->assertEquals($data->setBranch('bar', $b), $data);
		$this->assertTrue($data->hasKey('bar.foo'));
		$this->assertTrue($data->isBranch('bar'));
	}


	public function testUseItem() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = new TreeData($items);

		$this->assertEquals(1, $data->useItem('foo'));
		$this->assertEquals(2, $data->useItem('bar.foo'));

		$i =& $data->useItem('bar');
		$i['bar'] = 3;

		$this->assertEquals(3, $data->useItem('bar.bar'));
	}

	public function testUseItem_noKey() {
		$data = $this->_produceData();

		$this->expectException(TreePropertyException::class);

		$data->useItem('bar.bar');
	}

	public function testUseItem_noBranch() {
		$data = $this->_produceData();

		$this->expectException(TreeBranchException::class);

		$data->useItem('bar.foo.baz');
	}

	public function testSetItem() {
		$data = $this->_produceData();

		$this->assertEquals($data->setItem('foo', 3), $data);
		$this->assertEquals($data->useItem('foo'), 3);
		$this->assertEquals($data->setItem('baz', 4), $data);
		$this->assertEquals($data->useItem('baz'), 4);
		$this->assertEquals($data->setItem('bar.foo', 5), $data);
		$this->assertEquals($data->useItem('bar.foo'), 5);
	}


	public function testGetProjection() {
		$items = [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
		$data = $this->_produceData($items);

		$this->assertEquals($data->getProjection(), $items);
	}
}
