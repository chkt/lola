<?php

namespace test\common\access\exception;

use PHPUnit\Framework\TestCase;

use eve\common\access\exception\IAccessorException;
use lola\common\access\exception\ITreeAccessorException;
use lola\common\access\exception\ATreeAccessorException;



final class ATreeAccessorExceptionTest
extends TestCase
{

	private function _mockException(& $item = null, array $resolved = [], array $missing = []) {
		$ins = $this
			->getMockBuilder(ATreeAccessorException::class)
			->setConstructorArgs([ & $item, $resolved, $missing ])
			->getMockForAbstractClass();

		return $ins;
	}


	public function testInheritance() {
		$ex = $this->_mockException();

		$this->assertInstanceOf(IAccessorException::class, $ex);
		$this->assertInstanceOf(ITreeAccessorException::class, $ex);
	}


	public function testGetMessage() {
		$null = null;
		$ins = $this
			->getMockBuilder(ATreeAccessorException::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$ins
			->expects($this->once())
			->method('_produceMessage')
			->with()
			->willReturn('%s-%s');

		$ins->__construct($null, ['foo.bar'], ['baz.quux']);

		$this->assertEquals('foo.bar-baz.quux', $ins->getMessage());
	}


	public function testUseResolvedItem() {
		$item = [ 'foo' => 1 ];
		$ex = $this->_mockException($item);

		$resolved =& $ex->useResolvedItem();

		$this->assertSame($item, $resolved);

		$item['bar'] = 2;
		$resolved['baz'] = 3;

		$this->assertSame($item, $resolved);
	}

	public function testGetKey() {
		$null = null;
		$ex = $this->_mockException($null, ['foo', 'bar'], ['baz', 'quux']);

		$this->assertEquals('foo.bar.baz.quux', $ex->getKey());
	}


	public function testGetResolvedKeySegment() {
		$null = null;
		$ex = $this->_mockException($null, ['foo', 'bar'], []);

		$this->assertEquals('foo.bar', $ex->getResolvedKeySegment());
	}

	public function testGetResolvedPath() {
		$null = null;
		$path = ['foo', 'bar'];
		$ex = $this->_mockException($null, $path, []);

		$this->assertSame($path, $ex->getResolvedPath());
	}


	public function testGetMissingKey() {
		$null = null;
		$ex = $this->_mockException($null, [], ['baz', 'quux']);

		$this->assertEquals('baz.quux', $ex->getMissingKeySegment());
	}

	public function testGetMissingPath() {
		$null = null;
		$path = ['baz', 'quux'];
		$ex = $this->_mockException($null, [], $path);

		$this->assertSame($path, $ex->getMissingPath());
	}
}
