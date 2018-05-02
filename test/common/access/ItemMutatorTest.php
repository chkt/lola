<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;
use eve\common\base\IMethodProxy;
use lola\common\access\IAccessorSelector;
use lola\common\access\ItemMutator;



final class ItemMutatorTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		return $this
			->getMockBuilder($qname)
			->getMock();
	}

	private function _mockSelector() {
		return $this->_mockInterface(IAccessorSelector::class);
	}

	private function _mockMethodProxy() {
		return $this->_mockInterface(IMethodProxy::class);
	}


	private function _mockProjectable(array $data) {
		$ins = $this->_mockInterface(\eve\common\projection\IProjectable::class);

		$ins
			->method('getProjection')
			->willReturn($data);

		return $ins;
	}

	private function _mockFilterProjectable(array $data) {
		$ins = $this->_mockInterface(\lola\common\projection\IFilterProjectable::class);

		$ins
			->method('getProjection')
			->with($this->logicalOr(
				$this->isType('array'),
				$this->isNull()
			))
			->willReturnCallback(function(array $filter = null) use ($data) {
				if (is_null($filter)) $filter = array_keys($data);

				$res = [];

				foreach ($filter as $key) {
					if (array_key_exists($key, $data)) $res[$key] = $data[$key];
				}

				return $res;
			});

		return $ins;
	}


	private function _produceAccessor(
		array& $data = [],
		IMethodProxy $proxy = null,
		IAccessorSelector $selector = null
	) {
		if (is_null($proxy)) $proxy = $this->_mockMethodProxy();
		if (is_null($selector)) $selector = $this->_mockSelector();

		return new ItemMutator($proxy, $selector, $data);
	}


	public function testInheritance() {
		$access = $this->_produceAccessor();

		$this->assertInstanceOf(\lola\common\access\TraversableAccessor::class, $access);
		$this->assertInstanceOf(\eve\common\access\IItemMutator::class, $access);
	}

	public function testRemoveKey() {
		$data = [ 'foo' => 1, 'bar' => 2 ];
		$selector = $this->_mockSelector();

		$selector
			->expects($this->at(0))
			->method('select')
			->with(
				$this->equalTo($data),
				$this->equalTo('foo')
			)
			->willReturn($selector);

		$selector
			->expects($this->at(1))
			->method('unlinkRecursive')
			->willReturnCallback(function() use (& $data, $selector) {
				unset($data['foo']);

				return $selector;
			});

		$access = $this->_produceAccessor($data, null, $selector);

		$this->assertSame($access, $access->removeKey('foo'));
		$this->assertEquals([ 'bar' => 2], $data);
	}

	public function testSetItem() {
		$data = [];
		$selector = $this->_mockSelector();

		$selector
			->expects($this->at(0))
			->method('select')
			->with(
				$this->equalTo($data),
				$this->equalTo('foo')
			)
			->willReturn($selector);

		$selector
			->expects($this->at(1))
			->method('linkAll')
			->willReturn($selector);

		$selector
			->expects($this->at(2))
			->method('setResolvedItem')
			->with($this->equalTo(1))
			->willReturnCallback(function() use (& $data, $selector) {
				$data['foo'] = 1;

				return $selector;
			});

		$access = $this->_produceAccessor($data, null, $selector);

		$this->assertSame($access, $access->setItem('foo', 1));
		$this->assertSame([ 'foo' => 1 ], $data);
	}

	public function testCopy() {
		$accessData = [ 'bar' => 2 ];
		$access = $this->_produceAccessor($accessData);

		$projectableData = [ 'foo' => 1 ];
		$projectable = $this->_mockProjectable($projectableData);

		$this->assertSame($access, $access->copy($projectable));
		$this->assertSame($projectableData, $access->getProjection());
		$this->assertSame($projectableData, $accessData);
	}

	public function testMerge() {
		$data = [];
		$proxy = $this->_mockMethodProxy();

		$proxy
			->method('callMethod')
			->with(
				$this->equalTo(\lola\common\base\ArrayOperation::class),
				$this->equalTo('merge'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, string $method, array $args) {
				$this->assertCount(2, $args);
				$this->assertInternalType('array', $args[0]);
				$this->assertInternalType('array', $args[1]);

				return array_merge($args[0], $args[1]);
			});

		$access = $this->_produceAccessor($data, $proxy);
		$a = $this->_mockProjectable([ 'foo' => 1 ]);
		$b = $this->_mockProjectable([ 'bar' => 2 ]);

		$this->assertSame($access, $access->merge($a, $b));
		$this->assertSame([ 'foo' => 1, 'bar' => 2 ], $access->getProjection());
		$this->assertSame([ 'foo' => 1, 'bar' => 2 ], $data);
	}

	public function testMergeAssign() {
		$data = [ 'foo' => 1 ];
		$proxy = $this->_mockMethodProxy();

		$proxy
			->method('callMethod')
			->with(
				$this->equalTo(\lola\common\base\ArrayOperation::class),
				$this->equalTo('merge'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, string $method, array $args) {
				$this->assertCount(2, $args);
				$this->assertInternalType('array', $args[0]);
				$this->assertInternalType('array', $args[1]);

				return array_merge($args[0], $args[1]);
			});

		$access = $this->_produceAccessor($data, $proxy);
		$b = $this->_mockProjectable([ 'bar' => 2]);

		$this->assertSame($access, $access->mergeAssign($b));
		$this->assertSame([ 'foo' => 1, 'bar' => 2], $access->getProjection());
		$this->assertSame($data, $access->getProjection());
	}

	public function testFilter() {
		$sourceData = [ 'foo' => 1, 'bar' => 2 ];
		$targetData = [];

		$source = $this->_mockFilterProjectable($sourceData);
		$target = $this->_produceAccessor($targetData);

		$this->assertSame($target, $target->filter($source, [ 'foo' ]));
		$this->assertEquals([ 'foo' => 1 ], $target->getProjection());
		$this->assertEquals([ 'foo' => 1 ], $targetData);
	}

	public function testFilterSelf() {
		$data = [ 'foo' => 1, 'bar' => 2];

		$selectorData = new \stdClass();
		$select = $this->_mockSelector();

		$select
			->method('select')
			->willReturnCallback(function(array& $data, $key) use ($selectorData, $select) {
				$selectorData->data =& $data;
				$selectorData->key = $key;

				return $select;
			});

		$select
			->method('isResolved')
			->willReturn(true);

		$select
			->method('linkAll')
			->willReturnSelf();

		$select
			->method('getResolvedItem')
			->willReturn(1);

		$select
			->method('setResolvedItem')
			->willReturnCallback(function($value) use ($selectorData, $select) {
				$selectorData->data[$selectorData->key] = $value;

				return $select;
			});

		$access = $this->_produceAccessor($data, null, $select);

		$this->assertSame($access, $access->filterSelf([ 'foo' ]));
		$this->assertEquals([ 'foo' => 1 ], $access->getProjection());
		$this->assertEquals([ 'foo' => 1 ], $data);
	}

	public function testInsert() {
		$data = [];

		$selector = $this->_mockSelector();

		$selector
			->method('select')
			->with(
				$this->isType('array'),
				$this->equalto('bar')
			)
			->willReturnSelf();

		$selector
			->method('linkAll')
			->willReturnSelf();

		$selector
			->method('setResolvedItem')
			->with($this->isType('array'))
			->willReturnCallback(function($value) use (& $data, $selector) {
				$data['bar'] = $value;

				return $selector;
			});

		$access = $this->_produceAccessor($data, null, $selector);

		$targetData = [ 'foo' => 1 ];
		$target = $this->_mockProjectable($targetData);

		$sourceData = [ 'baz' => 2 ];
		$source = $this->_produceAccessor($sourceData);

		$res = [ 'foo' => 1, 'bar' => [ 'baz' => 2 ]];

		$this->assertSame($access, $access->insert($target, $source, 'bar'));
		$this->assertEquals($res, $access->getProjection());
		$this->assertEquals($res, $data);
	}

	public function testInsertAssign() {
		$data = [ 'foo' => 1];
		$selector = $this->_mockSelector();

		$selector
			->method('select')
			->with(
				$this->isType('array'),
				$this->equalto('bar')
			)
			->willReturnSelf();

		$selector
			->method('linkAll')
			->willReturnSelf();

		$selector
			->method('setResolvedItem')
			->willReturnCallback(function($value) use (& $data, $selector) {
				$data['bar'] = $value;

				return $selector;
			});

		$access = $this->_produceAccessor($data, null, $selector);

		$sourceData = [ 'baz' => 2];
		$source = $this->_mockProjectable($sourceData);

		$res = [ 'foo' => 1, 'bar' => [ 'baz' => 2 ]];

		$this->assertSame($access, $access->insertAssign($source, 'bar'));
		$this->assertEquals($res, $access->getProjection());
		$this->assertEquals($res, $data);
	}

	public function testSelect() {
		$data = [];
		$access = $this->_produceAccessor($data);

		$sourceData = [ 'foo' => [ 'bar' => 1 ]];
		$source = $this->_mockInterface(\eve\common\access\IItemAccessor::class);

		$source
			->method('getItem')
			->with($this->equalTo('foo'))
			->willReturn($sourceData['foo']);

		$res = [ 'bar' => 1 ];

		$this->assertSame($access, $access->select($source, 'foo'));
		$this->assertSame($res, $access->getProjection());
		$this->assertSame($res, $data);
	}

	public function testSelect_scalar() {
		$access = $this->_produceAccessor();

		$source = $this->_mockInterface(\eve\common\access\IItemAccessor::class);

		$source
			->method('getItem')
			->with($this->equalTo('foo'))
			->willReturn(1);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('invalid accessor root "foo"');

		$access->select($source, 'foo');
	}

	public function testSelectSelf() {
		$data = [ 'foo' => [ 'bar' => 1 ]];
		$selector = $this->_mockSelector();

		$selector
			->method('select')
			->with(
				$this->isType('array'),
				$this->equalTo('foo')
			)
			->willReturnSelf();

		$selector
			->method('isResolved')
			->willReturn(true);

		$selector
			->method('getResolvedItem')
			->willReturn($data['foo']);

		$access = $this->_produceAccessor($data, null, $selector);

		$res = [ 'bar' => 1 ];

		$this->assertSame($access, $access->selectSelf('foo'));
		$this->assertEquals($res, $access->getProjection());
		$this->assertEquals($res, $data);
	}
}
