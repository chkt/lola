<?php

namespace lola\io\http;


final class RemoteReplyFactory
extends AHttpMessageFactory
{

	protected function _produceInstance() : IHttpMessage {
		$start = 'HTTP/1.1 200 OK';
		$header = [
			IHttpMessage::HEADER_CONTENT_TYPE => [ 'text/plain;charset=utf-8' ]
		];

		return new HttpMessage($start, $header);
	}
}
