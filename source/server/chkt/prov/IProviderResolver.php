<?php

namespace chkt\prov;



interface IProviderResolver {
	
	public function& resolve($id, Array& $instances, Callable $factory);
}
