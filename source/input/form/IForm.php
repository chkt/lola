<?php

namespace lola\input\form;

use lola\type\IProjectable;

use lola\io\http\payload\IHttpPayload;



interface IForm
extends IProjectable
{

	public function isValidated() : bool;

	public function isSubmitted() : bool;

	public function isModified() : bool;

	public function isValid() : bool;


	public function getId() : string;


	public function validate(IHttpPayload $payload) : IForm;
}
