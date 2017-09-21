<?php
/**
 * PSpellEngine.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/**
 * Class TinyMCE_SpellChecker_PSpellEngine
 */
class TinyMCE_SpellChecker_PSpellEngine extends TinyMCE_SpellChecker_Engine
{
    /**
     * Spellchecks an array of words.
     *
     * @param String $lang  Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with
     *                      enchant >= 1.4.1
     * @param array  $words Array of words to check.
     *
     * @return array array of suggestions.
     * @throws Exception
     */
    public function getSuggestions($lang, $words)
    {
        $config = $this->getConfig();

        switch ($config['PSpell.mode']) {
            case "fast":
                $mode = PSPELL_FAST;
                break;

            case "slow":
                $mode = PSPELL_SLOW;
                break;

            default:
                $mode = PSPELL_NORMAL;
        }

        // Setup PSpell link
        $plink = pspell_new(
            $lang,
            $config['pspell.spelling'],
            $config['pspell.jargon'],
            $config['pspell.encoding'],
            $mode
        );

        if (!$plink) {
            throw new Exception("No PSpell link found opened.");
        }

        $outWords = [];
        foreach ($words as $word) {
            if (!pspell_check($plink, trim($word))) {
                $outWords[] = utf8_encode($word);
            }
        }

        return $outWords;
    }

    /**
     * Return true/false if the engine is supported by the server.
     *
     * @return boolean True/false if the engine is supported.
     */
    public function isSupported()
    {
        return function_exists("pspell_new");
    }
}

TinyMCE_SpellChecker_Engine::add("pspell", "TinyMCE_SpellChecker_PSpellEngine");
?>
