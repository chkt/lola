<?php

namespace chkt\route;

use \chkt\route\Route;
use \chkt\route\RouteCanceledException;



class Router {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.5';

	
	/**
	 * The route paths
	 * @var array
	 */
	protected $_path    = null;
	/**
	 * The memoized route path segments
	 * @var array
	 */
	protected $_segment	= null;
	
	/**
	 * The route controllers
	 * @var array
	 */
	protected $_ctrl   = null;
	/**
	 * The route actions
	 * @var array
	 */
	protected $_action = null;
	
	/**
	 * The route views
	 * @var array
	 */
	protected $_view   = null;
	
	/**
	 * The route hierarchy trees
	 * @var array
	 */
	protected $_tree   = null;
	/**
	 * The route data strings
	 * @var array
	 */
	protected $_data   = null;
	/**
	 * The memoized route data hashes
	 * @var array
	 */
	protected $_hash   = null;
	
	/**
	 * The stack of resolved routes
	 * @var array
	 */
	protected $_routeStack  = null;
	/**
	 * The stack of resolved return values
	 * @var array
	 */
	protected $_returnStack = null;
	
	
	/**
	 * Returns an <code>Array</code> containing the <code>$url</code> path and params
	 * @param $url The url
	 * @return array
	 */
	static private function _disassemblePath($url) {
		$path   = parse_url($url, PHP_URL_PATH);
		$params = [];
		
		parse_str(parse_url($url, PHP_URL_QUERY), $params);		
		
		return [$path, $params];
	}
	
	/**
	 * Returns a string representing an expanded version of <code>$segs</code>
	 * @param array $segs   The route segments
	 * @param array $expand The expansion dictionary
	 * @return string
	 */
	static private function _expandPath(Array $segs, Array $expand = []) {		
		$res = '';
		
		foreach ($segs as $seg) {			
			$index = strrpos($seg, ':');
			
			if ($index === false) {
				$res .= '/' . $seg;
				continue;
			}
			
			$name = substr($seg, $index + 1);
			$opt  = strrpos('?*', substr($name, -1)) !== false;
			
			if ($opt) $name = substr($name, 0, strlen($name) - 1);
			
			if (empty($name)) {
				$res .= '/' . substr($seg, 0, $index);
				continue;
			}
			
			$list = substr($seg, 0, $index);
			$val  = !empty($list) ? explode(',', $list) : [];
			
			switch (count($val)) {
				case 0 :
					if (array_key_exists($name, $expand)) $res .= '/' . $expand[$name];
					else if (!$opt) $res .= '/%' . $name . '%';
					
					break;
					
				case 1 :
					$res .= '/' . $val[0];
					
					break;
				
				default :
					if (array_key_exists($name, $expand) && in_array($expand[$name], $val)) $res .= '/' . $expand[$name];
					else if (!$opt) $res .= '/%' . $name . '%';
			}
		}
		
		return $res;
	}
	
	/**
	 * Returns the flags representing the match between <code>$pseg</code> and <code>$rseg</code>
	 * @param  string $pseg The path segment
	 * @param  string $rseg The route segment
	 * @param &string $name The segment name
	 * @return int
	 */
	static private function _matchSegment($pseg, $rseg, &$name) {
		$index = strrpos($rseg, ':');
		
		if ($index === false) return $pseg === $rseg ? 0x1 : 0x0;
		
		$name = substr($rseg, $index + 1);
		$char = substr($name, -1);
		
		$flag =                                    0x2  |
			(strpos('?*', $char) === false ? 0x0 : 0x4) |
			(strpos('*+', $char) === false ? 0x0 : 0x8);
		
		if ($flag & 0xC) $name = substr($name, 0, strlen($name) - 1);
				
		if (
			!empty($pseg) && 
			($index === 0 || in_array($pseg, explode(',', substr($rseg, 0, $index))))
		) $flag |= 0x1;
		
		return $flag;
	}
	
	/**
	 * Returns <code>true</code> if <code>$route</code> matches <code>$path</code>, <code>false</code> otherwise
	 * @param  array $route The route segments
	 * @param  array $path  The path segments
	 * @param &array $param The route params 
	 * @return boolean
	 * @throws \ErrorException if encountering unindentified match
	 */
	static private function _matchRoute(array $route, array $path, array &$param) {		//FIX guarantee that segments of type "foo:" will be treated as unnamed matches
		$rlen = count($route);
		$plen = count($path);
		
		$capture = '';

		for ($i = 0, $j = 0; $i < $rlen; $i += 1, $j += 1) {
			$pseg = $j < $plen ? $path[$j] : '';
			$name = '';

			$match = self::_matchSegment($pseg, $route[$i], $name);
			
			switch ($match) {
				case 0x6 : case 0xE :
					if (empty($path[$i])) break;
					
				case 0x0 : case 0x2 : case 0xA :
					if (empty($capture) || $j > $plen - 2) return false;

					$param[$capture] .= '/' . $pseg;
					$i -= 1;
					
					continue 2;

				case 0x1 :
					break;
					
				case 0xB : case 0xF :
					if ($i === $rlen - 1 && $j < $plen) {
						$param[$name] = implode('/', array_slice($path, $j));

						return true;
					}

					$param[$name] = $path[$j];
					$capture = $name;
					
					continue 2;
					
				case 0x3 : case 0x7 :
					$param[$name] = $path[$j];
					
					break;
				
				default : throw new \ErrorException();
			}
			
			$capture = '';
		}

		return $j >= $plen;
	}
	
	
	
	static public function JSON($json) {
		//IMPLEMENT
	}
	
	
	/**
	 * Returns the <code>Array</code> of segments referenced by <code>$index</code>
	 * @param uint $index The route index
	 * @return array
	 */
	private function _getSegs($index) {
		if (empty($this->_segment[$index])) $this->_segment[$index] = !empty($this->_path[$index]) ? explode('/', trim($this->_path[$index], '/')) : [];
		
		return $this->_segment[$index];
	}
	
	/**
	 * Returns the <code>Array<code> of hash data referenced by <code>$index</code>
	 * @param uint $index  The route index
	 * @param string $path The route path
	 * @return array
	 */
	private function _getHash($index, $path = '') {
		if (!empty($this->_hash[$index])) return $this->_hash[$index];
		
		$res = [];
		parse_str($this->_data[$index], $res);
		
		if (!array_key_exists('id'  , $res)) $res['id']   = substr($this->_tree[$index], strrpos($this->_tree[$index], '|') + 1);
		if (!array_key_exists('path', $res)) $res['path'] = $path;
		
		$this->_hash[$index] = $res;
		
		return $res;
	}
	
	/**
	 * Returns <code>$limit</code> routes referenced by <code>$filter</code>
	 * @param string $filter The route filter
	 * @param uint   $limit  The route limit
	 * @return array
	 */
	private function _filterRoutes($filter, $limit = PHP_INT_MAX) {
		$ftree = explode('|', $filter);
		$flen  = count($ftree);
		
		$res = [];
		
		for ($r = 0, $rnum = count($this->_path), $n = 0; $r < $rnum && $n < $limit; ++$r) {
			$rtree = explode('|', $this->_tree[$r]);
			$rlen  = count($rtree);
			
			if ($rlen < $flen) continue;
			
			for ($i = 0; $i < $rlen; ++$i) {				
				$select = explode(',', $rtree[$i]);
				
				if ($i < $flen) {
					if (in_array($ftree[$i], $select)) continue;
					else continue 2;
				}
				
				break;
			}
			
			$res[] = $r;
			$n += 1;
		}
		
		return $res;
	}
	
	
	/**
	 * Creates an instance
	 */
	public function __construct() {
		$this->_path    = [];
		$this->_segment = [];
	
		$this->_ctrl   = [];
		$this->_action = [];

		$this->_view   = [];
	
		$this->_tree   = [];
		$this->_data   = [];
		$this->_hash   = [];
	
		$this->_routeStack  = [];
		$this->_returnStack = [];
	}
	
	
	/**
	 * Returns the stack of resolved routes
	 * @return array
	 */
	public function getStack() {
		$res = [];
		
		for ($i = 0, $l = count($this->_returnStack); $i < $l; ++$i) {
			$res[] = [
				'return' => $this->_returnStack[$i],
				'route'  => $this->_routeStack[$i]
			];
		}
		
		return $res;
	}
	
	/**
	 * Resets the stack of resolved routes
	 * @return Router
	 */
	public function resetStack() {
		$this->_returnStack = [];
		$this->_routeStack  = [];
		
		return $this;
	}
	
	
	/**
	 * Appends a route
	 * @param string $path   The route path
	 * @param string $ctrl   The controller name
	 * @param string $action The action name
	 * @param string $view   The view location
	 * @param string $tree   The filter tree representation
	 * @param string $data   The urlencoded data hash
	 * @return Router
	 * @throws \ErrorException if <code>$path</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$ctrl</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$action</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$view</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$tree</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$data</code> is not a <code>String</code>
	 */
	public function defineRoute($path, $ctrl, $action, $view, $tree, $data = '') {
		if (
			!is_string($path)   || empty($path)   ||
			!is_string($ctrl)   || empty($ctrl)   ||
			!is_string($action) || empty($action) ||
			!is_string($view)   || empty($view)   ||
			!is_string($tree)   || empty($tree)   ||
			!is_string($data)
		) throw new \ErrorException();
		
		$this->_path[]   = $path;
		$this->_ctrl[]   = $ctrl;
		$this->_action[] = $action;
		$this->_view[]   = $view;
		$this->_tree[]   = $tree;
		$this->_data[]   = $data;
		
		return $this;
	}
	
	/**
	 * Appends a route and enters it
	 * @param string $ctrl   The controller name
	 * @param string $action The action name
	 * @param string $view   The view location
	 * @param array  $params The route params
	 * @param array  $data   The route identity data
	 * @return Router
	 */
	public function defineAndEnter($ctrl, $action, $view, array $params = [], array $data = []) {
		$route = new Route($this, '', $params);
		$route
			->setCtrl($ctrl, $action)
			->setView($view)
			->setData($data);
		
		$this->_returnStack[] = $route->enter();
		$this->_routeStack[]  = $route;
		
		return $this;
	}
	
	
	/**
	 * Returns a <code>Route</code> referenced by <code>$filter</code>
	 * @param string $filter The hierarchical route tree filter
	 * @param array  $params The route params
	 * @return Route|null
	 * @throws \ErrorException if <code>$filter</code> is not a <code>String</code>
	 */
	public function findRoute($filter = '', array $params = []) {
		if (!is_string($filter)) throw new \ErrorException();
		
		$list = $this->_filterRoutes($filter, 1);
		
		if (empty($list)) return null;

		$index = $list[0];
		$path = self::_expandPath($this->_getSegs($index), $params);
		$route = new Route($this, $path, $params);

		return $route
			->setCtrl($this->_ctrl[$index], $this->_action[$index])
			->setView($this->_view[$index])
			->setData($this->_getHash($index, $path));
	}
	
	/**
	 * Returns <code>$limit</code> <code>Route</code>s referenced by <code>$filter</code>
	 * @param string $filter The hierarchical route tree filter
	 * @param array  $params The route params
	 * @param uint   $limit  The route limit
	 * @return array
	 * @throws \ErrorException if <code>$filter</code> is not a <code>String</code>
	 */
	public function findRoutes($filter = '', array $params = [], $limit = PHP_INT_MAX) {
		if (!is_string($filter) || !is_int($limit)) throw new \ErrorException();
		
		$list = $this->_filterRoutes($filter, $limit);
		$res = [];
		
		foreach ($list as $index) {
			$path = self::_expandPath($this->_getSegs($index), $params);
			$route = new Route($this, $path, $params);
			$res[] = $route
				->setCtrl($this->_ctrl[$index], $this->_action[$index])
				->setView($this->_view[$index])
				->setData($this->_getHash($index, $path));
		}
		
		return $res;
	}
	
	
	/**
	 * Returns the <code>Route</code> referenced by <code>$url</code>
	 * @param  string $url    The route url
	 * @param &uint   $offset The search offset
	 * @return Route|null
	 * @throws \ErrorException if <code>$url</code> is not a <code>String</code>
	 * @throws \ErrorException if <code>$offset</code> is not a <code>Uint</code>
	 */
	public function resolveRoute($url, &$offset = 0) {
		if (!is_string($url) || !is_int($offset) || $offset < 0) throw new \ErrorException();		
		
		list($path, $param) = self::_disassemblePath($url);
		
		if (is_null($path)) return null;
		
		$pseg = explode('/', trim($path, '/'));
		
		for ($r =& $offset, $l = count($this->_path); $r < $l; $r += 1) {
			$rseg   = $this->_getSegs($r);
			$rparam = [];
			
			if (!self::_matchRoute($rseg, $pseg, $rparam)) continue;
			
			$route = new Route($this, $path, array_merge($param, $rparam));
			
			return $route
				->setCtrl($this->_ctrl[$r], $this->_action[$r])
				->setView($this->_view[$r])
				->setData($this->_getHash($r, $path));
		}
		
		return null;
	}
	
	/**
	 * Enters a route referenced by <code>$path</code>
	 * @param string $path   The route path
	 * @param uint   $offset The search offset
	 * @return Router
	 */
	public function enterRoute($path, $offset = 0) {
		$route = $this->resolveRoute($path, $offset);
		
		if (!is_null($route)) {
			try {
				$this->_returnStack[] = $route->enter();
				$this->_routeStack[]  = $route;
			}
			catch (RouteCanceledException $ex) {
				TLog::logException($ex);
			}
			catch (Exception $ex) {
				throw $ex;
			}
		}
		
		return $this;
	}
	
	/**
	 * Enters <code>$limit</code> <code>Route</code>s referenced by <code>$path</code>
	 * @param string $path   The route path
	 * @param uint   $offset The search offset
	 * @param uint   $limit  The route limit
	 * @return Router
	 * @throws \ErrorException if <code>$offset</code> is not an <code>Uint</code>
	 * @throws \ErrorException if <code>$limit</code> is not an <code>Uint</code>
	 */
	public function enterRoutes($path, $offset = 0, $limit = PHP_INT_MAX) {
		if (
			!is_int($offset) || $offset < 0 ||
			!is_int($limit) || $limit < 0
		) throw new \ErrorException();
				
		for ($i = 0; $i < $limit; ++$i) {
			$route = $this->resolveRoute($path, $offset);
			
			if (is_null($route)) break;
			
			try {
				$this->_returnStack[] = $route->enter();
				$this->_returnRoute[] = $route;
			}
			catch (RouteCanceledException $ex) {
				continue;
			}
			catch (\Exception $ex) {
				throw $ex;
			}
			finally {
				$offset += 1;
			}
		}
		
		return $this;
	}
	
	
	/**
	 * Returns a filtered <em>JSON</em> representation of the instance
	 * @param string $filter The filter string
	 * @param array $expand  The expansion dictionary
	 * @return array
	 * @throws \ErrorException if <code>$filter</code> is not a <em>unambigious</em> filter
	 */
	public function toJSON($filter = '', array $expand = []) {
		if (!is_string($filter) || strpos($filter, ',')) throw new \ErrorException();
		
		$ftree = explode('|', $filter);
		$flen  = count($ftree);
		
		$res = [];
		
		for ($r = 0, $rnum = count($this->_path); $r < $rnum; ++$r) {
			$rtree = explode('|', $this->_tree[$r]);
			$rname = array_pop($rtree);
			$rlen  = count($rtree);
			
			if ($rlen < $flen) continue;
			
			$sourceList = [&$res];
			
			for ($i = 0; $i < $rlen; ++$i) {				
				$select = explode(',', $rtree[$i]);
				
				if ($i < $flen) {
					if (in_array($ftree[$i], $select)) continue;
					else continue 2;
				}
				
				$targetList = [];
				
				foreach ($select as $key) {
					foreach ($sourceList as &$source) {
						if (!array_key_exists($key, $source)) $source[$key] = [];
						
						$targetList[] =& $source[$key];
					}
				}
				
				$sourceList = $targetList;
			}
			
			$rseg = $this->_getSegs($r);
			$path = self::_expandPath($rseg, $expand);
			
			foreach ($sourceList as &$item) $item[$rname] = $this->_getHash($r, $path);
		}
				
		return $res;
	}
}