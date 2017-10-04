<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use lola\common\access\TreeAccessor;
use lola\common\access\TreeAccessorFactory;



final class TreeAccessorFactoryTest
extends TestCase
{

	private function _mockCoreFactory() : ICoreFactory {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('newInstance')
			->with(
				$this->equalTo(TreeAccessor::class),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $opts = []) {
				return new $qname(...$opts);
			});

		return $ins;
	}

	private function _produceFactory(ICoreFactory $coreFactory = null) : TreeAccessorFactory {
		if (is_null($coreFactory)) $coreFactory = $this->_mockCoreFactory();

		return new TreeAccessorFactory($coreFactory);
	}


	public function testInheritance() {
		$ins = $this->_produceFactory();

		$this->assertInstanceOf(ISimpleFactory::class, $ins);
	}

	public function testProduce() {
		$fab = $this->_produceFactory();

		$data = [
			'foo' => 1,
			'bar' => 2
		];

		$ins = $fab->produce($data);

		$this->assertInstanceOf(TreeAccessor::class, $ins);
		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));

		$data['foo'] = 3;

		$this->assertEquals(3, $ins->getItem('foo'));
	}
}
