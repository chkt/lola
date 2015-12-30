<?php

namespace chkt\type;



interface IInjectable {
	static public function getDependencyConfig($id);
}
