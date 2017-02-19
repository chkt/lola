<?php

namespace lola\io\mime;

use lola\io\mime\IMimeParser;



interface IMimeConfig
{

	const MIME_PLAIN = 'text/plain';
	const MIME_HTML = 'text/html';
	const MIME_XML = 'application/xml';
	const MIME_XHTML = 'application/xml+html';
	const MIME_FORM = 'application/x-www-form-urlencoded';
	const MIME_JSON = 'application/json';

	const ENCODING_UTF8 = 'utf-8';



	public function isMime(string $mime) : bool;

	public function isEncoding(string $encoding) : bool;


	public function produceMimeParser(string $mime) : IMimeParser;
}
