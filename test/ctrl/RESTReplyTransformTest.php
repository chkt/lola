<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjector;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpDriver;
use lola\ctrl\RESTReplyTransform;



final class RESTReplyTransformTest
extends TestCase
{

	private function _mockInjector() : IInjector {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->expects($this->any())
			->method('produce')
			->with($this->logicalOr(
				$this->equalTo(\lola\io\http\RemoteReplyFactory::class)
			))
			->willReturnCallback(function(string $qname) {
				return new $qname;
			});

		return $injector;
	}

	private function _mockDriver(IInjector $injector = null) : IHttpDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$driver = new HttpDriver($injector);

		return $driver;
	}

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

		$driver = $this->_mockDriver();

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
