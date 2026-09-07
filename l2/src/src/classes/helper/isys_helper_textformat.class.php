<?php

use idoit\Component\Helper\Date;

/**
 * i-doit
 *
 * Helper methods for text formatting.
 *
 * @package     i-doit
 * @subpackage  Helper
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.1
 */
class isys_helper_textformat
{
    private const EMAIL_REGEX = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
    private const AVAILABLE_PROTOCOLS = ['http://', 'https://', 'ftp://', 'sftp://', 'ftps://', 'ssh://', 'www.'];

    /**
     * This method will link all URLs (like "http(s)://example.com" or "www.example.com") and link mailtos containing "@"
     *
     * @param string $text
     * @return string
     * @throws DOMException
     */
    public static function generateHyperlinksAndMailtosInString(string $text): string
    {
        // Do not further process empty values.
        if (trim($text) === '') {
            return $text;
        }

        // Check if any e-mails or links exist, before processing:
        if (self::containsHyperlinks($text) || self::containsEmailAddress($text)) {
            $doc = new DOMDocument('1.0', 'utf-8');
            libxml_use_internal_errors(true);
            $doc->loadHTML('<?xml encoding="utf-8" ?><body>' . $text . '</body>');

            self::processChildNodes($doc, $doc->documentElement);

            return $doc->saveHTML($doc->documentElement);
        }

        return $text;
    }

    private static function containsHyperlinks(string $text): bool
    {
        foreach (self::AVAILABLE_PROTOCOLS as $protocol) {
            if (str_contains($text, $protocol)) {
                return true;
            }
        }

        return false;
    }

    private static function containsEmailAddress(string $text): bool
    {
        return (bool)preg_match(self::EMAIL_REGEX, $text);
    }

    /**
     * @param string $text
     * @return array
     */
    private static function splitTextIntoHyperlinks(string $text): array
    {
        $pattern = '/[\s\n]/';
        $array = preg_split($pattern, $text);
        $sanitizedArray = [];

        foreach ($array as $key => $item) {
            foreach (self::AVAILABLE_PROTOCOLS as $prefix) {
                if (stripos($item, $prefix) === 0) {
                    $sanitizedArray['a' . $key] = $item;
                    break;
                }
            }
            if (isset($sanitizedArray['a' . $key])) {
                continue;
            } elseif (preg_match(self::EMAIL_REGEX, $item)) {
                $sanitizedArray['email' . $key] = $item;
            } else {
                $sanitizedArray['text' . $key] = $item;
            }
        }

        // Clean out empty fragments.
        return array_filter($sanitizedArray);
    }

    /**
     * @param DOMDocument $doc
     * @param DOMNode $node
     * @throws DOMException
     */
    private static function processChildNodes(DOMDocument $doc, DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            $childNodes = $node->childNodes;
            $length = $childNodes->length;
            for ($i = 0; $i < $length; $i++) {
                $node = $childNodes->item($i);

                if ($node === null || $node->nodeName === 'a') {
                    continue;
                }

                if ($node->nodeName !== '#text') {
                    self::processChildNodes($doc, $node);
                    continue;
                }

                $sanitizedTextArray = self::splitTextIntoHyperlinks($node->nodeValue);

                if (empty($sanitizedTextArray)) {
                    continue;
                }

                $newNode = $doc->createElement('span');

                foreach ($sanitizedTextArray as $key => $item) {
                    if (str_starts_with($key, 'a')) {
                        $link = $doc->createElement('a');
                        $link->nodeValue = $item . ' ';
                        $domAttribute = $doc->createAttribute('href');
                        $domAttribute->value = trim($item);
                        $link->appendChild($domAttribute);
                        $domAttribute = $doc->createAttribute('target');
                        $domAttribute->value = '_blank';
                        $link->appendChild($domAttribute);
                        $newNode->appendChild($link);
                    } elseif (str_starts_with($key, 'email')) {
                        $link = $doc->createElement('a');
                        $link->nodeValue = $item . ' ';
                        $domAttribute = $doc->createAttribute('href');
                        $domAttribute->value = 'mailto:' . trim($item);
                        $link->appendChild($domAttribute);
                        $newNode->appendChild($link);
                    } elseif (str_starts_with($key, 'text')) {
                        $text = $doc->createTextNode($item . ' ');
                        $newNode->appendChild($text);
                    }
                }

                $node->parentNode->replaceChild($newNode, $node);
            }
        }
    }

    /**
     * This method will link all URLs (like "http://example.com" or "www.example.com").
     *
     * @param string $p_text
     * @param string $p_quotation
     * @return string
     */
    public static function link_urls_in_string($p_text, $p_quotation = '"')
    {
        $p_text = preg_replace('~\b(?<!href="|">)(?<!src="|">)(((?:ht|f)tp(s)?)|ssh)://[^<\s]+(?:/|\b)~i', '<a href=' . $p_quotation . '$0' . $p_quotation . '>$0</a>', $p_text);

        return preg_replace('~\b(?<!://|">)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}[^<\s]*\b~i', '<a href=' . $p_quotation . 'http://$0' . $p_quotation . '>$0</a>', $p_text);
    }

    /**
     * This method will link all email-addresses (like "lfischer@i-doit.com").
     *
     * @param string $text
     * @param string $quotation
     * @return string
     */
    public static function link_mailtos_in_string(string $text, string $quotation = '"'): string
    {
        return preg_replace('~\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}\b~i', '<a href=' . $quotation . 'mailto:$0' . $quotation . '>$0</a>', $text);
    }

    /**
     * Method for stripping HTML attributes out of the given string.
     *
     * @param string $string
     * @return string
     */
    public static function strip_html_attributes(string $string): string
    {
        return preg_replace('~<([a-z][a-z0-9]*)[^>]*?(\/?)>~i', '<$1$2>', $string);
    }

    /**
     * Strips script-tags from a (HTML) string.
     *
     * @param   string  $string
     * @param   boolean $allowHtml
     * @return  string
     * @deprecated Please use the HTML purifier service. Will be removed in i-doit 40!
     */
    public static function strip_scripts_tags(string $string, bool $allowHtml = false)
    {
        if (!$allowHtml) {
            return strip_tags($string);
        } else {
            return preg_replace("~<script[^>]*>([\\S\\s]*?)</script>~", "\\1", $string);
        }
    }

    /**
     * Strips script-tags from a (HTML) string.
     *
     * @param string $string
     * @return string
     */
    public static function remove_scripts(string $string): string
    {
        return preg_replace("~<script[^>]*>(.*?)</script>~", "", $string);
    }

    /**
     * Method for cleaning a string from all "non-word-characters": All special characters.
     *
     * @param string $string
     * @return string
     */
    public static function clean_string(string $string): string
    {
        return preg_replace('~\W~i', '', $string);
    }

    /**
     * Method for retrieving a string like "Good morning", depending on the time of the day.
     *
     * @param int $hour
     * @return string
     */
    public static function get_daytime($hour = null): string
    {
        return Date::getDaytimeGreeting($hour);
    }

    /**
     * This method returns a string like "A, B, C and D".
     *
     * @param array $p_parts
     * @return string
     * @throws Exception
     */
    public static function this_this_and_that(array $items): string
    {
        if (count($items) > 1) {
            return implode(', ', array_slice($items, 0, -1)) . ' ' .
                isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__AND') . ' ' .
                end($items);
        } else {
            return current($items);
        }
    }

    /**
     * This method returns a string like "A, B, C or D".
     *
     * @param array $items
     * @return string
     * @throws Exception
     */
    public static function this_this_or_that(array $items): string
    {
        if (count($items) > 1) {
            return implode(', ', array_slice($items, 0, -1)) . ' ' .
                isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__OR') . ' ' .
                end($items);
        } else {
            return current($items);
        }
    }
}
