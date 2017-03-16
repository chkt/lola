<?php

namespace test\input\form;

use PHPUnit\Framework\TestCase;

use lola\input\valid\AValidationTransform;
use lola\input\form\Field;
use lola\input\form\ValidationInterceptor;



final class ValidationInterceptorTest
extends TestCase
{

	public function _mockValidationStep($id) : AValidationTransform {
		$step = $this
			->getMockBuilder(AValidationTransform::class)
			->getMockForAbstractClass();

		$step
			->expects($this->any())
			->method('_validate')
			->with($this->anything())
			->willReturnCallback(function($value) {
				return $value;
			});

		$step
			->expects($this->any())
			->method('getId')
			->with()
			->willReturn($id);

		return $step;
	}

	private function _produceField($name) : Field {
		return new Field($name);
	}

	public function testIntercept() {
		$field0 = $this->_produceField('foo');
		$field1 = $this->_produceField('bar');

		$step0 = $this->_mockValidationStep('foo');
		$step1 = $this->_mockValidationStep('bar');
		$step2 = $this->_mockValidationStep('baz');

		$map = [
			'foo' => & $field0,
			'bar' => & $field1,
			'baz' => & $field0
		];

		$interceptor = new ValidationInterceptor($map);

		$this->assertEquals($interceptor, $interceptor->intercept('foo', $step0));
		$this->assertEquals($step0, $field0->useValidation());

		$this->assertEquals($interceptor, $interceptor->intercept('bar', $step1));
		$this->assertEquals($step1, $field1->useValidation());

		$this->assertEquals($interceptor, $interceptor->intercept('baz', $step2));
		$this->assertEquals($step2, $field0->useValidation());
	}
}
