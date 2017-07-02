<?php

namespace lola\io\http;



interface IHttpMessage {

	const HEADER_ACCEPT_LANGUAGE = 'Accept-Language';
	const HEADER_ACCEPT_MIME = 'Accept';
	const HEADER_CONTENT_TYPE = 'Content-Type';
	const HEADER_COOKIE = 'Cookie';
	const HEADER_DATE = 'Date';
	const HEADER_HOST = 'Host';
	const HEADER_LOCATION = 'Location';
	const HEADER_SET_COOKIE = 'Set-Cookie';
	const HEADER_USER_AGENT = 'User-Agent';



	public function getStartLine() : string;

	public function setStartLine(string $line) : IHttpMessage;


	public function hasHeader(string $name) : bool;

	public function numHeader(string $name) : int;


	public function getHeader(string $name, int $index = 0) : string;

	public function setHeader(string $name, string $content, int $index = 0) : IHttpMessage;

	public function clearHeader(string $name) : IHttpMessage;


	public function insertHeader(string $name, string $content, int $index) : IHttpMessage;

	public function appendHeader(string $name, string $content) : IHttpMessage;

	public function removeHeader(string $name, int $index = 0) : IHttpMessage;


	public function iterateHeaders() : \Generator;


	public function getBody() : string;

	public function setBody(string $body) : IHttpMessage;


	public function __toString() : string;
}
