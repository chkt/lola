<?php

namespace lola\io\http;

use lola\io\http\IHttpReplyResource;



class HttpReplyResource
implements IHttpReplyResource
{

	const VERSION = '0.5.0';



	public function sendHeader(string $header) : IHttpReplyResource {
		header($header);

		return $this;
	}

	public function sendCookie(string $name, string $value, int $expires) : IHttpReplyResource {
		setCookie($name, $value, $expires);

		return $this;
	}

	public function sendBody(string $body) : IHttpReplyResource {
		$handle = fopen('php://output', 'r+');

		fwrite($handle, $body);
		fclose($handle);

		return $this;
	}
}
