<?php

namespace test\common\access\exception;

use PHPUnit\Framework\TestCase;
use lola\common\access\IAccessorSelector;
use lola\common\access\exception\AccessorException;



final class AccessorExceptionTest
extends TestCase
{

	private function _mockSelector() {
		$ins = $this
			->getMockBuilder(IAccessorSelector::class)
			->getMock();

		$ins
			->method('getPath')
			->willReturnCallback(function(int $index0 = 0, int $indexN = null) {
				if ($index0 < 0 || $indexN > 4) $this->fail(sprintf('invalid path index %s %s', $index0, $indexN));

				if (is_null($indexN)) $indexN = 3;

				return implode('.', array_slice(['foo', 'bar', 'baz', 'quux'], $index0, $indexN));
			});

		$ins
			->method('getPathLength')
			->willReturn(4);

		return $ins;
	}


	private function _produceException(IAccessorSelector $selector = null) {
		if (is_null($selector)) $selector = $this->_mockSelector();

		return new AccessorException($selector);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(\lola\common\access\exception\IAccessorException::class, $ex);
		$this->assertInstanceOf(\eve\common\access\exception\IAccessorException::class, $ex);
		$this->assertInstanceOf(\Exception::class, $ex);
	}

	public function testGetMessage_access() {
		$selector = $this->_mockSelector();

		$selector
			->method('hasAccessFailure')
			->willReturn(true);

		$selector
			->method('getResolvedLength')
			->willReturn(0);

		$ex = $this->_produceException($selector);

		$this->assertEquals('ACC no property ""["foo"]"bar.baz.quux"', $ex->getMessage());
	}

	public function testGetMessage_branch() {
		$selector = $this->_mockSelector();

		$selector
			->method('hasBranchFailure')
			->willReturn(true);

		$selector
			->method('getResolvedLength')
			->willReturn(3);

		$ex = $this->_produceException($selector);

		$this->assertEquals('ACC no branch "foo.bar.baz"["quux"]""', $ex->getMessage());
	}

	public function testGetMessage_other() {
		$selector = $this->_mockSelector();

		$selector
			->method('getResolvedLength')
			->willReturn(4);

		$ex = $this->_produceException($selector);

		$this->assertEquals('ACC unidentified error "foo.bar.baz.quux"', $ex->getMessage());
	}

	public function testGetKey() {
		$selector = $this->_mockSelector();

		$ex = $this->_produceException($selector);

		$this->assertEquals('foo.bar.baz', $ex->getKey());
	}

	public function testGetSelector() {
		$selector = $this->_mockSelector();
		$ex = $this->_produceException($selector);

		$this->assertSame($selector, $ex->getSelector());
	}
}
