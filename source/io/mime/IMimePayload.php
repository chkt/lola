<?php

namespace lola\io\mime;



interface IMimePayload {

	public function isValid() : bool;

	public function get() : array;

	public function set(array $payload) : IMimePayload;
}
