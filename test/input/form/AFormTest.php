<?php

namespace test\input\form;

use PHPUnit\Framework\TestCase;

use lola\io\mime\IMimePayload;
use lola\input\valid\IValidationInterceptor;
use lola\input\valid\AValidator;
use lola\input\valid\step\ArrayPropSelect;
use lola\input\valid\step\Constraint;
use lola\input\valid\step\IsStringNonEmpty;
use lola\input\form\Field;
use lola\input\form\ValidationInterceptor;
use lola\input\form\AForm;



final class AFormTest
extends TestCase
{

	private function _mockPayload(array $fields = []) : IMimePayload {
		$payload = $this
			->getMockBuilder(IMimePayload::class)
			->getMock();

		$payload
			->expects($this->any())
			->method('isValid')
			->with()
			->willReturn(true);

		$payload
			->expects($this->any())
			->method('get')
			->with()
			->willReturn($fields);

		return $payload;
	}

	private function _mockValidator(array $steps, IValidationInterceptor $interceptor) : AValidator {
		return $this
			->getMockBuilder(AValidator::class)
			->setConstructorArgs([ $steps, $interceptor ])
			->getMockForAbstractClass();
	}

	private function _mockForm(string $id = 'foo') : AForm {
		$field0 = new Field('foo', [ 'bang' ]);
		$field1 = new Field('bar', [ '1' ]);
		$field2 = new Field('baz', [], Field::FLAG_SUBMIT);

		$step0 = new ArrayPropSelect('foo', new Constraint(['bang', 'barf']));
		$step1 = new ArrayPropSelect('bar', new IsStringNonEmpty());

		$interceptor = new ValidationInterceptor([
			'arrayProp.foo' => & $field0,
			'constraint' => & $field0,
			'arrayProp.bar' => & $field1,
			'stringNonEmpty' => & $field1
		]);

		$validator = $this->_mockValidator([ $step0, $step1 ], $interceptor);

		$form = $this
			->getMockBuilder(AForm::class)
			->setConstructorArgs([ $id, [
				$field0,
				$field1,
				$field2
			], $validator ])
			->getMockForAbstractClass();

		return $form;
	}


	public function testIsValidated() {
		$form = $this->_mockForm();

		$this->assertFalse($form->isValidated());

		$form->validate($this->_mockPayload([
			'foo' => 'bang',
			'bar' => '1'
		]));

		$this->assertTrue($form->isValidated());
	}

	public function testIsSubmitted() {
		$form0 = $this->_mockForm();

		$this->assertFalse($form0->isSubmitted());

		$form0->validate($this->_mockPayload([
			'foo' => 'bang',
			'bar' => '1'
		]));

		$this->assertFalse($form0->isSubmitted());

		$form1 = $this
			->_mockForm()
			->validate($this->_mockPayload([
			'foo' => 'bang',
			'bar' => '1',
			'baz' => 'submit'
		]));

		$this->assertTrue($form1->isSubmitted());
	}

	public function testIsModified() {
		$form0 = $this->_mockForm();

		$this->assertFalse($form0->isModified());

		$form0->validate($this->_mockPayload([
			'foo' => 'bang',
			'bar' => '1',
			'baz' => 'submit'
		]));

		$this->assertFalse($form0->isModified());

		$form1 = $this
			->_mockForm()
			->validate($this->_mockPayload([
				'foo' => 'quux',
				'bar' => '',
				'baz' => 'submit'
			]));

		$this->assertTrue($form1->isModified());
	}

	public function testIsValid() {
		$form0 = $this->_mockForm();

		$this->assertFalse($form0->isValid());

		$form0->validate($this->_mockPayload([
			'foo' => 'quux',
			'bar' => '',
			'baz' => 'submit'
		]));

		$this->assertFalse($form0->isValid());

		$form1 = $this
			->_mockForm()
			->validate($this->_mockPayload([
				'foo' => 'barf',
				'bar' => 'nonempty',
				'baz' => 'submit'
			]));

		$this->assertTrue($form1->isValid());
	}


	public function testGetId() {
		$form = $this->_mockForm('bar');

		$this->assertEquals('bar', $form->getId());
	}


	public function testValidate() {
		$form = $this->_mockForm();

		$this->assertEquals($form, $form->validate($this->_mockPayload([
			'foo' => 'bang',
			'bar' => '1',
		])));
	}


	public function testGetProjection() {
		$form = $this
			->_mockForm('foobar')
			->validate($this->_mockPayload([
				'foo' => 'quux',
				'bar' => '',
				'baz' => 'submit'
			]));

		$proj = $form->getProjection();

		$this->assertArrayHasKey('id', $proj);
		$this->assertEquals('foobar', $proj['id']);
		$this->assertArrayHasKey('state', $proj);
		$this->assertEquals(0xb, $proj['state']);
		$this->assertArrayHasKey('fields', $proj);
		$this->assertEquals(3, count($proj['fields']));
	}
}
