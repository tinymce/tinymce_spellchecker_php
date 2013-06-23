<?php
/**
 * EnchantSpellChecker.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

class TinyMCE_EnchantSpellChecker extends TinyMCE_SpellChecker {
	/**
	 * Spellchecks an array of words.
	 *
	 * @param String $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
	 * @param Array $words Array of words to check.
	 * @return Name/value object with arrays of suggestions.
	 */
	public function getSuggestions($lang, $words) {
		$suggestions = array();
		$enchant = enchant_broker_init();
		$config = $this->getConfig();

		if (isset($config["enchant_dicts_path"])) {
			enchant_broker_set_dict_path($enchant, ENCHANT_MYSPELL, $config["enchant_dicts_path"]);
			enchant_broker_set_dict_path($enchant, ENCHANT_ISPELL, $config["enchant_dicts_path"]);
		}

		if (enchant_broker_dict_exists($enchant, $lang)) {
			$dict = enchant_broker_request_dict($enchant, $lang);

			foreach ($words as $word) {
				$correct = enchant_dict_check($dict, $word);

				if (!$correct) {
					$suggs = enchant_dict_suggest($dict, $word);

					if (!is_array($suggs)) {
						$suggs = array();
					}

					$suggestions[$word] = $suggs;
				}
			}

			enchant_broker_free_dict($dict);
			enchant_broker_free($enchant);
		} else {
			enchant_broker_free($enchant);
			throw new Exception("Could not find dictionary. Code: " . $lang);
		}

		return $suggestions;
	}

	/**
	 * Return true/false if the engine is supported by the server.
	 *
	 * @return boolean True/false if the engine is supported.
	 */
	public function isSupported() {
		return function_exists("enchant_broker_init");
	}
}

TinyMCE_Spellchecker::add("enchant", "TinyMCE_EnchantSpellChecker");
?>
