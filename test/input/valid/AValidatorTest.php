<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationInterceptor;
use lola\input\valid\ValidationException;
use lola\input\valid\AValidationStep;
use lola\input\valid\AValidationTransform;
use lola\input\valid\AValidationCatchingTransform;
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

	private function _mockTransform(AValidationStep $next, callable $test, callable $transform) {
		$step = $this
			->getMockBuilder(AValidationTransform::class)
			->setConstructorArgs([ $next ])
			->getMockForAbstractClass();

		$step
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		$step
			->expects($this->any())
			->method('_transform')
			->with($this->anything(), $this->anything())
			->willReturnCallback($transform);

		return $step;
	}

	private function _mockCatchableTransform(AValidationStep $next, callable $test, callable $transform, callable $recover) {
		$step = $this
			->getMockBuilder(AValidationCatchingTransform::class)
			->setConstructorArgs([ $next ])
			->getMockForAbstractClass();

		$step
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		$step
			->expects($this->any())
			->method('_transform')
			->with($this->anything(), $this->anything())
			->willReturnCallback($transform);

		$step
			->expects($this->any())
			->method('_recover')
			->with($this->isInstanceOf(ValidationException::class))
			->willReturnCallback($recover);

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


	private function _produceChain(string $prop0 = 'foo', string $prop1 = 'bar') {
		$step3 = $this->_mockStep(function($value) {
			if (!is_int($value)) throw new ValidationException('NOINT', 1);

			return $value;
		});

		$step2 = $this->_mockTransform($step3, function($value) {
			if (!is_string($value)) throw new ValidationException('NOSTRING', 2);

			$int = (int) $value;

			if ((string) $int !== $value) throw new ValidationException('MALFORMED', 3);

			return $int;
		}, function($source, $result) {
			return $result;
		});

		$step1 = $this->_mockTransform($step2, function($value) use ($prop1) {
			if (!is_array($value)) throw new ValidationException('NOARRAY.' . $prop1, 4);

			if (!array_key_exists($prop1, $value)) throw new ValidationException('NOPROP.' . $prop1, 5);

			return $value[$prop1];
		}, function($source, $result) use ($prop1) {
			 $source[$prop1] = $result;

			 return $source;
		});

		$step0 = $this->_mockTransform($step1, function($value) use ($prop0) {
			if (!is_array($value)) throw new ValidationException('NOARRAY.' . $prop0, 6);

			if (!array_key_exists($prop0, $value)) throw new ValidationException('NOPROP.' . $prop0, 7);

			return $value[$prop0];
		}, function($source, $result) use ($prop0) {
			$source[$prop0] = $result;

			return $source;
		});

		return $step0;
	}


	public function _produceException(string $message, int $code) {
		return new ValidationException($message, $code);
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
		$validator = $this->_mockValidator();

		$this->assertNull($validator->getSource());

		$validator->validate('foo');

		$this->assertEquals('foo', $validator->getSource());

		$validator->reset();

		$this->assertNull($validator->getSource());

		$validator->validate(1);

		$this->assertEquals(1, $validator->getSource());

		$validator->validate('');

		$this->assertEquals('', $validator->getSource());
	}

	public function testGetResult() {
		$validator = $this->_mockValidator([
			$this->_produceChain('foo', 'bar'),
			$this->_produceChain('baz', 'quux')
		]);

		$this->assertNull($validator->getResult());

		$validator->validate('foo');

		$this->assertEquals('foo', $validator->getResult());

		$validator->reset();

		$this->assertNull($validator->getResult());

		$validator->validate(1);

		$this->assertEquals(1, $validator->getResult());

		$validator->validate([
			'foo' => [ 'bar' => '1'],
			'baz' => [ 'quux' => '2' ]
		]);

		$this->assertEquals([
			'foo' => [ 'bar' => 1 ],
			'baz' => [ 'quux' => 2 ]
		], $validator->getResult());

		$validator->validate([
			'foo' => '1',
			'baz' => [ 'quux' => '2' ]
		]);

		$this->assertEquals([
			'foo' => '1',
			'baz' => [ 'quux' => 2 ]
		], $validator->getResult());
	}

	public function testGetFailures() {
		$validator = $this->_mockValidator([
			$this->_produceChain('foo', 'baz'),
			$this->_produceChain('bar', 'baz')
		]);

		$this->assertEquals(0, count($validator->getFailures()));

		$validator->validate('foo');

		$this->assertEquals(2, count($validator->getFailures()));
		$this->assertEquals('NOARRAY.foo', $validator->getFailures()[0]->getMessage());
		$this->assertEquals('NOARRAY.bar', $validator->getFailures()[1]->getMessage());

		$validator->reset();

		$this->assertEquals(0, count($validator->getFailures()));

		$validator->validate([
			'foo' => ['baz' => '1']
		]);

		$this->assertEquals(1, count($validator->getFailures()));
		$this->assertEquals('NOPROP.bar', $validator->getFailures()[0]->getMessage());

		$validator->validate([
			'foo' => ['baz' => '1'],
			'bar' => ['baz' => '2']
		]);

		$this->assertEquals(0, count($validator->getFailures()));
	}


	public function testHasChain() {
		$validator = $this->_mockValidator([
			'foo' => $this->_produceChain('foo', 'baz'),
			'bar' => $this->_produceChain('bar', 'baz')
		]);

		$this->assertTrue($validator->hasChain('foo'));
		$this->assertTrue($validator->hasChain('bar'));
		$this->assertFalse($validator->hasChain('baz'));
	}

	public function testIsChainValid() {
		$validator = $this->_mockValidator([
			'foo' => $this->_produceChain('foo', 'baz'),
			'bar' => $this->_produceChain('bar', 'baz')
		]);

		$this->assertFalse($validator->isChainValid('foo'));
		$this->assertFalse($validator->isChainValid('bar'));

		$validator->validate([
			'foo' => [ 'baz' => '1' ],
			'bar' => [ 'baz' => 'bang' ]
		]);

		$this->assertTrue($validator->isChainValid('foo'));
		$this->assertFalse($validator->isChainValid('bar'));
	}

	public function testGetChainResult() {
		$validator = $this
			->_mockValidator([
				'foo' => $this->_produceChain('foo', 'baz'),
				'bar' => $this->_produceChain('bar', 'baz')
			])
			->validate([
				'foo' => [ 'baz' => '1' ],
				'bar' => [ 'baz' => '2' ]
			]);

		$this->assertEquals(1, $validator->getChainResult('foo'));
		$this->assertEquals(2, $validator->getChainResult('bar'));
	}

	public function testGetChainFailure() {
		$validator = $this
			->_mockValidator([
				'foo' => $this->_produceChain('foo', 'baz'),
				'bar' => $this->_produceChain('bar', 'baz')
			])
			->validate([
				'foo' => [ 'baz' => 'bang' ],
				'bar' => [ 'baz' => '2' ]
			]);

		$this->assertEquals($this->_produceException('MALFORMED', 3), $validator->getChainFailure('foo'));
	}


	public function testValidate() {
		$validator = $this->_mockValidator();

		$this->assertEquals($validator, $validator->validate('foo'));
		$this->assertTrue($validator->wasValidated());
		$this->assertTrue($validator->isValid());
		$this->assertEquals('foo', $validator->getSource());
		$this->assertEquals('oof', $validator->getResult());
	}

	public function testValidateChain() {
		$validator = $this->_mockValidator([
			$this->_produceChain('foo', 'baz'),
			$this->_produceChain('bar', 'baz')
		]);

		$this->assertEquals($validator, $validator->validate([
			'foo' => [ 'baz' => '1' ],
			'bar' => [ 'baz' => '2' ]
		]));
		$this->assertTrue($validator->isValid());
		$this->assertEquals([
			'foo' => [ 'baz' => 1 ],
			'bar' => [ 'baz' => 2 ]
		], $validator->getResult());
	}

	public function testValidateCatchable() {
		$step2 = $this->_mockStep(function($value) {
			throw new ValidationException('ouch', 1);
		});

		$step1 = $this->_mockTransform($step2, function($value) {
			return 'bar';
		}, function($source, $result) {
			return $result;
		});

		$step0 = $this->_mockCatchableTransform($step1, function($value) {
			return $value;
		}, function($source, $result) {
			return $result;
		}, function(ValidationException $ex) {
			return 'bang';
		});

		$validator = $this->_mockValidator([ $step0 ]);

		$validator->validate('foo');

		$this->assertTrue($validator->isValid());
		$this->assertEquals('bang', $validator->getResult());
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
			->_mockValidator([
				$this->_produceChain('foo', 'baz'),
				$this->_produceChain('bar', 'baz')
			])
			->validate(false);

		$this->expectException(ValidationException::class);
		$this->expectExceptionMessage('NOARRAY.foo');
		$this->expectExceptionCode(6);

		$validator->assert();
	}


	public function testGetProjection() {
		$validator = $this->_mockValidator([
			$this->_produceChain('foo', 'baz'),
			$this->_produceChain('bar', 'baz')
		]);

		$this->assertEquals([
			'state' => 'new',
			'source' => null,
			'result' => null,
			'failures' => []
		], $validator->getProjection());

		$validator->validate([
			'foo' => [ 'baz' => '1' ],
			'bar' => [ 'baz' => '2' ],
			'quux' => 1
		]);

		$this->assertEquals([
			'state' => 'valid',
			'source' => [
				'foo' => [ 'baz' => '1' ],
				'bar' => [ 'baz' => '2' ],
				'quux' => 1
			],
			'result' => [
				'foo' => [ 'baz' => 1 ],
				'bar' => [ 'baz' => 2 ],
				'quux' => 1
			],
			'failures' => []
		], $validator->getProjection());

		$validator->validate([
			'foo' => [ 'baz' => '1' ],
			'quux' => 1
		]);

		$this->assertEquals([
			'state' => 'invalid',
			'source' => [
				'foo' => [ 'baz' => '1' ],
				'quux' => 1
			],
			'result' => [
				'foo' => [ 'baz' => 1],
				'quux' => 1
			],
			'failures' => [[
				'message' => 'NOPROP.bar',
				'code' => 7
			]]
		], $validator->getProjection());

		$validator->validate(true);

		$this->assertEquals([
			'state' => 'invalid',
			'source' => true,
			'result' => true,
			'failures' => [[
				'message' => 'NOARRAY.foo',
				'code' => 6
			], [
				'message' => 'NOARRAY.bar',
				'code' => 6
			]]
		], $validator->getProjection());
	}
}
