<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\data\TreeAccessException;
use lola\type\data\TreeBranchException;



final class TreeBranchExceptionTest
extends TestCase
{

	private function _produceException($item = null, array $resolved = [], array $missing = []) {
		return new TreeBranchException($item, $resolved, $missing);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(TreeAccessException::class, $ex);
	}


	public function test__construct() {
		$item = 'bang';
		$res = ['foo', 'bar'];
		$miss = ['baz', 'quux'];

		$ex = $this->_produceException($item, $res, $miss);

		$this->assertSame(TreeAccessException::TYPE_NO_BRANCH, $ex->getCode());
		$this->assertSame($item, $ex->useResolvedItem());
		$this->assertSame($res, $ex->getResolvedPath());
		$this->assertSame($miss, $ex->getMissingPath());
	}
}
