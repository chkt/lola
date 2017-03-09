<?php

namespace test\model;

use PHPUnit\Framework\TestCase;

use lola\model\ModelActionLog;



final class ModelActionLogTest
extends TestCase
{

	private function _produceLog() {
		return new ModelActionLog();
	}


	public function testGetLength() {
		$log = $this->_produceLog();

		$this->assertEquals(0, $log->getLength());
	}

	public function test_useItem() {
		$log = $this->_produceLog();

		$this->assertNull($log->useIndex(0));
		$this->assertEquals(0, $log->getLength());

		$log->push('foo', 'bar', 'baz');

		$this->assertEquals([
			'property' => 'foo',
			'oldData' => 'bar',
			'newData' => 'baz'
		], $log->useIndex(0));
		$this->assertEquals(1, $log->getLength());
		$this->assertNull($log->useIndex(1));
	}
}
