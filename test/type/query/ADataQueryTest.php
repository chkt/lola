<?php

namespace test\type\query;

use PHPUnit\Framework\TestCase;

use lola\type\query\ADataQuery;



final class ADataQueryTest
extends TestCase
{

	private function _mockQuery(array $props = [], array $ops = []) : ADataQuery {
		return $this
			->getMockBuilder(ADataQuery::class)
			->setConstructorArgs([ $props, $ops ])
			->getMockForAbstractClass();
	}


	public function testGetRequirements() {
		$query = $this->_mockQuery();

		$this->assertInternalType('array', $query->getRequirements());
	}

	public function testSetRequirements() {
		$query = $this->_mockQuery();
		$reqs = [ 'foo' => 1, 'bar' => 2 ];

		$this->assertEquals($query, $query->setRequirements($reqs));
		$this->assertEquals($reqs, $query->getRequirements());
	}


	public function testMatch() {
		$query = $this
			->_mockQuery([ 'foo', 'bar'], [ADataQuery::OP_EQ, ADataQuery::OP_EQ ])
			->setRequirements([ 1, 2 ]);

		$this->assertTrue($query->match([ 'foo' => 1, 'bar' => 2, 'baz' => 3]));
		$this->assertFalse($query->match(['bar' => 2, 'baz' => 3]));
		$this->assertFalse($query->match(['foo' => 1, 'baz' => 3]));
		$this->assertFalse($query->match(['foo' => 2, 'bar' => 2]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 1]));
	}

	public function testMatch_defaultOp() {
		$query = $this
			->_mockQuery(['foo', 'bar'])
			->setRequirements([1, 2]);

		$this->assertTrue($query->match(['foo' => 1, 'bar' => 2]));
	}

	public function testMatch_exists() {
		$query = $this
			->_mockQuery(['foo','bar'], [ADataQuery::OP_EXISTS, ADataQuery::OP_EXISTS])
			->setRequirements([true, false]);

		$this->assertTrue($query->match(['foo' => 1, 'baz' => 3]));
		$this->assertFalse($query->match(['baz' => 3]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 2, 'baz' => 3]));
		$this->assertFalse($query->match(['bar' => 2, 'baz' => 3]));
	}

	public function testMatch_notEqual() {
		$query = $this
			->_mockQuery(['foo','bar'], [ADataQuery::OP_EQ, ADataQuery::OP_NEQ])
			->setRequirements([1, 2]);

		$this->assertTrue($query->match(['foo' => 1, 'bar' => 1]));
		$this->assertFalse($query->match(['foo' => 2, 'bar' => 1]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 2]));
		$this->assertTrue($query->match(['foo' => 1, 'bar' => 3]));
	}

	public function testMatch_lessThan() {
		$query = $this
			->_mockQuery(['foo','bar'], [ADataQuery::OP_EQ, ADataQuery::OP_LT])
			->setRequirements([1, 0]);

		$this->assertTrue($query->match(['foo' => 1, 'bar' => -1]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 0]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 1]));
	}

	public function testMatch_greaterThan() {
		$query = $this
			->_mockQuery(['foo','bar'], [ADataQuery::OP_EQ, ADataQuery::OP_GT])
			->setRequirements([1, 0]);

		$this->assertTrue($query->match(['foo' => 1, 'bar' => 1]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 0]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => -1]));
	}

	public function testMatch_lessEqualsThan() {
		$query = $this
			->_mockQuery(['foo','bar'], [ADataQuery::OP_EQ, ADataQuery::OP_LTE])
			->setRequirements([1, 0]);

		$this->assertTrue($query->match(['foo' => 1, 'bar' => -1]));
		$this->assertTrue($query->match(['foo' => 1, 'bar' => 0]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => 1]));
	}

	public function testMatch_greaterEqualsThan() {
		$query = $this
			->_mockQuery(['foo', 'bar'], [ADataQuery::OP_EQ, ADataQuery::OP_GTE])
			->setRequirements([1, 0]);

		$this->assertTrue($query->match(['foo' => 1, 'bar' => 1]));
		$this->assertTrue($query->match(['foo' => 1, 'bar' => 0]));
		$this->assertFalse($query->match(['foo' => 1, 'bar' => -1]));
	}
}
