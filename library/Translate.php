<?php

require_once("config.php");

class Translate {
    private $language = 'sv';
    private $lang = array();
    
    public function __construct($language = null) {
        if ($language) {
            $this->language = $language;
        } else {
            $test_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            $langs = $this->get_available_languages();
            if (in_array($test_lang, $langs)) {
                $this->language = $test_lang;
            } else {
                $this->language = $langs[0];
            }
        }
    }

    public function get_language() {
        return $this->language;
    }
    
    private function findString($str) {
        if (array_key_exists($str, $this->lang[$this->language])) {
            echo $this->lang[$this->language][$str];
        } else {
            echo $str;
        }
    }
    
    private function splitStrings($str) {
        return explode('=',trim($str));
    }
    
    public function __($str) {  
        if (!array_key_exists($this->language, $this->lang)) {
            if (file_exists(TRANSLATIONS_PATH . $this->language.'.txt')) {
                $strings = array_map(array($this,'splitStrings'),file(TRANSLATIONS_PATH . $this->language.'.txt'));
                $this->lang[$this->language] = array();
                foreach ($strings as $k => $v) {
                    $this->lang[$this->language][$v[0]] = $v[1];
                }
                return $this->findString($str);
            } else {
                echo $str;
            }
        } else {
            return $this->findString($str);
        }
    }

    public function get_translations() {
        if (!array_key_exists($this->language, $this->lang)) {
            if (file_exists(TRANSLATIONS_PATH . $this->language.'.txt')) {
                $strings = array_map(array($this,'splitStrings'),file(TRANSLATIONS_PATH . $this->language.'.txt'));
                foreach ($strings as $k => $v) {
                    $this->lang[$this->language][$v[0]] = $v[1];
                }
            }
        }
        return $this->lang;
    }

    public function get_available_languages() {
        $lang_files = array_diff(scandir(TRANSLATIONS_PATH), array('..', '.'));
        $langs = array();
        foreach ($lang_files as $l) {
            if (preg_match('/^[a-z]{2}\.txt$/', $l)) {
                $langs[] = substr($l, 0, 2);
            }
        }
        return $langs;
    }
}