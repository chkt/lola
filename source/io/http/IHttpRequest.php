<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\mime\IMimeContainer;

use lola\io\mime\IMimePayload;



interface IHttpRequest
extends IRequest, IMimeContainer
{

	public function& usePayload() : IMimePayload;

	public function& useCookies() : IHttpCookies;


	public function getMethod() : string;

	public function setMethod(string $method) : IHttpRequest;


	public function getAcceptMimes() : array;

	public function getPreferedAcceptMime(array $mimes) : string;

	public function setAcceptMimes(array $mimes) : IHttpRequest;


	public function getAcceptLanguages() : array;

	public function getPreferedAcceptLanguage(array $langs) : string;

	public function setAcceptLanguages(array $langs) : IHttpRequest;


	public function hasHeader(string $name) : bool;

	public function getHeader(string $name) : string;

	public function setHeader(string $name, string $value) : IHttpRequest;

	public function resetHeader(string $name) : IHttpRequest;
}
