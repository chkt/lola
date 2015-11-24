<?php

namespace chkt\app;

use chkt\http\HttpRequest;



trait TAppLanguage {
	
	protected $_dict = [];
	
	protected $_tLanguageLangs = null;
	protected $_tLanguageDefault = null;
	
	protected $_tLanguageLocs = null;
	
	
	
	public function getAvailableLanguages() {
		if (is_null($this->_tLanguageLangs)) $this->_tLanguageLangs = array_keys($this->_dict['locales']);
		
		return $this->_tLanguageLangs;
	}
	
	public function isAvailableLanguage($lang) {
		if (!is_string($lang) || empty($lang)) throw new \ErrorException();
		
		return array_search($lang, $this->getAvailableLanguages(), true) !== false;
	}
	
	public function getDefaultLanguage() {
		if (is_null($this->_tLanguageDefault)) {
			$langs = $this->getAvailableLanguages();
			
			$this->_tLanguageDefault = count($langs) !== 0 ? $langs[0] : 'en';
		}
		
		return $this->_tLanguageDefault;
	}
	
	public function getNegotiatedLanguage() {
		$want = HttpRequest::originAcceptLanguages();
		$have = $this->getAvailableLanguages();
		
		print_r($have);
		
		foreach ($want as $lang => $weight) {
			if (array_search($lang, $have) !== false) return $lang;
		}
		
		return $this->getDefaultLanguage();
	}
	
	
	public function getAvailableLocales() {
		if (is_null($this->_tLanguageLocs)) $this->_tLanguageLocs = $this->_dict['locales'];
		
		return $this->_tLanguageLocs;
	}
	
	public function getLocaleByLang($lang) {
		if (!is_string($lang) || empty($lang)) throw new \ErrorException();
		
		$locales = $this->getAvailableLocales();
		
		return array_key_exists($lang, $locales) ? $locales[$lang] : 'en_US.utf8';
	}
}
