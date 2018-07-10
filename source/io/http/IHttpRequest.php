<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\IReply;
use lola\io\IClient;
use lola\io\http\payload\IHttpPayload;



interface IHttpRequest
extends IRequest
{

	public function& useReply() : IReply;

	public function& useClient() : IClient;


	public function& usePayload() : IHttpPayload;

	public function& useCookies() : IHttpCookies;


	public function getTime() : int;

	public function setTime(int $time) : IRequest;


	public function getProtocol() : string;

	public function setProtocol(string $protocol) : IRequest;


	public function getMethod() : string;

	public function setMethod(string $method) : IHttpRequest;


	public function getHostName() : string;

	public function setHostName(string $hostName) : IRequest;


	public function getPath() : string;

	public function setPath(string $path) : IRequest;


	public function& useQuery() : array;

	public function setQuery(array $query) : IRequest;


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
