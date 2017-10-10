<?php

namespace test\error;

use PHPUnit\Framework\TestCase;

use eve\common\IGenerateable;
use eve\access\TraversableAccessor;
use eve\inject\IInjector;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use lola\error\IErrorHandler;
use lola\error\IErrorEmitter;
use lola\error\ErrorEmitter;



final class ErrorEmitterTest
extends TestCase
{

	private function _mockHandler() {
		$ins = $this
			->getMockBuilder(IErrorHandler::class)
			->getMock();

		return $ins;
	}

	private function _mockInjector(IErrorHandler $handler = null) {
		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		if (!is_null($handler)) $ins
			->expects($this->once())
			->method('produce')
			->with($this->isType('string'))
			->willReturn($handler);

		return $ins;
	}


	private function _produceEmitter(IInjector $injector = null) : ErrorEmitter {
		if (is_null($injector)) $injector = $this->_mockInjector();

		return new ErrorEmitter($injector);
	}

	private function _produceAccessor(array $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$emitter = $this->_produceEmitter();

		$this->assertInstanceOf(IErrorEmitter::class, $emitter);
		$this->assertInstanceOf(IErrorHandler::class, $emitter);
		$this->assertInstanceOf(IInjectable::class, $emitter);
		$this->assertInstanceOf(IGenerateable::class, $emitter);
	}

	public function testDependencyConfig() {
		$this->assertEquals([
			'injector:'
		], ErrorEmitter::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_DEFAULT, ErrorEmitter::getInstanceIdentity($this->_produceAccessor()));
		$this->assertEquals('foo', ErrorEmitter::getInstanceIdentity($this->_produceAccessor([ 'id' => 'foo' ])));
	}


	public function testHandleException() {
		$emitter = $this->_produceEmitter();

		$handlerA = $this->_mockHandler();

		$handlerA
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(\Throwable::class))
			->willReturn(null);

		$handlerB = $this->_mockHandler();

		$handlerB
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(\Throwable::class))
			->willReturn(null);

		$emitter->appendItem($handlerA);
		$emitter->appendItem($handlerB);
		$emitter->handleException(new \Exception());
	}

	public function testHandleException_error() {
		$handlerA = $this->_mockHandler();

		$handlerA
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(\ErrorException::class))
			->willReturn(null);

		$handlerB = $this->_mockHandler();

		$handlerB
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(\Throwable::class))
			->willThrowException(new \ErrorException());

		$injector = $this->_mockInjector($handlerA);
		$emitter = $this->_produceEmitter($injector);
		$emitter->appendItem($handlerB);

		$emitter->handleException(new \Exception());
	}


	public function testGetLength() {
		$emitter = $this->_produceEmitter();

		$this->assertEquals(0, $emitter->getLength());
	}

	public function testHasIndex() {
		$emitter = $this->_produceEmitter();

		$this->assertFalse($emitter->hasIndex(0));
		$this->assertSame($emitter, $emitter->appendItem(0));
		$this->assertTrue($emitter->hasIndex(0));
		$this->assertFalse($emitter->hasIndex(1));
	}

	public function testRemoveIndex() {
		$emitter = $this->_produceEmitter();

		$this->assertSame($emitter, $emitter->appendItem(0));
		$this->assertEquals(1, $emitter->getLength());
		$this->assertSame($emitter, $emitter->removeIndex(0));
		$this->assertEquals(0, $emitter->getLength());
		$this->assertSame($emitter, $emitter->removeIndex(0));
		$this->assertEquals(0, $emitter->getLength());
	}

	public function testGetItemAt() {
		$emitter = $this->_produceEmitter();

		$this->assertSame($emitter, $emitter->appendItem(1));
		$this->assertEquals(1, $emitter->getItemAt(0));
	}

	public function testInsertItem() {
		$emitter = $this->_produceEmitter();

		$this->assertSame($emitter, $emitter->appendItem(0));
		$this->assertEquals(0, $emitter->indexOfItem(0));
		$this->assertSame($emitter, $emitter->insertItem(0, 1));
		$this->assertEquals(1, $emitter->indexOfItem(0));
		$this->assertEquals(0, $emitter->indexOfItem(1));
	}

	public function testAppendItem() {
		$emitter = $this->_produceEmitter();

		$this->assertSame($emitter, $emitter->appendItem(0));
		$this->assertEquals(0, $emitter->indexOfItem(0));
		$this->assertSame($emitter, $emitter->appendItem(1));
		$this->assertEquals(0, $emitter->indexOfItem(0));
		$this->assertEquals(1, $emitter->indexOfItem(1));
	}

	public function testIndexOfItem() {
		$emitter = $this->_produceEmitter();

		$this->assertEquals(-1, $emitter->indexOfItem(0));
		$this->assertSame($emitter, $emitter->appendItem(0));
		$this->assertEquals(0, $emitter->indexOfItem(0));
	}

	public function testIterate() {
		$emitter = $this->_produceEmitter();

		$this->assertSame($emitter, $emitter->appendItem(0));
		$this->assertSame($emitter, $emitter->appendItem(1));

		$gen = $emitter->iterate();
		$gen->rewind();
		$this->assertEquals(0, $gen->current());
		$gen->next();
		$this->assertEquals(1, $gen->current());
		$this->assertTrue($gen->valid());
		$gen->next();
		$this->assertFalse($gen->valid());
	}

	public function testIterate_fallback() {
		$handler = $this->_mockHandler();
		$injector = $this->_mockInjector($handler);
		$emitter = $this->_produceEmitter($injector);

		$gen = $emitter->iterate();
		$gen->rewind();
		$this->assertSame($handler, $gen->current());
		$gen->next();
		$this->assertFalse($gen->valid());
	}
}

