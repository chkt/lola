<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\data\TreeAccessException;
use lola\type\data\TreePropertyException;



final class TreePropertyExceptionTest
extends TestCase
{

	private function _produceException(array $item = [], array $resolved = [], array $missing = []) : TreePropertyException {
		return new TreePropertyException($item, $resolved, $missing);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(TreeAccessException::class, $ex);
	}


	public function test__construct() {
		$item = ['foo'];
		$res = ['foo', 'bar'];
		$miss = ['baz','quux'];

		$ex = $this->_produceException($item, $res, $miss);

		$this->assertSame(TreeAccessException::TYPE_NO_PROP, $ex->getCode());
		$this->assertSame($item, $ex->useResolvedItem());
		$this->assertSame($res, $ex->getResolvedPath());
		$this->assertSame($miss, $ex->getMissingPath());
	}
}