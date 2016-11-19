<?php

require_once('MockDriver.php');

use PHPUnit\Framework\TestCase;

use lola\io\http\HttpCookies;
use test\io\http\MockDriver;



class HttpCookiesTest
extends TestCase
{

	private $_driver = null;


	public function __construct() {
		parent::__construct();

		$this->_driver = new MockDriver();
	}

	public function testHasChanges() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertFalse($cookies->hasChanges());

		$cookies->set('y', 'x');

		$this->assertTrue($cookies->hasChanges());
	}

	public function testGetChangedNames() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertEquals($cookies->getChangedNames(), []);

		$cookies
			->set('x', 'foo')
			->set('y', 'bar');

		$this->assertEquals($cookies->getChangedNames(), ['x', 'y']);
	}

	public function testHasCookie() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertTrue($cookies->hasCookie('a'));
		$this->assertTrue($cookies->hasCookie('b'));
		$this->assertFalse($cookies->hasCookie('c'));

		$cookies->set('c', 'baz');

		$this->assertTrue($cookies->hasCookie('c'));
	}

	public function testIsUpdated() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertFalse($cookies->isUpdated('a'));
		$this->assertFalse($cookies->isUpdated('b'));

		$cookies->set('a', 'baz');

		$this->assertTrue($cookies->isUpdated('a'));
		$this->assertFalse($cookies->isUpdated('b'));
	}

	public function testIsRemoved() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertFalse($cookies->isRemoved('a'));
		$this->assertFalse($cookies->isRemoved('b'));

		$cookies->reset('a');

		$this->assertTrue($cookies->isRemoved('a'));
		$this->assertFalse($cookies->isRemoved('b'));
	}

	public function testIsSecure() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertFalse($cookies->isSecure('a'));
		$this->assertFalse($cookies->isSecure('b'));

		$cookies->set('a', 'baz', 0, [
			'secure' => true
		]);

		$this->assertTrue($cookies->isSecure('a'));
		$this->assertFalse($cookies->isSecure('b'));
	}

	public function testIsHttpOnly() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertFalse($cookies->isHttpOnly('a'));
		$this->assertFalse($cookies->isHttpOnly('b'));

		$cookies->set('a', 'baz', 0, [
			'http' => true
		]);

		$this->assertTrue($cookies->isHttpOnly('a'));
		$this->assertFalse($cookies->isHttpOnly('b'));
	}

	public function testGetValue() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertEquals($cookies->getValue('a'), 'foo');
		$this->assertEquals($cookies->getValue('b'), 'bar');

		$cookies->set('a', 'baz');

		$this->assertEquals($cookies->getValue('a'), 'baz');
		$this->assertEquals($cookies->getValue('b'), 'bar');

		$cookies->reset('a');

		$this->assertEquals($cookies->getValue('a'), '');
		$this->assertEquals($cookies->getValue('b'), 'bar');
	}

	public function testGetExpiry() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertEquals($cookies->getExpiry('a'), 0);
		$this->assertEquals($cookies->getExpiry('b'), 0);

		$cookies->set('a', 'baz', 1234);

		$this->assertEquals($cookies->getExpiry('a'), 1234);
		$this->assertEquals($cookies->getExpiry('b'), 0);

		$cookies->reset('a');

		$this->assertEquals($cookies->getExpiry('a'), 0);
		$this->assertEquals($cookies->getExpiry('b'), 0);
	}

	public function testGetPath() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertEquals($cookies->getPath('a'), '');
		$this->assertEquals($cookies->getPath('b'), '');

		$cookies->set('a', 'baz', 0, [
			'path' => '/path/to/resource'
		]);

		$this->assertEquals($cookies->getPath('a'), '/path/to/resource');
		$this->assertEquals($cookies->getPath('b'), '');

		$cookies->reset('a');

		$this->assertEquals($cookies->getPath('a'), '');
		$this->assertEquals($cookies->getPath('b'), '');
	}

	public function testGetDomain() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertEquals($cookies->getDomain('a'), '');
		$this->assertEquals($cookies->getDomain('b'), '');

		$cookies->set('a', 'baz', 0, [
			'domain' => 'sub.domain.tld'
		]);

		$this->assertEquals($cookies->getDomain('a'), 'sub.domain.tld');
		$this->assertEquals($cookies->getDomain('b'), '');

		$cookies->reset('a');

		$this->assertEquals($cookies->getDomain('a'), '');
		$this->assertEquals($cookies->getDomain('b'), '');
	}

	public function testSet() {
		$cookies = new HttpCookies($this->_driver);

		$this->assertFalse($cookies->hasCookie('c'));
		$this->assertEquals($cookies->set('c', 'baz'), $cookies);
		$this->assertTrue($cookies->hasCookie('c'));

		$cookies->set('c', 'quux', 1234, [
			'http' => true,
			'secure' => true,
			'path' => '/path/to/resource',
			'domain' => 'sub.domain.tld'
		]);

		$this->assertEquals($cookies->getValue('c'), 'quux');
		$this->assertEquals($cookies->getExpiry('c'), 1234);
		$this->assertTrue($cookies->isHttpOnly('c'));
		$this->assertTrue($cookies->isSecure('c'));
		$this->assertEquals($cookies->getDomain('c'), 'sub.domain.tld');
		$this->assertEquals($cookies->getPath('c'), '/path/to/resource');
	}

	public function testReset() {
		$cookies = new HttpCookies($this->_driver);

		$cookies->set('c', 'quux', 1234, [
			'http' => true,
			'secure' => true,
			'path' => '/path/to/resource',
			'domain' => 'sub.domain.tld'
		]);

		$this->assertEquals($cookies->reset('c'), $cookies);
		$this->assertEquals($cookies->getValue('c'), '');
		$this->assertEquals($cookies->getExpiry('c'), 0);
		$this->assertFalse($cookies->isHttpOnly('c'));
		$this->assertFalse($cookies->isSecure('c'));
		$this->assertEquals($cookies->getDomain('c'), '');
		$this->assertEquals($cookies->getPath('c'), '');
	}
}
