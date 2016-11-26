<?php

use PHPUnit\Framework\TestCase;

use lola\io\http\HttpConfig;



class HttpConfigTest
extends TestCase
{
	public function testParseHeader() {
		$this->assertEquals(HttpConfig::parseHeader('foo;bar=x;baz=y'), [
			'__' => 'foo',
			'bar' => 'x',
			'baz' => 'y'
		]);
	}

	public function testBuildHeader() {
		$this->assertEquals(HttpConfig::buildHeader('foo', [
			'bar' => 'x',
			'baz' => 'y'
		]), 'foo;bar=x;baz=y');
	}

	public function testParseWeightedHeader() {
		$this->assertEquals(HttpConfig::parseWeightedHeader('foo,bar,quux;q=0.1,baz;q=0.5'), [
			'foo' => 1.0,
			'bar' => 1.0,
			'baz' => 0.5,
			'quux' => 0.1
		]);
	}

	public function testBuildWeightedHeader() {
		$this->assertEquals(HttpConfig::buildWeightedHeader([
			'foo' => 1.0,
			'bar' => 1.0,
			'baz' => 0.5,
			'quux' => 0.1
		]), 'foo,bar,baz;q=0.5,quux;q=0.1');
	}


	public function testIsProtocol() {
		$config = new HttpConfig();

		$this->assertTrue($config->isProtocol('http'));
		$this->assertTrue($config->isProtocol('https'));
		$this->assertFalse($config->isProtocol('foo'));

		$config->addRule(HttpConfig::RULE_PROTOCOL, 'foo');

		$this->assertTrue($config->isProtocol('foo'));

		$config->removeRule(HttpConfig::RULE_PROTOCOL, 'foo');

		$this->assertFalse($config->isProtocol('foo'));
	}

	public function testIsMethod() {
		$config = new HttpConfig();

		$this->assertTrue($config->isMethod($config::METHOD_GET));
		$this->assertTrue($config->isMethod($config::METHOD_POST));
		$this->assertTrue($config->isMethod($config::METHOD_PUT));
		$this->assertTrue($config->isMethod($config::METHOD_PATCH));
		$this->assertTrue($config->isMethod($config::METHOD_DELETE));
		$this->assertTrue($config->isMethod($config::METHOD_OPTIONS));
		$this->assertTrue($config->isMethod($config::METHOD_HEAD));
		$this->assertFalse($config->isMethod('foo'));

		$config->addRule($config::RULE_METHOD, 'foo');

		$this->assertTrue($config->isMethod('foo'));

		$config->removeRule($config::RULE_METHOD, 'foo');

		$this->assertFalse($config->isMethod('foo'));
	}

	public function testIsMime() {
		$config = new HttpConfig();

		$this->assertTrue($config->isMime($config::MIME_PLAIN));
		$this->assertTrue($config->isMime($config::MIME_HTML));
		$this->assertTrue($config->isMime($config::MIME_XHTML));
		$this->assertTrue($config->isMime($config::MIME_XML));
		$this->assertTrue($config->isMime($config::MIME_FORM));
		$this->assertTrue($config->isMime($config::MIME_JSON));
		$this->assertFalse($config->isMime('foo'));

		$config->addRule($config::RULE_MIME, 'foo');

		$this->assertTrue($config->isMime('foo'));

		$config->removeRule($config::RULE_MIME, 'foo');

		$this->assertFalse($config->isMime('foo'));
	}

	public function testIsEncoding() {
		$config = new HttpConfig();

		$this->assertTrue($config->isEncoding($config::ENCODING_UTF8));
		$this->assertFalse($config->isEncoding('foo'));

		$config->addRule($config::RULE_ENCODING, 'foo');

		$this->assertTrue($config->isEncoding('foo'));

		$config->removeRule($config::RULE_ENCODING, 'foo');

		$this->assertFalse($config->isEncoding('foo'));
	}

	public function testIsCode() {
		$config = new HttpConfig();

		$this->assertTrue($config->isCode($config::CODE_OK));
		$this->assertTrue($config->isCode($config::CODE_NO_CONTENT));
		$this->assertTrue($config->isCode($config::CODE_MOVED_PERMANENT));
		$this->assertTrue($config->isCode($config::CODE_FOUND));
		$this->assertTrue($config->isCode($config::CODE_REDIRECT));
		$this->assertTrue($config->isCode($config::CODE_MOVED_TEMPORARY));
		$this->assertTrue($config->isCode($config::CODE_NOT_VALID));
		$this->assertTrue($config->isCode($config::CODE_NOT_AUTH));
		$this->assertTrue($config->isCode($config::CODE_NOT_FOUND));
		$this->assertTrue($config->isCode($config::CODE_GONE));
		$this->assertTrue($config->isCode($config::CODE_ERROR));
		$this->assertTrue($config->isCode($config::CODE_UNAVAILABLE));
		$this->assertFalse($config->isCode('foo'));

		$config->addRule($config::RULE_CODE, 'foo');

		$this->assertTrue($config->isCode('foo'));

		$config->removeRule($config::RULE_CODE, 'foo');

		$this->assertFalse($config->isCode('foo'));
	}

	public function testIsRedirectCode() {
		$config = new HttpConfig();

		$this->assertFalse($config->isRedirectCode($config::CODE_OK));
		$this->assertFalse($config->isRedirectCode($config::CODE_NO_CONTENT));
		$this->assertTrue($config->isRedirectCode($config::CODE_MOVED_PERMANENT));
		$this->assertTrue($config->isRedirectCode($config::CODE_FOUND));
		$this->assertTrue($config->isRedirectCode($config::CODE_REDIRECT));
		$this->assertTrue($config->isRedirectCode($config::CODE_MOVED_TEMPORARY));
		$this->assertFalse($config->isRedirectCode($config::CODE_NOT_VALID));
		$this->assertFalse($config->isRedirectCode($config::CODE_NOT_AUTH));
		$this->assertFalse($config->isRedirectCode($config::CODE_NOT_FOUND));
		$this->assertFalse($config->isRedirectCode($config::CODE_GONE));
		$this->assertFalse($config->isRedirectCode($config::CODE_ERROR));
		$this->assertFalse($config->isRedirectCode($config::CODE_UNAVAILABLE));

		$config->removeRule($config::RULE_REDIRECT_CODE, $config::CODE_MOVED_PERMANENT);

		$this->assertFalse($config->isRedirectCode($config::CODE_MOVED_PERMANENT));

		$config->addRule($config::RULE_REDIRECT_CODE, $config::CODE_OK);

		$this->assertTrue($config->isRedirectCode($config::CODE_OK));
		$this->assertFalse($config->isRedirectCode('foo'));

		$config->addRule($config::RULE_REDIRECT_CODE, 'foo');

		$this->assertTrue($config->isRedirectCode('foo'));

		$config->removeRule($config::RULE_REDIRECT_CODE, 'foo');

		$this->assertFalse($config->isRedirectCode('foo'));
	}


	public function testGetCodeHeader() {
		$config = new HttpConfig();

		$this->assertEquals($config->getCodeHeader($config::CODE_OK), 'HTTP/1.1 200 OK');
		$this->assertEquals($config->getCodeHeader($config::CODE_NO_CONTENT), 'HTTP/1.1 204 No Content');
		$this->assertEquals($config->getCodeHeader($config::CODE_MOVED_PERMANENT), 'HTTP/1.1 301 Moved Permanently');
		$this->assertEquals($config->getCodeHeader($config::CODE_FOUND), 'HTTP/1.1 302 Found');
		$this->assertEquals($config->getCodeHeader($config::CODE_REDIRECT), 'HTTP/1.1 303 See Other');
		$this->assertEquals($config->getCodeHeader($config::CODE_MOVED_TEMPORARY), 'HTTP/1.1 307 Temporary Redirect');
		$this->assertEquals($config->getCodeHeader($config::CODE_NOT_VALID), 'HTTP/1.1 400 Bad Request');
		$this->assertEquals($config->getCodeHeader($config::CODE_NOT_AUTH), 'HTTP/1.1 403 Forbidden');
		$this->assertEquals($config->getCodeHeader($config::CODE_NOT_FOUND), 'HTTP/1.1 404 Not Found');
		$this->assertEquals($config->getCodeHeader($config::CODE_GONE), 'HTTP/1.1 410 Gone');
		$this->assertEquals($config->getCodeHeader($config::CODE_ERROR), 'HTTP/1.1 500 Internal Server Error');
		$this->assertEquals($config->getCodeHeader($config::CODE_UNAVAILABLE), 'HTTP/1.1 503 Service Unavailable');

		$config->addLink($config::LINK_CODE_HEADER, 'foo', 'bar');

		$this->assertEquals($config->getCodeHeader('foo'), 'bar');

		$config->removeLink($config::LINK_CODE_HEADER, 'foo');
	}

	public function testGetCodeMessage() {
		$config = new HttpConfig();

		$this->assertEquals($config->getCodeMessage($config::CODE_OK), '200 - OK');
		$this->assertEquals($config->getCodeMessage($config::CODE_NO_CONTENT), '204 - No Content');
		$this->assertEquals($config->getCodeMessage($config::CODE_MOVED_PERMANENT), '301 - Moved Permanently');
		$this->assertEquals($config->getCodeMessage($config::CODE_FOUND), '302 - Found');
		$this->assertEquals($config->getCodeMessage($config::CODE_REDIRECT), '303 - See Other');
		$this->assertEquals($config->getCodeMessage($config::CODE_MOVED_TEMPORARY), '307 - Temporary Redirect');
		$this->assertEquals($config->getCodeMessage($config::CODE_NOT_VALID), '400 - Bad Request');
		$this->assertEquals($config->getCodeMessage($config::CODE_NOT_AUTH), '403 - Forbidden');
		$this->assertEquals($config->getCodeMessage($config::CODE_NOT_FOUND), '404 - Page not found');
		$this->assertEquals($config->getCodeMessage($config::CODE_GONE), '410 - Gone');
		$this->assertEquals($config->getCodeMessage($config::CODE_ERROR), '500 - Internal Server Error');
		$this->assertEquals($config->getCodeMessage($config::CODE_UNAVAILABLE), '503 - Service Unavailable');

		$config->addLink($config::LINK_CODE_MESSAGE, 'foo', 'bar');

		$this->assertEquals($config->getCodeMessage('foo'), 'bar');

		$config->removeLink($config::LINK_CODE_MESSAGE, 'foo');
	}

	public function testGetMimeBody() {
		$config = new HttpConfig();

		$this->assertEquals($config->getMimeBody($config::CODE_OK, $config::MIME_PLAIN), '200 - OK');
		$this->assertEquals($config->getMimeBody($config::CODE_OK, $config::MIME_HTML), '<!DOCTYPE html><html><head><title>200 - OK</title></head><body><p>200 - OK</p></body></html>');
		$this->assertEquals($config->getMimeBody($config::CODE_REDIRECT, $config::MIME_PLAIN), '303 - See Other');
		$this->assertEquals($config->getMimeBody($config::CODE_REDIRECT, $config::MIME_PLAIN, '/foo/bar/baz'), '303 - See Other: /foo/bar/baz');
		$this->assertEquals($config->getMimeBody($config::CODE_REDIRECT, $config::MIME_HTML), '<!DOCTYPE html><html><head><title>303 - See Other</title></head><body><p>303 - See Other</p></body></html>');
		$this->assertEquals($config->getMimeBody($config::CODE_REDIRECT, $config::MIME_HTML, '/foo/bar/baz'), '<!DOCTYPE html><html><head><title>303 - See Other</title></head><body><p>303 - See Other: <a href="/foo/bar/baz">/foo/bar/baz</a></p></body></html>');
	}

	public function testGetMimePayloadParser() {
		$config = new HttpConfig();

		$this->assertEquals('', $config->getMimePayloadParser($config::MIME_PLAIN));
		$this->assertEquals('', $config->getMimePayloadParser($config::MIME_HTML));
		$this->assertEquals('', $config->getMimePayloadParser($config::MIME_XHTML));
		$this->assertEquals('', $config->getMimePayloadParser($config::MIME_XML));
		$this->assertEquals('\\lola\\io\\http\\payload\\JSONPayloadParser', $config->getMimePayloadParser($config::MIME_JSON));
		$this->assertEquals('\\lola\\io\\http\\payload\\FormPayloadParser', $config->getMimePayloadParser($config::MIME_FORM));
	}


	public function testHasRule() {
		$config = new HttpConfig();

		$this->assertTrue($config->hasRule($config::RULE_PROTOCOL, $config::PROTOCOL_HTTP));
		$this->assertFalse($config->hasRule($config::RULE_PROTOCOL, 'foo'));
	}

	public function testAddRule() {
		$config = new HttpConfig();

		$this->assertEquals($config->addRule($config::RULE_PROTOCOL, 'foo'), $config);
		$this->assertTrue($config->hasRule($config::RULE_PROTOCOL, 'foo'));
	}

	public function testRemoveRule() {
		$config = new HttpConfig();

		$this->assertTrue($config->hasRule($config::RULE_PROTOCOL, $config::PROTOCOL_HTTP));
		$this->assertEquals($config->removeRule($config::RULE_PROTOCOL, $config::PROTOCOL_HTTP), $config);
		$this->assertFalse($config->hasRule($config::RULE_PROTOCOL, $config::PROTOCOL_HTTP));
	}


	public function testHasLink() {
		$config = new HttpConfig();

		$this->assertTrue($config->hasLink($config::LINK_CODE_HEADER, $config::CODE_OK));
		$this->assertFalse($config->hasLink($config::LINK_CODE_HEADER, 'foo'));
	}

	public function testAddLink() {
		$config = new HttpConfig();

		$this->assertEquals($config->addLink($config::LINK_CODE_HEADER, 'foo', 'bar'), $config);
		$this->assertTrue($config->hasLink($config::LINK_CODE_HEADER, 'foo'));
	}

	public function testRemoveLink() {
		$config = new HttpConfig();

		$this->assertTrue($config->hasLink($config::LINK_CODE_HEADER, $config::CODE_OK));
		$this->assertEquals($config->removeLink($config::LINK_CODE_HEADER, $config::CODE_OK), $config);
		$this->assertFalse($config->hasLink($config::LINK_CODE_HEADER, $config::CODE_OK));
	}
}
