<?php
/**
 * GoogleSpellChecker.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 *
 * This spellchecker is some what of a hack since it uses an undocumented google
 * service that might be closed down in the future. So use it on your own risk.
 */

class TinyMCE_GoogleSpellChecker extends TinyMCE_SpellChecker {
	/**
	 * Spellchecks an array of words.
	 *
	 * @param String $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
	 * @param Array $words Array of words to check.
	 * @return Name/value object with arrays of suggestions.
	 */
	public function getSuggestions($lang, $words) {
		$wordstr = implode(" ", $words);
		$matches = $this->getMatches($lang, $wordstr);

		$words = array();
		for ($i = 0; $i < count($matches); $i++) {
			$word = $this->unhtmlentities(mb_substr($wordstr, $matches[$i][1], $matches[$i][2], "UTF-8"));
			$suggestions = preg_split('/\t/', utf8_encode($this->unhtmlentities($matches[$i][4])), -1, PREG_SPLIT_NO_EMPTY);

			if (!empty($suggestions)) {
				$words[$word] = $suggestions;
			}
		}

		return $words;
	}

	/**
	 * Return true/false if the engine is supported by the server.
	 *
	 * @return boolean True/false if the engine is supported.
	 */
	public function isSupported() {
		return function_exists("curl_init") || function_exists("fsockopen");
	}

	// Private methods

	private function unhtmlentities($string) {
		$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
		$string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);

		$transTbl = get_html_translation_table(HTML_ENTITIES);
		$transTbl = array_flip($transTbl);

		return strtr($string, $transTbl);
	}

	private function getMatches($lang, $str) {
		$server = "www.google.com";
		$port = 443;
		$path = "/tbproxy/spell?lang=" . $lang . "&hl=en";
		$host = "www.google.com";
		$url = "https://" . $server;

		// Setup XML request
		$xml = '<?xml version="1.0" encoding="utf-8" ?>';
		$xml .= '<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">';
		$xml .=  '<text>' . str_replace(" ", "\n", $str) . '</text>';
		$xml .= '</spellrequest>';

		$header  = "POST ".$path." HTTP/1.0 \r\n";
		$header .= "MIME-Version: 1.0 \r\n";
		$header .= "Content-type: application/PTI26 \r\n";
		$header .= "Content-length: ".strlen($xml)." \r\n";
		$header .= "Content-transfer-encoding: text \r\n";
		$header .= "Request-number: 1 \r\n";
		$header .= "Document-type: Request \r\n";
		$header .= "Interface-Version: Test 1.4 \r\n";
		$header .= "Connection: close \r\n\r\n";
		$header .= $xml;

		// Use curl if it exists
		if (function_exists('curl_init')) {
			// Use curl
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$xml = curl_exec($ch);

			if ($xml === false) {
				throw new Exception("Curl failed to send request.");
			}

			curl_close($ch);
		} else {
			// Use raw sockets
			$fp = fsockopen("ssl://" . $server, $port, $errno, $errstr, 30);
			if ($fp) {
				// Send request
				fwrite($fp, $header);

				// Read response
				$xml = "";
				while (!feof($fp)) {
					$xml .= fgets($fp, 1024);
				}

				fclose($fp);
			} else {
				throw new Exception("Could not open SSL connection to google.");
			}
		}

		// Grab and parse content
		$matches = array();
		preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $xml, $matches, PREG_SET_ORDER);

		return $matches;
	}
}

TinyMCE_Spellchecker::add("google", "TinyMCE_GoogleSpellChecker");
?>