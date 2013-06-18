<?php
/**
 * spellcheck.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

require('./includes/SpellChecker.php');
require('./includes/GoogleSpellChecker.php');
require('./includes/EnchantSpellChecker.php');
require('./includes/PSpellSpellChecker.php');

$tinymceSpellCheckerConfig = array(
	"engine" => "google", // enchant, google or pspell

	// Enchant options
	"enchant_dicts_path" => "",

	// PSpell options
	"pspell.mode" => "fast",
	"pspell.spelling" => "",
	"pspell.jargon" => "",
	"pspell.encoding" => ""
);

TinyMCE_Spellchecker::processRequest($tinymceSpellCheckerConfig);
?>