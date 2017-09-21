<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;

use lola\common\access\ATreeAccessorException;
use lola\common\access\TreeBranchException;



final class TreeBranchExceptionTest
extends TestCase
{

	private function _produceException(& $item = null, $resolved = [], $missing = []) {
		return new TreeBranchException($item, $resolved, $missing);
	}


	public function testInheritance() {
		$ins = $this->_produceException();

		$this->assertInstanceOf(ATreeAccessorException::class, $ins);
	}


	public function testGetMessage() {
		$null = null;
		$ins = $this->_produceException($null, ['foo', 'bar'], ['baz', 'quux']);

		$this->assertEquals('ACC no branch "foo.bar!baz.quux"', $ins->getMessage());
	}
}
