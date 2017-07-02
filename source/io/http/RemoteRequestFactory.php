<?php

namespace lola\io\http;



final class RemoteRequestFactory
extends AHttpMessageFactory
{

	protected function _produceInstance() : IHttpMessage {
		$start = implode(' ', [
			filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
			filter_input(INPUT_SERVER,'REQUEST_URI'),
			filter_input(INPUT_SERVER, 'SERVER_PROTOCOL')
		]);

		$headers = [];

		foreach(getallheaders() as $name => $content) $headers[$name] = [$content];

		$handle = fopen('php://input', 'r');
		$body = stream_get_contents($handle);

		fclose($handle);

		return new HttpMessage($start, $headers, $body);
	}
}
