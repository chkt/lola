<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;



final class ValidationExceptionTest
extends TestCase
{

	public function testInheritance() {
		$ex1 = new ValidationException('foo');

		$this->assertEquals('foo', $ex1->getMessage());
		$this->assertEquals(0, $ex1->getCode());

		$ex2 = new ValidationException('bar', 1);

		$this->assertEquals('bar', $ex2->getMessage());
		$this->assertEquals(1, $ex2->getCode());
	}

	public function testIsFinal() {
		$ex1 = new ValidationException('foo');

		$this->assertFalse($ex1->isFinal());

		$ex2 = new ValidationException('foo', 1, false);

		$this->assertFalse($ex2->isFinal());

		$ex3 = new ValidationException('foo', 1, true);

		$this->assertTrue($ex3->isFinal());
	}

	public function testGetProjection() {
		$ex = new ValidationException('foo');

		$this->assertEquals([
			'message' => 'foo',
			'code' => 0,
			'final' => false
		], $ex->getProjection());
	}
}
