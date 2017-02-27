<?php

namespace test\ctrl;

use PHPUnit\Framework\TestCase;

use lola\ctrl\AItemController;
use lola\ctrl\RESTItemRequestTransform;



final class RESTItemRequestTransformTest
extends TestCase
{

	private function _mockController() {
		return $this
			->getMockBuilder(AItemController::class)
			->disableOriginalConstructor();
	}

	private function _getRoute($action) {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->once())
			->method('setAction')
			->with($this->equalTo($action))
			->willReturn($route);

		return $route;
	}

	private function _getController($route) {
		$ctrl = $this
			->_mockController()
			->setMethods([ 'useRoute' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useRoute')
			->willReturn($route);

		return $ctrl;
	}

	public function _getStatesController($method, $action) {
		$request = $this
			->getMockBuilder('\lola\io\http\HttpRequest')
			->disableOriginalConstructor()
			->setMethods([ 'getPreferedAcceptMime', 'getMethod' ])
			->getMock();

		$request
			->expects($this->at(0))
			->method('getPreferedAcceptMime')
			->willReturn('application/json');

		$request
			->expects($this->at(1))
			->method('getMethod')
			->willReturn($method);

		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->once())
			->method('setAction')
			->with($this->equalTo($action))
			->willReturn($route);

		$ctrl = $this
			->_mockController()
			->setMethods([ 'useRequest', 'useRoute' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->at(0))
			->method('useRequest')
			->willReturn($request);

		$ctrl
			->expects($this->at(1))
			->method('useRoute')
			->willReturn($route);

		return $ctrl;
	}

	public function testResolveStep() {
		$request = $this
			->getMockBuilder('\lola\io\http\HttpRequest')
			->disableOriginalConstructor()
			->setMethods([ 'getPreferedAcceptMime', 'getMethod' ])
			->getMock();

		$request
			->expects($this->at(0))
			->method('getPreferedAcceptMime')
			->willReturn('');

		$request
			->expects($this->at(1))
			->method('getPreferedAcceptMime')
			->willReturn('application/json');

		$request
			->expects($this->at(2))
			->method('getMethod')
			->willReturn('GET');

		$request
			->expects($this->at(3))
			->method('getPreferedAcceptMime')
			->willReturn('application/json');

		$request
			->expects($this->at(4))
			->method('getMethod')
			->willReturn('PUT');

		$request
			->expects($this->at(5))
			->method('getPreferedAcceptMime')
			->willReturn('application/json');

		$request
			->expects($this->at(6))
			->method('getMethod')
			->willReturn('PATCH');

		$request
			->expects($this->at(7))
			->method('getPreferedAcceptMime')
			->willReturn('application/json');

		$request
			->expects($this->at(8))
			->method('getMethod')
			->willReturn('DELETE');

		$request
			->expects($this->at(9))
			->method('getPreferedAcceptMime')
			->willReturn('application/json');

		$request
			->expects($this->at(10))
			->method('getMethod')
			->willReturn('HEAD');

		$ctrl = $this
			->_mockController()
			->setMethods([ 'useRequest' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->exactly(6))
			->method('useRequest')
			->willReturn($request);

		$trn = new RESTItemRequestTransform();

		$this->assertEquals('failure', $trn->resolveStep($ctrl));
		$this->assertEquals('read', $trn->resolveStep($ctrl));
		$this->assertEquals('create', $trn->resolveStep($ctrl));
		$this->assertEquals('update', $trn->resolveStep($ctrl));
		$this->assertEquals('delete', $trn->resolveStep($ctrl));
		$this->assertEquals('failure', $trn->resolveStep($ctrl));
	}

	public function testCreateStep() {
		$route = $this->_getRoute('create');
		$ctrl = $this->_getController($route);
		$trn = new RESTItemRequestTransform();

		$trn->createStep($ctrl);
	}

	public function testReadStep() {
		$route = $this->_getRoute('read');
		$ctrl = $this->_getController($route);
		$trn = new RESTItemRequestTransform();

		$trn->readStep($ctrl);
	}

	public function testUpdateStep() {
		$route = $this->_getRoute('update');
		$ctrl = $this->_getController($route);
		$trn = new RESTItemRequestTransform();

		$trn->updateStep($ctrl);
	}

	public function testDeleteStep() {
		$route = $this->_getRoute('delete');
		$ctrl = $this->_getController($route);
		$trn = new RESTItemRequestTransform();

		$trn->deleteStep($ctrl);
	}

	public function testUnavailableStep() {
		$route = $this->_getRoute('unavailable');
		$ctrl = $this->_getController($route);
		$trn = new RESTItemRequestTransform();

		$trn->unavailableStep($ctrl);
	}

	public function testCreateStates() {
		$ctrl = $this->_getStatesController('GET', 'read');
		$trn = new RESTItemRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}

	public function testReadStates() {
		$ctrl = $this->_getStatesController('PUT', 'create');
		$trn = new RESTItemRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}

	public function testUpdateStates() {
		$ctrl = $this->_getStatesController('PATCH', 'update');
		$trn = new RESTItemRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}

	public function testDeleteStates() {
		$ctrl = $this->_getStatesController('DELETE', 'delete');
		$trn = new RESTItemRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}

	public function testPostStates() {
		$ctrl = $this->_getStatesController('POST', 'unavailable');
		$trn = new RESTItemRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}
}
