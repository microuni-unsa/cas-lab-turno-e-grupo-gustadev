<?php

namespace idoit\Component\Table;

use isys_application;
use isys_convert;

/**
 * i-doit Placeholder Component.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Oscar Pohl <opohl@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Placeholder
{
    /**
     * @var bool|mixed
     */
    private $linkEmailAddresses;

    /**
     * @var bool|mixed
     */
    private $linkObjects;

    public function __construct($linkObjects = true, $linkEmailAddresses = true)
    {
        $this->linkObjects = $linkObjects;
        $this->linkEmailAddresses = $linkEmailAddresses;
    }

    /**
     * Replaces placeholder example: {mem,1073741824,GB}
     *
     * @param $value
     * @param $unit
     *
     * @return mixed
     */
    private function replaceMemory($value, $unit): mixed
    {
        return round(isys_convert::memory($value, 'C__MEMORY_UNIT__' . $unit, C__CONVERT_DIRECTION__BACKWARD), 4) . ' ' . $unit;
    }

    /**
     * Replace placeholder example: {currency,25000.42,1}
     *
     * @param $value
     *
     * @return mixed
     */
    private function replaceCurrency($value): mixed
    {
        return isys_application::instance()->container->get('locales')->fmt_monetary($value);
    }

    /**
     * Replace placeholders for a single datum
     *
     * @param $html
     *
     * @return string
     * @deprecated Is this method used?
     */
    public function replacePlaceholdersInCell($html): string
    {
        global $g_dirs;
        $html = preg_replace([
            '/[\w\- ]+ \{1\}/', // Replace the "Title {1}" with the root location house.
            '/\{(#[0-9a-fA-F]{3,6})\}/', // Replace "{#123456}" with the cmdb-marker.
            '/([^\{\>,]+) \{([0-9]+)\}/' // Replace object links with format "Title {id}".
        ], [
            '<img class="vam" src="' . $g_dirs['images'] . 'axialis/construction/house-4.svg">',
            '<div class="dynamic-replacement cmdb-marker" style="background-color: $1;"></div>',
            ' <a class="dynamic-replacement quickinfo" href="?objID=$2" data-object-id="$2">$1</a>'
        ], $html);

        $html = preg_replace_callback('/\{([a-z]*)\,([0-9a-zA-Z\.-]*)\,([0-9a-zA-Z\.]*)\}/', function ($matches) {
            switch ($matches[1]) {
                case 'mem':
                    return $this->replaceMemory($matches[2], $matches[3]);
                case 'currency':
                    return $this->replaceCurrency($matches[2]);
                default:
                    return $matches[0];
            }
        }, $html);

        // ID-6376 Detect e-mail links and add some css classes to it
        if ($this->linkEmailAddresses) {
            preg_match('/((([^<>()\[\]\.,;\s@\"]+(\.[^<>()\[\]\.,;\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,}))/', $html, $matches);

            // Check whether there are any matches
            if ($matches) {
                // Exlude http links
                if (strpos($matches[0], '://') !== false) {
                    return $matches[0];
                }

                // Check whether there are any 'mailto' links left
                if (strlen($matches[0]) > 0) {
                    // todo make sure this works properly in a report
                    return '<a class="dynamic-replacement email-marker" href="mailto:' . $matches[0] . '">' . $matches[0] . '</a>';
                }
            }
        }

        return $html;
    }

    /**
     * Replace placeholders
     *
     * !!! BE CAREFUL !!!
     *
     * This logic is used in object list
     *
     * @param $html
     *
     * @return string|string[]|null
     * @deprecated Is this method used?
     */
    public function replacePlaceholders($html): string|array|null
    {
        global $g_dirs;

        $html = preg_replace([
            '/[\w\- ]+ \{1\}/', // Replace the "Title {1}" with the root location house.
            '/\{(#[0-9a-fA-F]{3,6})\}/', // Replace "{#123456}" with the cmdb-marker.
            '/([^\{\>,]+) \{([0-9]+)\}/' // Replace object links with format "Title {id}".
        ], [
            '<img class="vam" src="' . $g_dirs['images'] . 'axialis/construction/house-4.svg">',
            '<div class="dynamic-replacement cmdb-marker" style="background-color: $1;"></div>',
            ' <a class="dynamic-replacement quickinfo" href="?objID=$2" data-object-id="$2">$1</a>'
        ], $html);

        // Replace email with mailto links
        if ($this->linkEmailAddresses) {
            $urlRegex = '/((([^<>()\[\]\.,;\s@\"]+(\.[^<>()\[\]\.,;\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,}))/';

            $html = preg_replace_callback($urlRegex, function (array $match): string {
                /*
                 * ID-5939 Testing for "://" - in this case we got a link like "ssh://root@domain.tld" and NOT an email address.
                 * We CAN NOT replace "http://..." strings with HTML links or it will break all images etc.
                 */
                $httpLinkIndex = array_search('://', $match);

                if ($httpLinkIndex !== false) {
                    return $match[$httpLinkIndex];
                }

                return '<a class="dynamic-replacement email-marker" href="mailto:' . $match[0] . '">' . $match[0] . '</a>';
            }, $html);
        }

        return $html;
    }
}
