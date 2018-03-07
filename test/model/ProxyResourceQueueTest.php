<?php

namespace test\model;

use eve\common\access\ITraversableAccessor;
use lola\model\ProxyResourceQueue;
use PHPUnit\Framework\TestCase;



final class ProxyResourceQueueTest
extends TestCase
{

	private function _mockAccessor() {
		$access = $this
			->getMockBuilder(ITraversableAccessor::class)
			->getMock();

		return $access;
	}


	private function _produceQueue(array $callbacks = null) {
		return new ProxyResourceQueue($callbacks);
	}

	public function testInheritance() {
		$queue = $this->_produceQueue();

		$this->assertInstanceOf(\lola\type\AQueue::class, $queue);
	}

	public function testProcess() {
		$data = [ 'foo' => 'bar' ];
		$access = $this->_mockAccessor();

		$access
			->method('getProjection')
			->willReturn($data);

		$queue = $this->_produceQueue([
			function(array $item) use ($data) {
				$this->assertSame($data, $item);
			}
		]);

		$this->assertSame($queue, $queue->process($access));
	}
}
