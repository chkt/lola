<?php

namespace lola\io\http;

use lola\inject\IInjectable;



interface IHttpMessageFactory
extends IInjectable
{

	public function getMessage() : IHttpMessage;
}
