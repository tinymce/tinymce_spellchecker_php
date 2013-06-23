<?php
/**
 * spellcheck.base.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 *
 * Base class for all spellcheckers this takes in the words to check
 * spelling on and returns the suggestions.
 */

class TinyMCE_SpellChecker {
	private static $engines = array();
	private $config;

	public function __constructor($config) {
		$this->config = $config;
	}

	/**
	 * Spellchecks an array of words.
	 *
	 * @param String $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
	 * @param Array $words Array of words to check.
	 * @return Name/value object with arrays of suggestions.
	 */
	public function getSuggestions($lang, $words) {
		return array();
	}

	/**
	 * Return true/false if the engine is supported by the server.
	 *
	 * @return boolean True/false if the engine is supported.
	 */
	public function isSupported() {
		return true;
	}

	/**
	 * Sets the config array used to create the instance.
	 *
	 * @param Array $config Name/value array with config options.
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 * Returns the config array used to create the instance.
	 *
	 * @return Array Name/value array with config options.
	 */
	public function getConfig() {
		return $this->config;
	}

	// Static methods

	public static function processRequest($tinymceSpellcheckerConfig) {
		$engine = self::get($tinymceSpellcheckerConfig["engine"]);
		$engine = new $engine();
		$engine->setConfig($tinymceSpellcheckerConfig);

		header("Content-Type: application/json; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		$json = json_decode(file_get_contents("php://input"));
		if ($json && $json->method === "spellcheck") {
			try {
				if (!$engine->isSupported()) {
					throw new Exception("Current spellchecker isn't supported.");
				}

				// Sanitize input
				$lang = preg_replace('/[^a-z\-_]/i', '', $json->params->lang); // a-z, -, _
				$words = explode(' ', preg_replace('/[\x00-\x1F\x7F]/', '', implode(' ', $json->params->words))); // No control characters

				$words = $engine->getSuggestions($lang, $words);

				echo json_encode((object) array(
					"id" => $json->id,
					"result" => (object) $words,
					"error" => null
				));
			} catch (Exception $e) {
				echo json_encode((object) array(
					"id" => $json->id,
					"result" => null,
					"error" => $e->getMessage()
				));
			}
		} else {
			echo json_encode((object) array(
				"id" => $json->id,
				"result" => null,
				"error" => "Invalid JSON input"
			));
		}
	}

	public static function add($name, $className) {
		self::$engines[$name] = $className;
	}

	public static function get($name) {
		if (!isset(self::$engines[$name])) {
			return null;
		}

		return self::$engines[$name];
	}
}

?>
