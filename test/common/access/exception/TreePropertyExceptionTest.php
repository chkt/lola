<?php

namespace test\common\access\exception;

use PHPUnit\Framework\TestCase;

use lola\common\access\exception\ATreeAccessorException;
use lola\common\access\exception\TreePropertyException;



final class TreePropertyExceptionTest
extends TestCase
{

	private function _produceException(& $item = null, array $resolved = [], array $missing = []) : TreePropertyException {
		return new TreePropertyException($item, $resolved, $missing);
	}

	public function testInheritance() {
		$ins = $this->_produceException();

		$this->assertInstanceOf(ATreeAccessorException::class, $ins);
	}

	public function testGetMessage() {
		$null = null;
		$ins = $this->_produceException($null, ['foo', 'bar'], ['baz', 'quux']);

		$this->assertEquals('ACC no property "foo.bar!baz.quux"', $ins->getMessage());
	}
}
