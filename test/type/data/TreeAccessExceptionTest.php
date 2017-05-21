<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\data\IAccessException;
use lola\type\data\ITreeAccessException;
use lola\type\data\TreeAccessException;



final class TreeAccessExceptionTest
extends TestCase
{

	private function _produceException($type = TreeAccessException::TYPE_NO_PROP, & $item = null, array $missing = [], array $resolved = []) {
		return new TreeAccessException($type, $item, $missing, $resolved);
	}


	public function testInheritance() {
		$ex = $this->_produceException();

		$this->assertInstanceOf(IAccessException::class, $ex);
		$this->assertInstanceOf(ITreeAccessException::class, $ex);
	}

	public function testGetMessage() {
		$null = null;
		$ex0 = $this->_produceException(TreeAccessException::TYPE_NO_PROP, $null, ['baz', 'quux'], ['foo', 'bar']);

		$this->assertEquals('ACC_NO_PROP:foo.bar!baz.quux', $ex0->getMessage());

		$ex1 = $this->_produceException(TreeAccessException::TYPE_NO_BRANCH, $null, ['baz']);

		$this->assertEquals('ACC_NO_BRANCH:!baz', $ex1->getMessage());
	}

	public function testGetCode() {
		$ex0 = $this->_produceException(TreeAccessException::TYPE_NO_PROP);

		$this->assertEquals(TreeAccessException::TYPE_NO_PROP, $ex0->getCode());

		$ex1 = $this->_produceException(TreeAccessException::TYPE_NO_BRANCH);

		$this->assertEquals(TreeAccessException::TYPE_NO_BRANCH, $ex1->getCode());
	}


	public function testUseResolvedItem() {
		$item = [ 'foo' => 1 ];
		$ex = $this->_produceException(TreeAccessException::TYPE_NO_PROP, $item);

		$resolved =& $ex->useResolvedItem();

		$this->assertSame($item, $resolved);

		$item['bar'] = 2;
		$resolved['baz'] = 3;

		$this->assertSame($item, $resolved);
	}


	public function testGetResolvedKey() {
		$null = null;
		$ex = $this->_produceException(TreeAccessException::TYPE_NO_PROP, $null, [], ['foo', 'bar']);

		$this->assertSame('foo.bar', $ex->getResolvedKey());
	}

	public function testGetResolvedPath() {
		$null = null;
		$path = ['foo', 'bar'];
		$ex = $this->_produceException(TreeAccessException::TYPE_NO_PROP, $null, [], $path);

		$this->assertSame($path, $ex->getResolvedPath());
	}


	public function testGetMissingKey() {
		$null = null;
		$ex = $this->_produceException(TreeAccessException::TYPE_NO_PROP, $null, ['baz', 'quux']);

		$this->assertSame('baz.quux', $ex->getMissingKey());
	}

	public function testGetMissingPath() {
		$null = null;
		$path = ['baz', 'quux'];
		$ex = $this->_produceException(TreeAccessException::TYPE_NO_PROP, $null, $path);

		$this->assertSame($path, $ex->getMissingPath());
	}
}
