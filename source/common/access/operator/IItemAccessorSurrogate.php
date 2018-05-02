<?php

namespace lola\common\access\operator;

use eve\common\factory\ISimpleFactory;
use eve\common\projection\operator\IProjectableSurrogate;
use eve\inject\IInjectableIdentity;



interface IItemAccessorSurrogate
extends ISimpleFactory, IInjectableIdentity, IProjectableSurrogate, IItemAccessorComposition
{

}
