<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\http\IHttpCookies;



interface IHttpRequest
extends IRequest
{

	public function& useCookies() : IHttpCookies;


	public function getMethod() : string;

	public function setMethod(string $method) : IHttpRequest;


	public function getMime() : string;

	public function setMime(string $mime) : IHttpRequest;


	public function getEncoding() : string;

	public function setEncoding(string $encoding) : IHttpRequest;


	public function& useAcceptMimes() : array;

	public function getPreferedAcceptMime(array $mimes) : string;

	public function setAcceptMimes(array $mimes) : IHttpRequest;


	public function& useAcceptLanguages() : array;

	public function getPreferedAcceptLanguage(array $langs) : string;

	public function setAcceptLanguages(array $langs) : IHttpRequest;


	public function hasHeader(string $name) : bool;

	public function getHeader(string $name) : string;

	public function setHeader(string $name, string $value) : IHttpRequest;


	public function getBody() : string;

	public function setBody(string $body) : IHttpRequest;
}
