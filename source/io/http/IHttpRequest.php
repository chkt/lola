<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\mime\IMimeContainer;

use lola\io\http\IHttpCookies;
use lola\io\http\payload\IHttpPayload;



interface IHttpRequest
extends IRequest, IMimeContainer
{

	public function& usePayload() : IHttpPayload;

	public function& useCookies() : IHttpCookies;


	public function getMethod() : string;

	public function setMethod(string $method) : IHttpRequest;


	public function& useAcceptMimes() : array;

	public function getPreferedAcceptMime(array $mimes) : string;

	public function setAcceptMimes(array $mimes) : IHttpRequest;


	public function& useAcceptLanguages() : array;

	public function getPreferedAcceptLanguage(array $langs) : string;

	public function setAcceptLanguages(array $langs) : IHttpRequest;


	public function hasHeader(string $name) : bool;

	public function getHeader(string $name) : string;

	public function setHeader(string $name, string $value) : IHttpRequest;
}
