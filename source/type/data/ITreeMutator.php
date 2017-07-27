<?php

namespace lola\type\data;



interface ITreeMutator
extends ITreeAccessor
{

	public function filter(ITreeAccessor $tree, array $keys) : ITreeMutator;

	public function filterEq(array $keys) : ITreeMutator;


	public function merge(ITreeAccessor $a, ITreeAccessor $b) : ITreeMutator;

	public function mergeEq(ITreeAccessor $b) : ITreeMutator;
}
