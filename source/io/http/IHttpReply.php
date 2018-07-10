<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\IReply;



interface IHttpReply
extends IReply
{

	public function& useRequest() : IRequest;

	public function& useCookies() : IHttpCookies;


	public function getCode() : string;

	public function setCode(string $code) : IHttpReply;

	public function getCodeHeader() : string;

	public function getCodeMessage() : string;


	public function getMime() : string;

	public function setMime(string $mime)  : IHttpReply;


	public function getEncoding() : string;

	public function setEncoding(string $encoding) : IHttpReply;


	public function isRedirect() : bool;

	public function getRedirectTarget() : string;

	public function setRedirectTarget(string $url) : IHttpReply;


	public function hasHeader(string $name) : bool;

	public function getHeader(string $name) : string;

	public function setHeader(string $name, string $value) : IHttpReply;

	public function resetHeader(string $name) : IHttpReply;

	public function getHeaders() : array;


	public function getBody() : string;

	public function setBody(string $body) : IReply;

	public function setBodyFromOB() : IReply;


	public function send();

	public function sendOB();
}
