<?php

namespace lola\io\http;



interface IHttpRequestResource
{

	public function getTime() : int;

	public function getProtocol() : string;

	public function getHostName() : string;

	public function getPath() : string;

	public function getQuery() : array;


	public function getMethod() : string;

	public function getMime() : string;

	public function getEncoding() : string;

	public function getAcceptMimes() : array;

	public function getAcceptLanguages() : array;


	public function getClientIP() : string;

	public function getClientUA() : string;

	public function getClientTime() : int;


	public function hasHeader(string $name) : bool;

	public function getHeader(string $name) : string;


	public function hasCookie(string $name) : bool;

	public function getCookie(string $name) : string;


	public function getBody() : string;
}
