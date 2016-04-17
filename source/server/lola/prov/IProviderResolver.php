<?php

namespace lola\prov;



interface IProviderResolver {
	
	public function& resolve($id, Array& $instances, Callable $factory);
}
