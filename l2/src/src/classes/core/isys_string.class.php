<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_string
{
    /**
     * Split string by delimiters | ; ,
     *
     * @param $p_str
     *
     * @return array
     */
    public static function split($p_str)
    {
        return array_map('trim', preg_split('/[|;,]/', $p_str ?? '', 0, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Highlight string and preserve it's original case
     *
     * @param string $needle
     * @param string $haystack
     * @param int    $minimumLength
     *
     * @return string
     */
    public static function highlight($needle, $haystack, $minimumLength = 3)
    {
        if (strlen($needle) >= $minimumLength) {
            $needle = isys_helper::sanitize_text(strip_tags($needle));

            $ind = stripos($haystack, $needle);
            $len = strlen($needle);
            if ($ind !== false) {
                return substr($haystack, 0, $ind) . "<span class=\"searchHighlight\">" . substr($haystack, $ind, $len) . "</span>" .
                    self::highlight($needle, substr($haystack, $ind + $len));
            }
        }

        return $haystack;
    }

    /**
     * @param string             $search
     * @param DOMDocument|string $dom
     *
     * @return false|string
     */
    public static function domHighlight(string $search, $dom)
    {
        if (is_string($dom)) {
            $doc = new DOMDocument('1.0', 'utf-8');
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $dom);
            $dom =& $doc;
        } elseif (!$dom instanceof DOMDocument) {
            return false;
        }
        self::domTextReplace($search, $dom);
        return $dom->saveHTML();
    }

    /**
     * method for replacing in all textNodes of a document only nodeValues, inserting <span class=highlight> into them surrounding search string
     * @param         $search
     * @param DOMNode $domNode
     */
    public static function domTextReplace($search, DOMNode &$domNode)
    {
        if ($domNode->hasChildNodes()) {
            $children = [];
            // since looping through a DOM being modified is a bad idea we prepare an array:
            foreach ($domNode->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $oldText = $child->wholeText;
                    $newText = isys_string::highlight($search, $oldText);
                    if ($oldText !== $newText) {
                        $fragment = $domNode->ownerDocument->createDocumentFragment();
                        $fragment->appendXML($newText);
                        $domNode->replaceChild($fragment, $child);
                    }
                } else {
                    self::domTextReplace($search, $child);
                }
            }
        }
    }
}
