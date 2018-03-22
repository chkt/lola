<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use eve\common\access\IKeyAccessor;
use eve\common\access\IItemAccessor;
use lola\common\access\TreeAccessor;
use lola\common\access\exception\TreePropertyException;
use lola\common\access\exception\TreeBranchException;



final class TreeAccessorTest
extends TestCase
{

	private function _mockAccessor(array& $data = null) : TreeAccessor {
		if (is_null($data)) $data = $this->_produceSampleData();

		$ins = $this
			->getMockBuilder(TreeAccessor::class)
			->setConstructorArgs([& $data])
			->setMethods(['_handleBranchException', '_handlePropertyException'])
			->getMock();

		return $ins;
	}


	private function _produceSampleData() : array {
		return [
			'foo' => 1,
			'bar' => [
				'foo' => 2
			]
		];
	}

	private function _produceAccessor(array& $data = null) : TreeAccessor {
		if (is_null($data)) $data = $this->_produceSampleData();

		return new TreeAccessor($data);
	}


	public function testInheritance() {
		$ins = $this->_produceAccessor();

		$this->assertInstanceOf(IKeyAccessor::class, $ins);
		$this->assertInstanceOf(IItemAccessor::class, $ins);
	}


	public function testHasKey() {
		$ins = $this->_produceAccessor();

		$this->assertTrue($ins->hasKey('foo'));
		$this->assertFalse($ins->hasKey('foo.bar'));
		$this->assertTrue($ins->hasKey('bar'));
		$this->assertTrue($ins->hasKey('bar.foo'));
		$this->assertFalse($ins->hasKey('bar.bar'));
		$this->assertFalse($ins->hasKey('baz'));
	}

	public function testGetItem() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceAccessor($data);

		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar.foo'));

		$data['bar']['bar'] = 3;

		$this->assertEquals(3, $ins->getItem('bar.bar'));
	}

	public function testGetItem_noKey() {
		$ins = $this->_produceAccessor();

		$this->expectException(TreePropertyException::class);
		$this->expectExceptionMessage('ACC no property "bar!bar"');

		$ins->getItem('bar.bar');
	}

	public function testGetItem_handleNoKey() {
		$ins = $this->_mockAccessor();
		$ins
			->expects($this->never())
			->method('_handleBranchException');

		$ins
			->expects($this->once())
			->method('_handlePropertyException')
			->with($this->isInstanceOf(TreePropertyException::class))
			->willReturnCallback(function(TreePropertyException $ex) {
				$this->assertEquals('bar', $ex->getResolvedKeySegment());
				$this->assertEquals('bar.foo', $ex->getMissingKeySegment());
				$this->assertEquals([ 'foo' => 2 ], $ex->useResolvedItem());

				$item =& $ex->useResolvedItem();
				$item['bar'] = [ 'foo' => 3 ];

				return true;
			});

		$this->assertEquals(3, $ins->getItem('bar.bar.foo'));
		$this->assertEquals(3, $ins->getItem('bar.bar.foo'));
	}

	public function testGetItem_noBranch() {
		$ins = $this->_produceAccessor();

		$this->expectException(TreeBranchException::class);

		$ins->getItem('bar.foo.baz');
	}

	public function testGetItem_handleNoBranch() {
		$ins = $this->_mockAccessor();
		$ins
			->expects($this->once())
			->method('_handleBranchException')
			->with($this->isInstanceOf(TreeBranchException::class))
			->willReturnCallback(function(TreeBranchException $ex) {
				$this->assertEquals('bar.foo', $ex->getResolvedKeySegment());
				$this->assertEquals('foo', $ex->getMissingKeySegment());
				$this->assertEquals(2, $ex->useResolvedItem());

				$item =& $ex->useResolvedItem();
				$item = [ 'foo' => 3 ];

				return true;
			});
		$ins
			->expects($this->never())
			->method('_handlePropertyException');

		$this->assertEquals(3, $ins->getItem('bar.foo.foo'));
		$this->assertEquals(3, $ins->getItem('bar.foo.foo'));
	}

	public function testGetItem_handleBoth() {
		$ins = $this->_mockAccessor();
		$ins
			->expects($this->once())
			->method('_handleBranchException')
			->with($this->isInstanceOf(TreeBranchException::class))
			->willReturnCallback(function(TreeBranchException $ex) {
				$this->assertEquals('bar.foo', $ex->getResolvedKeySegment());
				$this->assertEquals('foo', $ex->getMissingKeySegment());

				$item =& $ex->useResolvedItem();
				$item = [];

				return true;
			});
		$ins
			->expects($this->once())
			->method('_handlePropertyException')
			->with($this->isInstanceOf(TreePropertyException::class))
			->willReturnCallback(function(TreePropertyException $ex) {
				$this->assertEquals('bar.foo', $ex->getResolvedKeySegment());
				$this->assertEquals('foo', $ex->getMissingKeySegment());

				$item =& $ex->useResolvedItem();
				$item['foo'] = 3;

				return true;
			});

		$this->assertEquals(3, $ins->getItem('bar.foo.foo'));
		$this->assertEquals(3, $ins->getItem('bar.foo.foo'));
	}
}
