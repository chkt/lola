<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\ValidationException;



final class ValidationExceptionTest
extends TestCase
{

	public function testInheritance() {
		$ex1 = new ValidationException('foo');

		$this->assertInstanceOf(\lola\input\valid\IValidationException::class, $ex1);
		$this->assertInstanceOf(\eve\common\projection\IProjectable::class, $ex1);
		$this->assertInstanceOf(\Exception::class, $ex1);
		$this->assertInstanceOf(\Throwable::class, $ex1);

		$this->assertEquals('foo', $ex1->getMessage());
		$this->assertEquals(0, $ex1->getCode());

		$ex2 = new ValidationException('bar', 1);

		$this->assertEquals('bar', $ex2->getMessage());
		$this->assertEquals(1, $ex2->getCode());
	}

	public function testGetProjection() {
		$ex = new ValidationException('foo');

		$this->assertEquals([
			'message' => 'foo',
			'code' => 0
		], $ex->getProjection());
	}
}
