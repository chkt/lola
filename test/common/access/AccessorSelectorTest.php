<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;
use lola\common\access\AccessorSelector;



final class AccessorSelectorTest
extends TestCase
{

	private function _produceSelector() {
		return new AccessorSelector();
	}


	public function testIsResolved() {
		$selector = $this->_produceSelector();
		$data = [
			'foo' => [ 'foo' => 1 ],
			'bar' => [],
			'baz' => 1
		];

		$this->assertFalse($selector->isResolved());
		$this->assertTrue($selector->select($data, 'foo.foo')->isResolved());
		$this->assertFalse($selector->select($data, 'bar.foo')->isResolved());
		$this->assertFalse($selector->select($data, 'baz.foo')->isResolved());
		$this->assertFalse($selector->select($data, 'quux')->isResolved());
	}

	public function testHasAccessFailure() {
		$selector = $this->_produceSelector();
		$data = [
			'foo' => [ 'foo' => 1 ],
			'bar' => [],
			'baz' => 1
		];

		$this->assertFalse($selector->hasAccessFailure());
		$this->assertFalse($selector->select($data, 'foo.foo')->hasAccessFailure());
		$this->assertTrue($selector->select($data, 'bar.foo')->hasAccessFailure());
		$this->assertFalse($selector->select($data, 'baz.foo')->hasAccessFailure());
		$this->assertTrue($selector->select($data, 'quux')->hasAccessFailure());
	}

	public function testHasBranchFailure() {
		$selector = $this->_produceSelector();
		$data = [
			'foo' => [ 'foo' => 1 ],
			'bar' => [],
			'baz' => 1
		];

		$this->assertFalse($selector->hasBranchFailure());
		$this->assertFalse($selector->select($data, 'foo.foo')->hasBranchFailure());
		$this->assertFalse($selector->select($data, 'bar.foo')->hasBranchFailure());
		$this->assertTrue($selector->select($data, 'baz.foo')->hasBranchFailure());
		$this->assertFalse($selector->select($data, 'quux')->hasBranchFailure());
	}


	public function testGetPath() {
		$selector = $this->_produceSelector();
		$data = [];

		$this->assertEquals('', $selector->getPath());
		$this->assertEquals('foo.bar.baz', $selector->select($data, 'foo.bar.baz')->getPath());
		$this->assertEquals('foo.bar.baz', $selector->select($data, 'foo.bar.baz')->getPath(0));
		$this->assertEquals('foo.bar.baz', $selector->select($data, 'foo.bar.baz')->getPath(0, 3));
		$this->assertEquals('foo.bar', $selector->select($data, 'foo.bar.baz')->getPath(0, 2));
		$this->assertEquals('bar.baz', $selector->select($data, 'foo.bar.baz')->getPath(1));
		$this->assertEquals('bar.baz', $selector->select($data, 'foo.bar.baz')->getPath(1, 3));
		$this->assertEquals('', $selector->select($data, 'foo.bar.baz')->getPath(0, 0));
	}

	public function testGetPath_negativeFirst() {
		$data = [];

		$this->expectException(\ErrorException::class);

		$this
			->_produceSelector()
			->select($data, 'foo.bar.baz')
			->getPath(-1);
	}

	public function testGetPath_negativeLast() {
		$data = [];

		$this->expectException(\ErrorException::class);

		$this
			->_produceSelector()
			->select($data, 'foo.bar.baz')
			->getPath(0, -1);
	}

	public function testGetPath_overflowFirst() {
		$data = [];

		$this->expectException(\ErrorException::class);

		$this
			->_produceSelector()
			->select($data, 'foo.bar.baz')
			->getPath(4);
	}

	public function testGetPath_overflowLast() {
		$data = [];

		$this->expectException(\ErrorException::class);

		$this
			->_produceSelector()
			->select($data, 'foo.bar.baz')
			->getPath(0, 4);
	}

	public function testGetPathLength() {
		$selector = $this->_produceSelector();
		$data = [ 'foo' => [ 'foo' => 1 ]];

		$this->assertEquals(0, $selector->getPathLength());
		$this->assertEquals(1, $selector->select($data, 'foo')->getPathLength());
		$this->assertEquals(2, $selector->select($data, 'foo.foo')->getPathLength());
		$this->assertEquals(3, $selector->select($data, 'foo.foo.foo')->getPathLength());
	}

	public function testGetResolvedLength() {
		$selector = $this->_produceSelector();
		$data = [
			'foo' => [ 'foo' => 1 ],
			'bar' => [],
			'baz' => 1
		];

		$this->assertEquals(0, $selector->getResolvedLength());
		$this->assertEquals(2, $selector->select($data, 'foo.foo')->getResolvedLength());
		$this->assertEquals(1, $selector->select($data, 'bar.foo')->getResolvedLength());
		$this->assertEquals(1, $selector->select($data, 'baz.foo')->getResolvedLength());
		$this->assertEquals(0, $selector->select($data, 'quux')->getResolvedLength());
	}


	public function testGetResolvedItem() {
		$selector = $this->_produceSelector();
		$baz = [];
		$foo = [ 'bar' => 1, 'baz' => $baz ];
		$data = [ 'foo' => $foo];

		$this->assertSame($foo, $selector->select($data, 'foo')->getResolvedItem());
		$this->assertEquals(1, $selector->select($data, 'foo.bar')->getResolvedItem());
		$this->assertSame(1, $selector->select($data, 'foo.bar.baz')->getResolvedItem());
		$this->assertSame($baz, $selector->select($data, 'foo.baz.bar')->getResolvedItem());
	}

	public function testGetResolvedItem_unused() {
		$selector = $this->_produceSelector();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC no selection');

		$selector->getResolvedItem();
	}

	public function testSetResolvedItem() {
		$selector = $this->_produceSelector();
		$data = [ 'foo'=> 1];
		$children = [ 'bar' => 1, 'baz' => [] ];

		$this->assertSame($selector, $selector->select($data, 'foo')->setResolvedItem($children));
		$this->assertArrayHasKey('foo', $data);
		$this->assertEquals($children, $data['foo']);
		$this->assertSame($selector, $selector->select($data, 'foo.baz.bar')->setResolveditem(2));
		$this->assertArrayHasKey('foo', $data);
		$this->assertInternalType('array', $data['foo']);
		$this->assertArrayHasKey('bar', $data['foo']);
		$this->assertEquals(2, $data['foo']['baz']);
		$this->assertSame($selector, $selector->select($data, 'foo.bar.baz')->setResolvedItem(3));
		$this->assertArrayHasKey('foo', $data);
		$this->assertInternalType('array', $data['foo']);
		$this->assertArrayHasKey('bar', $data['foo']);
		$this->assertEquals(3, $data['foo']['bar']);
	}

	public function testSetResolvedItem_unused() {
		$selector = $this->_produceSelector();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC no selection');

		$selector->setResolvedItem('foo');
	}

	public function testSelect() {
		$selector = $this->_produceSelector();
		$data = [];

		$this->assertSame($selector, $selector->select($data, 'foo'));
	}

	public function testSelect_noKey() {
		$selector = $this->_produceSelector();
		$data = [];

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC degenerate key ""');

		$selector->select($data, '');
	}

	public function testSelect_badKey() {
		$selector = $this->_produceSelector();
		$data = [];

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC degenerate key "foo..baz"');

		$selector->select($data, 'foo..baz');
	}

	public function testSelect_notBadKey() {
		$selector = $this->_produceSelector();
		$data = [
			'null' => 0,
			'false' => 1,
			'0' => 2,
			'NAN' => 3,
			' ' => 4
		];

		$this->assertEquals(0, $selector->select($data, 'null')->getResolvedItem());
		$this->assertEquals(1, $selector->select($data, 'false')->getResolvedItem());
		$this->assertEquals(2, $selector->select($data, '0')->getResolvedItem());
		$this->assertEquals(3, $selector->select($data, 'NAN')->getResolvedItem());
		$this->assertEquals(4, $selector->select($data, ' ')->getResolvedItem());
	}


	public function testLinkTo() {
		$data = [ 'foo' => 1 ];
		$select = $this
			->_produceSelector()
			->select($data, 'foo.bar.baz.quux');

		$this->assertSame($select, $select->linkTo(1));
		$this->assertEquals(1, $select->getResolvedLength());
		$this->assertArrayHasKey('foo', $data);
		$this->assertInternalType('array', $data['foo']);
		$this->assertEmpty($data['foo']);

		$this->assertSame($select, $select->linkTo(3));
		$this->assertEquals(3, $select->getResolvedLength());
		$this->assertArrayHasKey('bar', $data['foo']);
		$this->assertInternalType('array', $data['foo']['bar']);
		$this->assertArrayHasKey('baz', $data['foo']['bar']);
		$this->assertInternalType('array', $data['foo']['bar']['baz']);
		$this->assertEmpty($data['foo']['bar']['baz']);

		$this->assertSame($select, $select->linkTo(4));
		$this->assertEquals(4, $select->getResolvedLength());
		$this->assertArrayHasKey('quux', $data['foo']['bar']['baz']);
		$this->assertNull($data['foo']['bar']['baz']['quux']);
	}

	public function testLinkTo_badIndex() {
		$data = [];
		$selector = $this
			->_produceSelector()
			->select($data, 'foo.bar');

		$this->expectException(\ErrorException::class);

		$selector->linkTo(3);
	}

	public function testLinkAll() {
		$data = [];
		$selector = $this
			->_produceSelector()
			->select($data, 'foo.bar.baz');

		$this->assertSame($selector, $selector->linkAll());
		$this->assertEquals(3, $selector->getResolvedLength());
		$this->assertArrayHasKey('foo', $data);
		$this->assertInternalType('array', $data['foo']);
		$this->assertArrayHasKey('bar', $data['foo']);
		$this->assertInternalType('array', $data['foo']['bar']);
		$this->assertArrayHasKey('baz', $data['foo']['bar']);
		$this->assertNull($data['foo']['bar']['baz']);
	}

	public function testUnlinkAt() {
		$data = [ 'foo' => [ 'bar' => [ 'baz' => 1 ]]];
		$selector = $this
			->_produceSelector()
			->select($data, 'foo.bar.baz');

		$this->assertSame($selector, $selector->unlinkAt(2));
		$this->assertEquals(2, $selector->getResolvedLength());
		$this->assertArrayNotHasKey('baz', $data['foo']['bar']);

		$this->assertSame($selector, $selector->unlinkAt(1));
		$this->assertEquals(1, $selector->getResolvedLength());
		$this->assertArrayNotHasKey('bar', $data['foo']);

		$this->assertSame($selector, $selector->unlinkAt(0));
		$this->assertEquals(0, $selector->getResolvedLength());
		$this->assertArrayNotHasKey('foo', $data);
	}

	public function testUnlinkAt_badIndex() {
		$data = [];
		$selector = $this
			->_produceSelector()
			->select($data, 'foo.bar');

		$this->expectException(\ErrorException::class);

		$selector->unlinkAt(3);
	}

	public function testUnlinkRecursive() {
		$data = [ 'foo' => [
			'bar' => [ 'baz' => 1 ],
			'baz' => 2
		]];
		$selector = $this
			->_produceSelector()
			->select($data, 'foo.bar.baz');

		$this->assertSame($selector, $selector->unlinkRecursive());
		$this->assertEquals(1, $selector->getResolvedLength());
		$this->assertEquals([ 'foo' => [ 'baz' => 2]], $data);
	}
}
