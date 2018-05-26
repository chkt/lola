<?php

namespace test\common\access\factory;

use PHPUnit\Framework\TestCase;
use eve\common\base\IMethodProxy;
use eve\common\factory\IBaseFactory;
use eve\common\access\IItemAccessor;
use eve\common\access\ITraversableAccessor;
use lola\common\access\IAccessorSelector;
use lola\common\access\AccessorSelector;
use lola\common\access\TraversableAccessor;
use lola\common\access\operator\AItemAccessorSurrogate;
use lola\common\access\factory\TraversableAccessorFactory;



final class TraversableAccessorFactoryTest
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

	private function _mockBaseFactory(IAccessorSelector $selector = null) {
		if (is_null($selector)) $selector = $this->_mockInterface(IAccessorSelector::class);

		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('produce')
			->willReturn($selector);

		return $base;
	}

	private function _produceFactory(IBaseFactory $baseFactory = null) {
		if (is_null($baseFactory)) $baseFactory = $this->_mockBaseFactory();

		return new TraversableAccessorFactory($baseFactory);
	}

	public function testInheritance() {
		$factory = $this->_produceFactory();

		$this->assertInstanceOf(AItemAccessorSurrogate::class, $factory);
	}

	public function testProduce() {
		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('produce')
			->with(
				$this->logicalOr(
					$this->equalTo(AccessorSelector::class),
					$this->equalTo(TraversableAccessor::class)
				),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args = null) {
				if ($qname === AccessorSelector::class) {
					if (!empty($args)) $this->fail($qname);
					else return $this->_mockInterface(IAccessorSelector::class);
				}
				else if ($qname === TraversableAccessor::class) {
					if (empty($args)) $this->fail($qname);
					else return $this->_mockInterface(ITraversableAccessor::class, $args);
				}
				else $this->fail($qname);
			});

		$factory = $this->_produceFactory($base);
		$data = [ 'foo' => 1 ];
		$ins = $factory->produce($data);

		$this->assertInstanceOf(IItemAccessor::class, $ins);
		$this->assertObjectHasAttribute('p0', $ins);
		$this->assertInstanceOf(IMethodProxy::class, $ins->p0);
		$this->assertObjectHasAttribute('p1', $ins);
		$this->assertInstanceOf(IAccessorSelector::class, $ins->p1);
		$this->assertObjectHasAttribute('p2', $ins);
		$this->assertSame($data, $ins->p2);

		$data['foo'] = 2;

		$this->assertSame($data, $ins->p2);
	}
}
