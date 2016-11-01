<?php

namespace lola\io\http;



interface IHttpReplyResource
{

	public function sendHeader(string $header) : IHttpReplyResource;

	public function sendCookie(string $name, string $value, int $expires) : IHttpReplyResource;

	public function sendBody(string $body) : IHttpReplyResource;
}
