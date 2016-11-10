<?php

namespace lola\io\http;

use lola\io\http\IHttpRequestResource;

use lola\io\http\HttpConfig;



class HttpRequestResource
implements IHttpRequestResource
{

	const VERSION = '0.5.0';



	public function getTime() : int {
		return $_SERVER['REQUEST_TIME'];		//php does not return anything when using filter_input()
	}

	public function getProtocol() : string {
		switch (filter_input(INPUT_SERVER, 'HTTPS')) {
			case '' :
			case 'off' : return 'http';
			default : return 'https';
		}
	}

	public function getHostName() : string {
		return filter_input(INPUT_SERVER, 'SERVER_NAME');
	}

	public function getPath() : string {
		return parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
	}

	public function getQuery() : array {
		$query = [];

		parse_str(filter_input(INPUT_SERVER, 'QUERY_STRING'), $query);

		return $query;
	}


	public function getMethod() : string {
		return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
	}

	public function getMime() : string {
		return HttpConfig::parseHeader(filter_input(INPUT_SERVER, 'HTTP_CONTENT_TYPE'))[HttpConfig::HEADER_PARAM_DEFAULT];
	}

	public function getEncoding() : string {
		return HttpConfig::parseHeader(filter_input(INPUT_SERVER, 'HTTP_CONTENT_TYPE'))['charset'];
	}

	public function getAcceptMimes() : array {
		return HttpConfig::parseWeightedHeader(filter_input(INPUT_SERVER, 'HTTP_ACCEPT'), '[a-z]+\\/[a-z]+|\\*\\/\\*');
	}

	public function getAcceptLanguages() : array {
		return HttpConfig::parseWeightedHeader(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), '[A-Za-z]{2}(?:-[A-Za-z]{2})?');
	}


	public function getClientIP() : string {
		return filter_input(INPUT_SERVER, 'REMOTE_ADDR');
	}

	public function getClientUA() : string {
		return filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
	}

	public function getClientTime() : int {
		return strtotime(filter_input(INPUT_SERVER, 'HTTP_DATE'));
	}


	public function hasHeader(string $name) : bool {
		$filter = 'HTTP_' . str_replace('-', '_', strtoupper($name));

		return filter_input(INPUT_SERVER, $filter) !== false;
	}

	public function getHeader(string $name) : string {
		$filter = 'HTTP_' . str_replace('-', '_', strtoupper($name));

		return filter_input(INPUT_SERVER, $filter);
	}


	public function hasCookie(string $name) : bool {
		$ret = filter_input(INPUT_COOKIE, $name);

		return !is_null($ret) && $ret !== false;
	}

	public function getCookie(string $name) : string {
		return filter_input(INPUT_COOKIE, $name);
	}


	public function getBody() : string {
		$handle = fopen('php://input', 'r');
		$res = stream_get_contents($handle);

		fclose($handle);

		return $res;
	}
}
