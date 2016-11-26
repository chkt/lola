<?php

namespace lola\io\http\payload;



interface IHttpPayload {

	public function isValid() : bool;

	public function get() : array;

	public function set(array $payload) : IHttpPayload;
}
