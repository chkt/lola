<?php

namespace lola\io\http;

use lola\io\IClient;



interface IHttpClient
extends IClient
{
	public function getTime() : int;

	public function setTime(int $time) : IHttpClient;
}
