<?php

namespace chkt\app;

use chkt\http\Cookie;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods



trait TAppLangCookie {
//	use \chkt\app\TAppLanguage;
	
	protected $_dict = [];
	
	protected $_tLangCookiePrefered = null;
	
	
	
	public function getPreferedLanguage() {
		if (is_null($this->_tLangCookiePrefered)) {
			$iso = Cookie::value('lang');
			
			$this->_tLangCookiePrefered = !is_null($iso) ? $iso : $this->getNegotiatedLanguage();
		}
		
		return $this->_tLangCookiePrefered;
	}
	
	public function updateLangLocale(Cookie &$cookie, $lang) {
		if (!$this->isAvailableLanguage($lang)) throw new \ErrorException();
		
		setlocale(LC_ALL, $this->getLocaleByLang($lang));
		
		$cookie->set('lang', $lang, Cookie::EXPIRES_SESSION, Cookie::PATH_ROOT);
		
		return $lang;
	}
}
