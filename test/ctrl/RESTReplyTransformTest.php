<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;

use test\io\http\MockDriver;
use lola\ctrl\RESTReplyTransform;



final class RESTReplyTransformTest
extends TestCase
{

	public function testViewStep() {
		$return = new \lola\type\Stack();

		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'useActionResult' ])
			->getMock();

		$route
			->expects($this->once())
			->method('useActionResult')
			->willReturn($return);

		$driver = new MockDriver();

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\AReplyController')
			->setConstructorArgs([ & $driver ])
			->setMethods([ 'useRoute' ])
			->getMock();

		$ctrl
			->expects($this->once())
			->method('useRoute')
			->willReturn($route);

		$return->pushItem([
			'state' => 'success',
			'items' => []
		]);

		$trn = new RESTReplyTransform();

		$this->assertEquals(null, $trn->viewStep($ctrl));
		$this->assertEquals('application/json', $ctrl->useReply()->getMime());
		$this->assertEquals('{"state":"success","items":[]}', $ctrl->useReply()->getBody());
	}
}
