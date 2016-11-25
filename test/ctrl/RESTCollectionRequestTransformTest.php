<?php

use PHPUnit\Framework\TestCase;
use lola\ctrl\RESTCollectionRequestTransform;



class RESTCollectionRequestTransformTest
extends TestCase
{

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
			->willReturn('POST');

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\ACollectionController')
			->setMethods([ 'useRequest' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->any())
			->method('useRequest')
			->willReturn($request);

		$trn = new RESTCollectionRequestTransform();

		$this->assertEquals('failure', $trn->resolveStep($ctrl));
		$this->assertEquals('read', $trn->resolveStep($ctrl));
		$this->assertEquals('create', $trn->resolveStep($ctrl));
		$this->assertEquals('failure', $trn->resolveStep($ctrl));
	}

	public function testCreateStep() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->once())
			->method('setAction')
			->with($this->equalTo('create'))
			->willReturn($route);

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\ACollectionController')
			->setMethods([ 'useRoute' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useRoute')
			->willReturn($route);

		$trn = new RESTCollectionRequestTransform();

		$this->assertEquals(null, $trn->createStep($ctrl));
	}

	public function testReadStep() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->once())
			->method('setAction')
			->with($this->equalTo('read'))
			->willReturn($route);

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\ACollectionController')
			->setMethods([ 'useRoute' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useRoute')
			->willReturn($route);

		$trn = new RESTCollectionRequestTransform();

		$this->assertEquals(null, $trn->readStep($ctrl));
	}

	public function testUnavailableStep() {
		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->once())
			->method('setAction')
			->with($this->equalTo('unavailable'))
			->willReturn($route);

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\ACollectionController')
			->setMethods([ 'useRoute' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useRoute')
			->willReturn($route);

		$trn = new RESTCollectionRequestTransform();

		$this->assertEquals(null, $trn->unavailableStep($ctrl));
	}

	public function testReadStates() {
		$request =$this
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
			->willReturn('GET');

		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->once())
			->method('setAction')
			->with($this->equalTo('read'))
			->willReturn($route);

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\ACollectionController')
			->setMethods([ 'useRequest', 'useRoute' ])
			->getMockForAbstractClass();

		$ctrl
			->expects($this->once())
			->method('useRequest')
			->willReturn($request);

		$ctrl
			->expects($this->once())
			->method('useRoute')
			->willReturn($route);

		$trn = new RESTCollectionRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}

	public function testCreateStates() {
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
			->willReturn('PUT');

		$route = $this
			->getMockBuilder('\lola\route\Route')
			->disableOriginalConstructor()
			->setMethods([ 'setAction' ])
			->getMock();

		$route
			->expects($this->at(0))
			->method('setAction')
			->with($this->equalTo('create'))
			->willReturn($route);

		$ctrl = $this
			->getMockBuilder('\lola\ctrl\ACollectionController')
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

		$trn = new RESTCollectionRequestTransform();

		$trn
			->setTarget($ctrl)
			->process();
	}
}
