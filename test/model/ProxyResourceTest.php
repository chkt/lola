<?php

namespace test\model;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use lola\model\ProxyResource;
use lola\model\ProxyResourceDriver;



final class ProxyResourceTest
extends TestCase
{

	private function _mockDriver() {		//TODO: ProxyResourceDriver is not mockable
		$driver = new ProxyResourceDriver();

		return $driver;
	}

	private function _mockAccessor() {
		$access = $this
			->getMockBuilder(ITraversableAccessor::class)
			->getMock();

		return $access;
	}

	private function _produceResource(ProxyResourceDriver $driver = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new ProxyResource($driver);
	}


	public function testInheritance() {
		$resource = $this->_produceResource();

		$this->assertInstanceOf(\lola\model\IResource::class, $resource);
	}

	public function testGetData() {
		$data = $this->_mockAccessor();
		$resource = $this->_produceResource();

		$resource->create($data);

		$this->assertSame($data, $resource->getData());
	}

	public function testGetData_new() {
		$resource = $this->_produceResource();

		$this->expectException(\ErrorException::class);

		$resource->getData();
	}

	public function testGetData_dead() {
		$resource = $this
			->_produceResource()
			->create($this->_mockAccessor())
			->delete();

		$this->expectException(\ErrorException::class);

		$resource->getData();
	}

	public function testSetData() {
		$resource = $this->_produceResource();

		$resource->create($this->_mockAccessor());

		$data = $this->_mockAccessor();

		$this->assertSame($resource, $resource->setData($data));
		$this->assertSame($data, $resource->getData());
	}

	public function testSetData_new() {
		$resource = $this->_produceResource();

		$this->expectException(\ErrorException::class);

		$resource->setData($this->_mockAccessor());
	}

	public function testSetData_dead() {
		$resource = $this
			->_produceResource()
			->create($this->_mockAccessor())
			->delete();

		$this->expectException(\ErrorException::class);

		$resource->setData($this->_mockAccessor());
	}

	public function testCreate() {
		$data = $this->_mockAccessor();
		$resource = $this->_produceResource();

		$this->assertSame($resource, $resource->create($data));
	}

	public function testCreate_live() {
		$resource = $this
			->_produceResource()
			->create($this->_mockAccessor());

		$this->expectException(\ErrorException::class);

		$resource->create($this->_mockAccessor());
	}

	public function testCreate_dead() {
		$resource = $this
			->_produceResource()
			->create($this->_mockAccessor())
			->delete();

		$this->expectException(\ErrorException::class);

		$resource->create($this->_mockAccessor());
	}

	public function testDelete() {
		$resource = $this
			->_produceResource()
			->create($this->_mockAccessor());

		$this->assertSame($resource, $resource->delete());
	}

	public function testDelete_new() {
		$resource = $this->_produceResource();

		$this->expectException(\ErrorException::class);

		$resource->delete();
	}

	public function testDelete_dead() {
		$resource = $this
			->_produceResource()
			->create($this->_mockAccessor())
			->delete();

		$this->expectException(\ErrorException::class);

		$resource->delete();
	}
}
