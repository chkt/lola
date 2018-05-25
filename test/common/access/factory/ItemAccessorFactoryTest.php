<?php

namespace test\common\access\factory;

use PHPUnit\Framework\TestCase;
use eve\common\factory\IBaseFactory;
use eve\common\access\IItemAccessor;
use lola\common\access\IAccessorSelector;
use lola\common\access\AccessorSelector;
use lola\common\access\ItemAccessor;
use lola\common\access\operator\AItemAccessorSurrogate;
use lola\common\access\factory\ItemAccessorFactory;



final class ItemAccessorFactoryTest
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

	private function _produceFactory(IBaseFactory $baseFactory = null) {
		if (is_null($baseFactory)) $baseFactory = $this->_mockInterface(IBaseFactory::class);

		return new ItemAccessorFactory($baseFactory);
	}


	public function testInheritance() {
		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('newInstance')
			->willReturnCallback(function() {
				return $this->_mockInterface(IAccessorSelector::class);
			});

		$factory = $this->_produceFactory($base);

		$this->assertInstanceOf(AItemAccessorSurrogate::class, $factory);
	}

	public function testProduce() {
		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('newInstance')
			->with(
				$this->logicalOr(
					$this->equalTo(AccessorSelector::class),
					$this->equalTo(ItemAccessor::class)
				),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args = null) {
				if ($qname === AccessorSelector::class) {
					if (!empty($args)) $this->fail($qname);
					else return $this->_mockInterface(IAccessorSelector::class);
				}
				else if ($qname === ItemAccessor::class) {
					if (empty($args)) $this->fail($qname);
					else return $this->_mockInterface(IItemAccessor::class, $args);
				}
				else $this->fail($qname);
			});

		$factory = $this->_produceFactory($base);
		$data = [ 'foo' => 1 ];
		$ins = $factory->produce($data);

		$this->assertInstanceOf(IItemAccessor::class, $ins);
		$this->assertObjectHasAttribute('p0', $ins);
		$this->assertInstanceOf(IAccessorSelector::class, $ins->p0);
		$this->assertObjectHasAttribute('p1', $ins);
		$this->assertSame($data, $ins->p1);

		$data['foo'] = 2;

		$this->assertSame($data, $ins->p1);
	}
}
