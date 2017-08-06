<?php

namespace lola\type\data;



interface ITreeMutator
extends ITreeAccessor
{

	public function merge(ITreeAccessor $a, ITreeAccessor $b) : ITreeMutator;

	public function mergeEq(ITreeAccessor $b) : ITreeMutator;


	public function filter(ITreeAccessor $tree, array $keys) : ITreeMutator;

	public function filterSelf(array $keys) : ITreeMutator;


	public function select(ITreeAccessor $tree, string $key) : ITreeMutator;

	public function selectSelf(string $key) : ITreeMutator;


	public function insert(ITreeAccessor $tree, string $key) : ITreeMutator;
}
