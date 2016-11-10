<?php

namespace test\io\http;

use lola\io\http\IHttpRequestResource;



final class MockRequestResource
implements IHttpRequestResource
{

	public function getTime() : int {
		return 3;
	}

	public function getProtocol() : string {
		return 'http';
	}

	public function getHostName() : string {
		return 'sub.domain.tld';
	}

	public function getPath() : string {
		return '/path/to/resource';
	}

	public function getQuery() : array {
		return [
			'foo' => 'bar',
			'baz' => 'quux'
		];
	}


	public function getMethod() : string {
		return 'GET';
	}

	public function getMime() : string {
		return 'text/plain';
	}

	public function getEncoding() : string {
		return 'iso-8859-1';
	}

	public function getAcceptMimes() : array {
		return [
			'text/plain' => 1.0,
			'text/html' => 0.5
		];
	}

	public function getAcceptLanguages() : array {
		return [
			'en' => 1.0,
			'en-us' => 0.9
		];
	}


	public function getClientIP() : string {
		return '127.0.0.1';
	}

	public function getClientUA() : string {
		return 'Mozilla/5.0';
	}

	public function getClientTime() : int {
		return 2;
	}


	public function hasHeader(string $name) : bool {
		return in_array($name, [
			'Header-1',
			'Header-2'
		]);
	}

	public function getHeader(string $name) : string {
		$map = [
			'Header-1' => 'foo',
			'Header-2' => 'bar'
		];

		if (!array_key_exists($name, $map)) return false;

		return $map[$name];
	}


	public function hasCookie(string $name) : bool {
		return in_array($name, [
			'a',
			'b'
		]);
	}

	public function getCookie(string $name) : string {
		$map = [
			'a' => 'foo',
			'b' => 'bar'
		];

		if (!array_key_exists($name, $map)) return false;

		return $map[$name];
	}


	public function getBody() : string {
		return '{"items":[]}';
	}
}
