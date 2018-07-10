<?php

require_once 'MockDriver.php';

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpConfig;
use lola\io\http\HttpReply;
use test\io\http\MockDriver;



class HttpReplyTest
extends TestCase
{

	private $_driver;


	public function __construct() {
		parent::__construct();

		$this->_driver = new MockDriver();
		$this->_driver
			->useConfig()
			->addRule(IHttpConfig::RULE_ENCODING, 'iso-8859-1');
	}


	public function testUseRequest() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($this->_driver->getRequest(), $reply->useRequest());
	}

	public function testUseCookies() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($this->_driver->useCookies(), $reply->useCookies());
	}


	public function testGetCode() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getCode(), '200');
	}

	public function testSetCode() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->setCode('404'), $reply);
		$this->assertEquals($reply->getCode(), '404');
	}

	public function testGetCodeHeader() {
		$reply = new HttpReply($this->_driver);

		$reply->setCode(IHttpConfig::CODE_NOT_FOUND);

		$this->assertEquals('HTTP/1.1 404 Not Found', $reply->getCodeHeader());
	}

	public function testGetCodeMessage() {
		$reply = new HttpReply($this->_driver);

		$reply->setCode(IHttpConfig::CODE_NOT_FOUND);

		$this->assertEquals('404 - Page not found', $reply->getCodeMessage());
	}


	public function testGetMime() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getMime(), 'text/plain');
	}

	public function testSetMime() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->setMime('text/html'), $reply);
		$this->assertEquals($reply->getMime(), 'text/html');
	}

	public function testGetEncoding() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getEncoding(), 'utf-8');
	}

	public function testSetEncoding() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->setEncoding('iso-8859-1'), $reply);
		$this->assertEquals($reply->getEncoding(), 'iso-8859-1');
	}

	public function testIsRedirect() {
		$reply = new HttpReply($this->_driver);

		$this->assertFalse($reply->isRedirect());

		$reply->setCode('301');

		$this->assertTrue($reply->isRedirect());

		$reply->setCode('404');

		$this->assertFalse($reply->isRedirect());
	}

	public function testGetRedirectTarget() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getRedirectTarget(), '');
	}

	public function testSetRedirectTarget() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->setRedirectTarget('/path/to/resource'), $reply);
		$this->assertEquals($reply->getRedirectTarget(), '/path/to/resource');
	}

	public function testHasHeader() {
		$reply = new HttpReply($this->_driver);

		$this->assertTrue($reply->hasHeader('Content-Type'));
		$this->assertFalse($reply->hasHeader('Location'));
		$this->assertFalse($reply->hasHeader('Header-1'));
	}

	public function testGetHeader() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getHeader('Content-Type'), 'text/plain;charset=utf-8');
		$this->assertEquals($reply->getHeader('Location'), false);
		$this->assertEquals($reply->getHeader('Header-1'), false);
	}

	public function testSetHeader() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->setHeader('Content-Type', 'text/html;charset=iso-8859-1'), $reply);
		$this->assertEquals($reply->getHeader('Content-Type'), 'text/html;charset=iso-8859-1');
		$this->assertEquals($reply->getMime(), 'text/html');
		$this->assertEquals($reply->getEncoding(), 'iso-8859-1');

		$this->assertEquals($reply->setHeader('Location', '/path/to/resource'), $reply);
		$this->assertEquals($reply->getHeader('Location'), '/path/to/resource');
		$this->assertEquals($reply->getRedirectTarget(), '/path/to/resource');

		$this->assertEquals($reply->setHeader('Header-1', 'foo'), $reply);
		$this->assertTrue($reply->hasHeader('Header-1'));
		$this->assertEquals($reply->getHeader('Header-1'), 'foo');
	}

	public function testResetHeader() {
		$reply = new HttpReply($this->_driver);

		$reply->setHeader('Content-Type', 'text/html;charset=iso-8859-1');
		$this->assertEquals($reply->resetHeader('Content-Type'), $reply);
		$this->assertEquals($reply->getMime(), 'text/plain');
		$this->assertEquals($reply->getEncoding(), 'utf-8');
		$this->assertEquals($reply->getHeader('Content-Type'), 'text/plain;charset=utf-8');
		$this->assertTrue($reply->hasHeader('Content-Type'));

		$reply->setHeader('Location', '/path/to/resource');
		$this->assertEquals($reply->resetHeader('Location'), $reply);
		$this->assertEquals($reply->getRedirectTarget(), '');
		$this->assertEquals($reply->getHeader('Location'), '');
		$this->assertFalse($reply->hasHeader('Location'));

		$reply->setHeader('Header-1', 'foo');
		$this->assertEquals($reply->resetHeader('Header-1'), $reply);
		$this->assertEquals($reply->getHeader('Header-1'), '');
		$this->assertFalse($reply->hasHeader('Header-1'));
	}

	public function testGetHeaders() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getHeaders(), []);

		$reply->setHeader('Header-1', 'foo');

		$this->assertEquals($reply->getHeaders(), [
			'Header-1' => 'foo'
		]);

		$reply->setHeader('Header-2', 'bar');

		$this->assertEquals($reply->getHeaders(), [
			'Header-1' => 'foo',
			'Header-2' => 'bar'
		]);
	}

	public function testGetBody() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->getBody(), '');
	}

	public function testSetBody() {
		$reply = new HttpReply($this->_driver);

		$this->assertEquals($reply->setBody('foo'), $reply);
		$this->assertEquals($reply->getBody(), 'foo');
	}

	public function testSetBodyFromOB() {
		$reply = new HttpReply($this->_driver);

		ob_start();

		print 'foo';

		$this->assertEquals($reply->setBodyFromOB(), $reply);
		$this->assertEquals($reply->getBody(), 'foo');

		ob_end_clean();
	}


	public function testSend() {
		$this->_driver->setReplyCallback(function(HttpReply $reply) {
			$this->assertEquals($reply->getCode(), '404');
			$this->assertEquals($reply->getMime(), 'text/html');
			$this->assertEquals($reply->getEncoding(), 'iso-8859-1');
			$this->assertEquals($reply->getBody(), 'foo');
		});

		$this->_driver
			->getReply()
			->setCode('404')
			->setMime('text/html')
			->setEncoding('iso-8859-1')
			->setBody('foo')
			->send();
	}

	public function testSendOB() {
		$this->_driver->setReplyCallback(function(HttpReply $reply) {
			$this->assertEquals($reply->getCode(), '404');
			$this->assertEquals($reply->getMime(), 'text/html');
			$this->assertEquals($reply->getEncoding(), 'iso-8859-1');
			$this->assertEquals($reply->getBody(), 'bar');
		});

		ob_start();

		print 'bar';

		$this->_driver
			->getReply()
			->setCode('404')
			->setMime('text/html')
			->setEncoding('iso-8859-1')
			->sendOB();

		ob_end_clean();
	}
}
