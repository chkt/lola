<?php

namespace chkt\engine;

use chkt\engine\NoPathException;



class StateEngine 
{
	
	private $_states = null;
	
	
	public function __construct(Array $states) {
		$this->_states = $states;
	}
	
	
	public function isValidState($state) {
		return array_key_exists($state, $this->_states);
	}
	
	
	private function _buildPath(Array $origin, $target) {		
		while (array_key_exists($target, $origin)) {
			$res[] = $target;
			$target = $origin[$target];
		}
		
		return array_reverse($res);
	}
	
	private function _spliceNext(Array& $openSet, Array $fScore) {
		$name = '';
		$pos = 0;
		$score = PHP_INT_MAX;
		
		foreach ($openSet as $index => $item) {
			if ($fScore[$name] >= $score) continue;
			
			$name = $item;
			$pos = $index;
			$score = $fScore[$name];
		}
		
		array_splice($openSet, $pos, 1);
		
		return $name;
	}
	
	private function _getPath($source, $target) {
		$states = $this->_states;
		
		$closedSet = [];
		$openSet = [ $source ];
		
		$shortPath = [];
		
		$gScore = [ $source => 0 ];
		$fScore = [ $source => abs($states[$target]['weight'] - $states[$source]['weight']) ];
		
		while (!empty($openSet)) {
			$current = $this->_spliceNext($openSet, $fScore);
			
			if ($current === $target) return $this->_buildPath($shortPath, $target);
			
			$closedSet[] = $current;
			
			if (!array_key_exists('transition', $states[$current])) continue;
			
			foreach ($states[$current]['transition'] as $name => $action) {
				if (array_key_exists($name, $closedSet)) continue;
				
				$score = $gScore[$current] + abs($states[$name]['weight'] - $states[$current]['weight']);
				
				if (!array_key_exists($name, $openSet)) $openSet[] = $name;
				else if ($score >= $gScore[$name]) continue;
				
				$shortPath[$name] = $current;
				$gScore[$name] = $score;
				$fScore[$name] = $score + abs($states[$target]['weight'] - $states[$name]['weight']);
			}
		}
		
		throw new NoPathException();
	}
	
	
	public function enter($action, AStateEngineModel& $model) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		$action .= 'Action';
		
		if (!method_exists($this, $action)) throw new \ErrorException();
		
		return $this->$action($model);
	}
	
	
	private function _transition(AStateEngineModel& $model, $state) {
		$states = $this->_states;
		$current = $states[$model->getState()];
		$model->setState($state);
			
		if (array_key_exists('exit', $current)) $this->enter($current['exit'], $model);

		$action = $current['transition'][$state];

		if (!empty($action)) $this->enter($action, $model);

		$next = $states[$action];

		if (array_key_exists('enter', $next)) $this->enter($next['enter'], $model);
		
		return $this;
	}
	
	
	public function transition(AStateEngineModel& $model, $next) {
		if (
			!$this->isValidState($model->getState()) ||
			!$this->isValidState($next)
		) throw new \ErrorException();
		
		$states = $this->_states;
		$current = $states[ $model->getState() ];
		
		$path = $this->_getPath($model->getState(), $next);
		$model->deferUpdates();
		
		for ($i = 0, $state = $path[0]; !is_null($state); $state = $path[++$i]) {
			$this->_transition($model, $state);
			
			if (
				is_null($path[$i + 1]) &&
				array_key_exists('transition', $current) && count($current['transition'] !== 1) &&
				array_key_exists('forward', $current) && $current['forward'] === true
			) $path[] = array_keys($current['transition'])[0];
		}		
		
		$model->update();
		
		return $this;
	}
}
