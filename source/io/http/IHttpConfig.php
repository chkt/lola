<?php

namespace lola\io\http;



interface IHttpConfig
{

	const RULE_PROTOCOL = 'protocol';
	const RULE_METHOD = 'method';
	const RULE_MIME = 'mime';
	const RULE_ENCODING = 'encoding';
	const RULE_CODE = 'code';
	const RULE_REDIRECT_CODE = 'redirectCode';

	const LINK_CODE_HEADER = 'codeHeader';
	const LINK_CODE_MESSAGE = 'codeMessage';
	const LINK_MIME_BODY = 'mimeBody';
	const LINK_MIME_LINK = 'mimeLink';

	const PROTOCOL_HTTP = 'http';
	const PROTOCOL_HTTPS = 'https';

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';
	const METHOD_HEAD = 'HEAD';
	const METHOD_OPTIONS = 'OPTIONS';

	const MIME_PLAIN = 'text/plain';
	const MIME_HTML = 'text/html';
	const MIME_XML = 'application/xml';
	const MIME_XHTML = 'application/xml+html';
	const MIME_FORM = 'application/x-www-form-urlencoded';
	const MIME_JSON = 'application/json';

	const ENCODING_UTF8 = 'utf-8';

	const CODE_OK = '200';
	const CODE_NO_CONTENT = '204';
	const CODE_MOVED_PERMANENT = '301';
	const CODE_FOUND = '302';
	const CODE_REDIRECT = '303';
	const CODE_MOVED_TEMPORARY = '307';
	const CODE_NOT_VALID = '400';
	const CODE_NOT_AUTH = '403';
	const CODE_NOT_FOUND = '404';
	const CODE_GONE = '410';
	const CODE_ERROR = '500';
	const CODE_UNAVAILABLE = '503';

	const HEADER_PARAM_DEFAULT = '__';


	static function parseHeader(string $header) : array;

	static function buildHeader(string $default, array $params) : string;

	static function parseWeightedHeader(string $header) : array;

	static function buildWeightedHeader(array $params) : string;



	public function isProtocol($protocol) : bool;

	public function isMethod($method) : bool;

	public function isMime($mime) : bool;

	public function isEncoding($encoding) : bool;

	public function isCode($code) : bool;

	public function isRedirectCode($code) : bool;


	public function getCodeHeader(string $code) : string;

	public function getCodeMessage(string $code) : string;

	public function getMimeBody(string $mime, string $code, string $link = null) : string;


	public function hasRule(string $type, string $rule) : bool;

	public function addRule(string $type, string ...$rules) : IHttpConfig;

	public function removeRule(string $type, string ...$rules) : IHttpConfig;


	public function hasLink(string $type, string $key) : bool;

	public function addLink(string $type, string $key, string $value) : IHttpConfig;

	public function removeLink(string $type, string $key) : IHttpConfig;
}
