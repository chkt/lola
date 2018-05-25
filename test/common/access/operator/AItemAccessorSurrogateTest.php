<?php

namespace test\common\access\operator;

use PHPUnit\Framework\TestCase;
use eve\common\base\IMethodProxy;
use eve\common\projection\IProjectable;
use lola\common\projection\IFilterProjectable;
use lola\common\access\IAccessorSelector;
use lola\common\access\operator\AItemAccessorSurrogate;



final class AItemAccessorSurrogateTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => & $arg) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;
			$ins->$prop =& $arg;
		}

		return $ins;
	}

	private function _mockAccessor(array& $data = []) {
		$ins = $this->_mockInterface(\eve\common\access\IItemAccessor::class, [ & $data ]);

		return $ins;
	}

	private function _mockProjectable(array& $data = []) {
		$ins = $this->_mockInterface(IProjectable::class);

		$ins
			->method('getProjection')
			->willReturn($data);

		return $ins;
	}

	private function _mockProxy() {
		$ins = $this->_mockInterface(IMethodProxy::class);

		return $ins;
	}

	private function _mockSelector() {
		$ins = $this->_mockInterface(IAccessorSelector::class);

		return $ins;
	}

	private function _mockSurrogate(IMethodProxy $proxy = null, IAccessorSelector $selector = null) {
		if (is_null($proxy)) $proxy = $this->_mockProxy();
		if (is_null($selector)) $selector = $this->_mockSelector();

		$ins = $this
			->getMockBuilder(AItemAccessorSurrogate::class)
			->setConstructorArgs([ $proxy, $selector ])
			->getMockForAbstractClass();

		$ins
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return $this->_mockAccessor($data);
			});

		return $ins;
	}


	public function testInheritance() {
		$surrogate = $this->_mockSurrogate();

		$this->assertInstanceOf(\eve\common\access\operator\AItemAccessorSurrogate::class, $surrogate);
		$this->assertInstanceOf(\lola\common\access\operator\IItemAccessorSurrogate::class, $surrogate);
		$this->assertInstanceOf(\lola\common\access\operator\IItemAccessorComposition::class, $surrogate);
		$this->assertInstanceOf(\eve\inject\IInjectableIdentity::class, $surrogate);
		$this->assertInstanceOf(\eve\common\projection\operator\IProjectableSurrogate::class, $surrogate);
		$this->assertInstanceof(\eve\common\factory\ISimpleFactory::class, $surrogate);
	}


	public function testDependencyConfig() {
		$this->assertEquals(
			[ 'core:baseFactory' ],
			AItemAccessorSurrogate::getDependencyConfig($this->_mockInterface(\eve\common\access\ITraversableAccessor::class))
		);
	}

	public function testInstanceIdentity() {
		$this->assertEquals(
			\eve\inject\IInjectableIdentity::IDENTITY_SINGLE,
			AItemAccessorSurrogate::getInstanceIdentity($this->_mockInterface(\eve\common\access\ITraversableAccessor::class))
		);
	}


	public function testCopy() {
		$surrogate = $this->_mockSurrogate();

		$data = [ 'foo' => 1 ];
		$projectable = $this->_mockProjectable($data);

		$copy = $surrogate->copy($projectable);

		$this->assertSame($data, $copy->p0);
	}

	public function testMerge() {
		$a = [ 'foo' => 1 ];
		$b = [ 'bar' => 2 ];
		$c = [ 'baz' => 3 ];

		$proxy = $this->_mockProxy();

		$proxy
			->method('callMethod')
			->with(
				$this->equalTo(\lola\common\base\ArrayOperation::class),
				$this->equalTo('merge'),
				$this->logicalAnd(
					$this->isType('array'),
					$this->countOf(2)
				)
			)
			->willReturnCallback(function(string $qname, string $method, array $args) use ($a, $b, $c) {
				$this->assertSame($a, $args[0]);
				$this->assertSame($b, $args[1]);

				return $c;
			});

		$surrogate = $this->_mockSurrogate($proxy);

		$pa = $this->_mockProjectable($a);
		$pb = $this->_mockProjectable($b);

		$pc = $surrogate->merge($pa, $pb);

		$this->assertSame($c, $pc->p0);
	}

	public function testFilter() {
		$surrogate = $this->_mockSurrogate();

		$filter = [ 'foo' ];
		$data = [ 'foo' => 1 ];
		$projectable = $this->_mockInterface(IFilterProjectable::class);

		$projectable
			->method('getProjection')
			->with($this->equalTo($filter))
			->willReturn($data);

		$filtered = $surrogate->filter($projectable, $filter);

		$this->assertSame($data, $filtered->p0);
	}

	public function testInsert() {
		$a = [ 'foo' => 1 ];
		$pa = $this->_mockProjectable($a);

		$b = [ 'bar' => 2 ];
		$pb = $this->_mockProjectable($b);

		$c = ['foo' => [ 'bar' => 2 ]];

		$ref = [];
		$selector = $this->_mockSelector();

		$selector
			->method('select')
			->willReturnCallback(function(array& $data, string $key) use (& $ref, $selector) {
				$ref[0] =& $data[$key];

				return $selector;
			});

		$selector
			->method('linkAll')
			->willReturnSelf();

		$selector
			->method('setResolvedItem')
			->willReturnCallback(function($item) use (& $ref, $selector) {
				$ref[0] = $item;

				return $selector;
			});

		$surrogate = $this->_mockSurrogate(null, $selector);
		$merged = $surrogate->insert($pa, $pb, 'foo');

		$this->assertNotEquals($a, $merged->p0);
		$this->assertNotEquals($b, $merged->p0);
		$this->assertSame($c, $merged->p0);
	}
}
