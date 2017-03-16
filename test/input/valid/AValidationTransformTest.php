<?php

namespace test\input\valid;

use PHPUnit\Framework\TestCase;

use lola\input\valid\IValidationTransform;
use lola\input\valid\AValidationTransform;
use lola\input\valid\ValidationException;



final class AValidationTransformTest
extends TestCase
{

	private function _mockTest(callable $test) {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->setMethods([ '_transform' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		return $ins;
	}

	private function _mockTransform(IValidationTransform $next, callable $test, callable $transform) {
		$ins = $this
			->getMockBuilder(AValidationTransform::class)
			->setConstructorArgs([ $next ])
			->setMethods([ '_transform' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback($test);

		$ins
			->expects($this->any())
			->method('_transform')
			->with($this->anything(), $this->anything())
			->willReturnCallback($transform);

		return $ins;
	}

	private function _produceTestChain() {
		$step1 = $this->_mockTest(function($value) {
			if (!is_string($value)) throw new ValidationException('NOSTRING', 3);

			if (!is_numeric($value)) throw new ValidationException('NOTNUMERIC', 4);

			return (int) $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			if (!is_array($value)) throw new ValidationException('NOARRAY', 1);

			if (!array_key_exists('foo', $value)) throw new ValidationException('NOPROP', 2);

			return $value['foo'];
		}, function($source, $result) {
			$source['foo'] = $result;

			return $source;
		});

		return $step0;
	}

	function _produceValidationException(string $message = '', int $code = 0) {
		return new ValidationException($message, $code);
	}


	public function testWasValidated() {
		$step0 = $this->_produceTestChain();
		$step1 =& $step0->useNextStep();

		$this->assertFalse($step0->wasValidated());
		$this->assertFalse($step1->wasValidated());

		$step1->validate('foo');
		$this->assertFalse($step0->wasValidated());
		$this->assertTrue($step1->wasValidated());

		$step1->reset();
		$step0->validate('foo');
		$this->assertTrue($step0->wasValidated());
		$this->assertFalse($step1->wasValidated());

		$step0->validate([ 'foo' => 1 ]);
		$this->assertTrue($step0->wasValidated());
		$this->assertTrue($step1->wasValidated());
	}

	public function testIsValid() {
		$step0 = $this->_produceTestChain();
		$step1 =& $step0->useNextStep();

		$this->assertFalse($step0->isValid());
		$this->assertFalse($step1->isValid());

		$step0->validate('foo');
		$this->assertFalse($step0->isValid());
		$this->assertFalse($step1->isValid());

		$step0->validate(['foo' => 1]);
		$this->assertFalse($step0->isValid());
		$this->assertFalse($step1->isValid());

		$step0->validate(['foo' => '1']);
		$this->assertTrue($step0->isValid());
		$this->assertTrue($step1->isValid());

		$step0->reset();
		$step1->validate('1');

		$this->assertFalse($step0->isValid());
		$this->assertTrue($step1->isValid());
	}


	public function testHasNextStep() {
		$step1 = $this->_mockTest(function($value) {
			return $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function($source, $result) {
			return $result;
		});

		$this->assertTrue($step0->hasNextStep());
		$this->assertFalse($step1->hasNextStep());
	}

	public function testUseNextStep() {
		$step1 = $this->_mockTest(function($value) {
			return $value;
		});

		$step0 = $this->_mockTransform($step1, function($value) {
			return $value;
		}, function($source, $result) {
			return $result;
		});

		$this->assertEquals($step1, $step0->useNextStep());
	}

	public function testUseNextStep_error() {
		$step0 = $this->_mockTest(function($value) {
			return $value;
		});

		$this->expectException(\ErrorException::class);

		$step0->useNextStep();
	}

	public function testUseTerminalStep() {
		$step0 = $this->_produceTestChain();
		$step1 = $step0->useNextStep();

		$this->assertEquals($step0, $step0->useTerminalStep());

		$step0->validate('foo');
		$this->assertEquals($step0, $step0->useTerminalStep());

		$step0->validate(['foo' => 1]);
		$this->assertEquals($step1, $step0->useTerminalStep());

		$step0->validate(['foo' => '1']);
		$this->assertEquals($step1, $step0->useTerminalStep());
	}


	public function testGetSource() {
		$step0 = $this->_produceTestChain();
		$step1 =& $step0->useNextStep();

		$this->assertNull($step0->getSource());
		$this->assertNull($step1->getSource());

		$step1->validate(1);
		$this->assertNull($step0->getSource());
		$this->assertEquals(1, $step1->getSource());

		$step1->reset();
		$step0->validate('foo');
		$this->assertEquals('foo', $step0->getSource());
		$this->assertNull($step1->getSource());

		$step0->validate(['foo' => 1]);
		$this->assertEquals(['foo' => 1], $step0->getSource());
		$this->assertEquals(1, $step1->getSource());
	}

	public function testGetResult() {
		$step0 = $this->_produceTestChain();
		$step1 =& $step0->useNextStep();

		$this->assertNull($step0->getResult());
		$this->assertNull($step1->getResult());

		$step1->validate(1);
		$this->assertNull($step0->getResult());
		$this->assertEquals(1, $step1->getResult());

		$step1->reset();
		$step0->validate('foo');
		$this->assertEquals('foo', $step0->getResult());
		$this->assertNull($step1->getResult());

		$step0->validate(['foo' => 1]);
		$this->assertEquals(['foo' => 1], $step0->getResult());
		$this->assertEquals(1, $step1->getResult());

		$step0->validate(['foo' => '1']);
		$this->assertEquals(['foo' => 1], $step0->getResult());
		$this->assertEquals(1, $step1->getResult());
	}

	public function testGetError() {
		$step0 = $this->_produceTestChain();
		$step1 =& $step0->useNextStep();

		$step1->validate(1);
		$this->assertEquals($this->_produceValidationException('NOSTRING', 3), $step1->getError());

		$step1->reset();
		$step0->validate('foo');
		$this->assertEquals($this->_produceValidationException('NOARRAY', 1), $step0->getError());

		$step0->validate(['foo' => 1]);
		$this->assertEquals($this->_produceValidationException('NOSTRING', 3), $step0->getError());
		$this->assertEquals($step0->getError(), $step1->getError());
	}

	public function testGetError_errorNotValidated() {
		$step0 = $this->_produceTestChain();

		$this->expectException(\ErrorException::class);

		$step0->getError();
	}

	public function testGetError_errorValid() {
		$step0 = $this->_produceTestChain();

		$step0->validate(['foo' => '1']);

		$this->expectException(\ErrorException::class);

		$step0->getError();
	}

	public function testValidate() {
		$step0 = $this->_produceTestChain();

		$this->assertEquals($step0, $step0->validate('foo'));
	}

	public function testReset() {
		$step0 = $this->_produceTestChain();
		$step1 =& $step0->useNextStep();

		$step0->validate(['foo' => '1']);
		$this->assertTrue($step0->wasValidated());
		$this->assertTrue($step1->wasValidated());

		$step0->reset();
		$this->assertFalse($step0->wasValidated());
		$this->assertFalse($step1->wasValidated());
	}
}
