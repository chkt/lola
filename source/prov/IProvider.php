<?php

namespace lola\prov;



interface IProvider
{

	public function& using(string $id);

	public function set(string $id, $ins) : IProvider;

	public function reset(string $id) : IProvider;

	public function configure(string $id, callable $fn) : IProvider;

	public function clearConfig(string $id) : IProvider;
}
