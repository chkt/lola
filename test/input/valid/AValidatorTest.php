<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationInterceptor;
use lola\input\valid\ValidationException;
use lola\input\valid\AValidationStep;
use lola\input\valid\AValidator;



final class AValidatorTest
extends TestCase
{

	private function _mockStep(callable $fn = null) {
		if (is_null($fn)) $fn = function($value) {
			if (!is_string($value)) throw new ValidationException('bang');

			return strrev($value);
		};

		$step = $this
			->getMockBuilder(AValidationStep::class)
			->getMockForAbstractClass();

		$step
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($fn);

		return $step;
	}

	private function _mockInterceptor(callable $fn) {
		$interceptor = $this
			->getMockBuilder(IValidationInterceptor::class)
			->setMethods([ 'intercept' ])
			->getMock();

		$interceptor
			->expects($this->any())
			->method('intercept')
			->with($this->isInstanceOf(AValidationStep::class))
			->willReturnCallback(function(AValidationStep $step) use ($fn) {
				call_user_func($fn, $step);
			});

		return $interceptor;
	}

	private function _mockValidator(array $steps = null, IValidationInterceptor $interceptor = null) : AValidator {
		if (is_null($steps)) $steps = [ $this->_mockStep() ];

		$validator = $this
			->getMockBuilder(AValidator::class)
			->setConstructorArgs([ $steps, $interceptor ])
			->getMockForAbstractClass();

		return $validator;
	}


	private function _produceSteps() {
		$step0 = $this->_mockStep(function($value) {
			if (is_bool($value)) throw new ValidationException('BOOLEAN', 1);

			if (!is_string($value)) throw new ValidationException('NOSTRING', 2, true);

			return $value;
		});

		$step1 = $this->_mockStep(function($value) {
			if ($value === false || $value === 'bang') throw new ValidationException('BANG', 3);

			if ($value === true) return 'true';

			return empty($value) ? 1 : strrev($value);
		});

		return [ $step0, $step1 ];
	}


	public function testWasValidated() {
		$validator = $this->_mockValidator();

		$this->assertFalse($validator->wasValidated());

		$validator->validate('foo');

		$this->assertTrue($validator->wasValidated());

		$validator->reset();

		$this->assertFalse($validator->wasValidated());

		$validator->validate(1);

		$this->assertTrue($validator->wasValidated());
	}

	public function testIsValid() {
		$validator = $this->_mockValidator();

		$this->assertFalse($validator->isValid());

		$validator->validate('foo');

		$this->assertTrue($validator->isValid());

		$validator->reset();

		$this->assertFalse($validator->isValid());

		$validator->validate(1);

		$this->assertFalse($validator->isValid());
	}


	public function testGetSource() {
		$validator = $this->_mockValidator($this->_produceSteps());

		$this->assertNull($validator->getSource());

		$validator->validate('foo');

		$this->assertEquals('foo', $validator->getSource());

		$validator->reset();

		$this->assertNull($validator->getSource());

		$validator->validate(1);

		$this->assertEquals(1, $validator->getSource());

		$validator->validate('');

		$this->assertEquals('', $validator->getSource());

		$validator->validate('bang');

		$this->assertEquals('bang', $validator->getSource());
	}

	public function testGetResult() {
		$validator = $this->_mockValidator($this->_produceSteps());

		$this->assertNull($validator->getResult());

		$validator->validate('foo');

		$this->assertEquals('oof', $validator->getResult());

		$validator->reset();

		$this->assertNull($validator->getResult());

		$validator->validate(1);

		$this->assertEquals(1, $validator->getResult());

		$validator->validate('');

		$this->assertEquals(1, $validator->getResult());

		$validator->validate('bang');

		$this->assertEquals('bang', $validator->getResult());

		$validator->validate(true);

		$this->assertEquals('true', $validator->getResult());
	}

	public function testGetFailures() {
		$validator = $this->_mockValidator($this->_produceSteps());

		$this->assertEmpty($validator->getFailures());

		$validator->validate(1);

		$this->assertEquals(1, count($validator->getFailures()));
		$this->assertEquals('NOSTRING', $validator->getFailures()[0]->getMessage());

		$validator->reset();

		$this->assertEmpty($validator->getFailures());

		$validator->validate('foo');

		$this->assertEmpty($validator->getFailures());

		$validator->validate(false);

		$this->assertEquals(2, count($validator->getFailures()));
		$this->assertEquals('BOOLEAN', $validator->getFailures()[0]->getMessage());
		$this->assertEquals('BANG', $validator->getFailures()[1]->getMessage());
	}


	public function testValidate() {
		$validator = $this->_mockValidator();

		$this->assertEquals($validator, $validator->validate('foo'));
		$this->assertTrue($validator->wasValidated());
		$this->assertTrue($validator->isValid());
		$this->assertEquals('foo', $validator->getSource());
		$this->assertEquals('oof', $validator->getResult());
	}

	public function testValidateInterceptor() {
		$count = 0;

		$step0 = $this->_mockStep(function($value) {
			return $value;
		});

		$step1 = $this->_mockStep(function($value) {
			return $value;
		});

		$interceptor = $this->_mockInterceptor(function(AValidationStep $step) use ($step0, $step1, & $count) {
			$this->assertEquals(++$count % 2 === 1 ? $step0 : $step1, $step);
		});

		$validator = $this->_mockValidator([ $step0, $step1 ], $interceptor);

		$validator
			->validate('foo')
			->validate('bar')
			->validate('baz');

		$this->assertEquals(6, $count);
	}

	public function testReset() {
		$validator = $this
			->_mockValidator()
			->validate('foo');

		$this->assertTrue($validator->wasValidated());
		$this->assertTrue($validator->isValid());
		$this->assertEquals('foo', $validator->getSource());
		$this->assertEquals('oof', $validator->getResult());

		$this->assertEquals($validator, $validator->reset());
		$this->assertFalse($validator->wasValidated());
		$this->assertFalse($validator->isValid());
		$this->assertNull($validator->getSource());
		$this->assertNull($validator->getResult());
	}

	public function testAssert() {
		$validator = $this
			->_mockValidator()
			->validate('foo');

		$this->assertEquals($validator, $validator->assert());
	}

	public function testAssertThrow() {
		$validator = $this
			->_mockValidator($this->_produceSteps())
			->validate(false);

		$this->expectException(ValidationException::class);
		$this->expectExceptionMessage('BOOLEAN');
		$this->expectExceptionCode(1);

		$validator->assert();
	}


	public function testGetProjection() {
		$validator = $this->_mockValidator($this->_produceSteps());

		$this->assertEquals([
			'state' => 'new',
			'source' => null,
			'result' => null,
			'failures' => []
		], $validator->getProjection());

		$validator->validate('foo');

		$this->assertEquals([
			'state' => 'valid',
			'source' => 'foo',
			'result' => 'oof',
			'failures' => []
		], $validator->getProjection());

		$validator->validate(1);

		$this->assertEquals([
			'state' => 'invalid',
			'source' => 1,
			'result' => 1,
			'failures' => [[
				'message' => 'NOSTRING',
				'code' => 2,
				'final' => true
			]]
		], $validator->getProjection());

		$validator->validate(true);

		$this->assertEquals([
			'state' => 'invalid',
			'source' => true,
			'result' => 'true',
			'failures' => [[
				'message' => 'BOOLEAN',
				'code' => 1,
				'final' => false
			]]
		], $validator->getProjection());

		$validator->validate(false);

		$this->assertEquals([
			'state' => 'invalid',
			'source' => false,
			'result' => false,
			'failures' => [[
				'message' => 'BOOLEAN',
				'code' => 1,
				'final' => false
			], [
				'message' => 'BANG',
				'code' => 3,
				'final' => false
			]]
		], $validator->getProjection());
	}
}
