<?php

namespace lola\io\http;

use lola\io\IRequestReplyDriver;

use lola\io\http\IHttpCookies;
use lola\io\http\IHttpRequestQuery;
use lola\io\http\IHttpConfig;



interface IHttpDriver
extends IRequestReplyDriver
{

	public function& useCookies() : IHttpCookies;

	public function& useRequestQuery() : IHttpRequestQuery;

	public function& useConfig() : IHttpConfig;
}
