<?php

namespace lola\input\form;

use lola\type\IProjectable;

use lola\io\mime\IMimePayload;



interface IForm
extends IProjectable
{

	public function isValidated() : bool;

	public function isSubmitted() : bool;

	public function isModified() : bool;

	public function isValid() : bool;


	public function getId() : string;


	public function validate(IMimePayload $payload) : IForm;
}
