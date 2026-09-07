<?php declare(strict_types = 1);

namespace idoit\Component\Helper;

use isys_application;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\UnicodeString;

class Purify
{
    /**
     * @return array
     */
    private static function intValues(): array
    {
        return [
            defined_or_default('C__CMDB__GET__OBJECT'),
            defined_or_default('C__CMDB__GET__OBJECTTYPE'),
            defined_or_default('C__CMDB__GET__OBJECTGROUP'),
            defined_or_default('C__CMDB__GET__CATG'),
            defined_or_default('C__CMDB__GET__CATS'),
            defined_or_default('C__CMDB__GET__CATLEVEL'),
            defined_or_default('C__CMDB__GET__VIEWMODE'),
            defined_or_default('C__CMDB__GET__TREEMODE')
        ];
    }

    /**
     * @return string[]
     */
    public static function getSqlStatementIndicators(): array
    {
        return [
            'select',
            'update',
            'insert',
            'drop',
            'replace',
            'delete',
            'alter',
            'create',
            'set'
        ];
    }

    /**
     * Cast only i-doit specific get parameters C__CMDB__GET__OBJECT, C__CMDB__GET__OBJECTTYPE,
     * C__CMDB__GET__OBJECTGROUP, C__CMDB__GET__CATG, C__CMDB__GET__VIEWMODE ... to int
     *
     * @param array $params
     *
     * @return array
     */
    public static function castIntValues($params): array
    {
        $intValues = self::intValues();

        array_walk($params, function (&$value, $key) use ($intValues) {
            // have to additional check if $value is not null because in some cases we need it as NULL
            if (in_array($key, $intValues) && $value !== null) {
                $value = (int)$value;
            }
        });

        return $params;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public static function purifyParams($params): array
    {
        return isys_application::instance()->container->get('htmlpurifier')->purifyArray(self::castIntValues($params));
    }

    /**
     * @param array $params
     * @param array $exclude
     *
     * @return string[]
     * @throws \Exception
     */
    public static function removeWhiteSpacesFromParams($params, $exclude = []): array
    {
        foreach ($params as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $params[$key] = strtr($value, [' ' => '']);
        }

        return isys_application::instance()->container->get('htmlpurifier')->purifyArray($params);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function removeSqlIndicators(string $string): string
    {
        // @see ID-12186 Strip bad words as whole words. Keep strings like 'selection' etc.
        $badWordPattern = '~\b(' . implode('|', self::getSqlStatementIndicators()) . ')\b~i';

        return preg_replace($badWordPattern, '', $string);
    }

    /**
     * @param string $key
     * @param array $params
     *
     * @return string
     */
    public static function purifyParameter($key, $params): string
    {
        return isys_application::instance()->container->get('htmlpurifier')->purify($params[$key]);
    }

    /**
     * Purifies value
     *
     * @param string|null $value
     *
     * @return string|null
     * @throws \Exception
     */
    public static function purifyValue(?string $value): ?string
    {
        if ($value === null) {
            return $value;
        }
        return isys_application::instance()->container->get('htmlpurifier')->purify((string)$value);
    }

    /**
     * @param string $value
     * @param string $pattern
     *
     * @return string
     */
    public static function purifyValueByRegex(string $value, string $pattern = ''): string
    {
        if ($pattern !== '') {
            $value = preg_replace($pattern, '', $value);
        }
        return self::purifyValue((string)$value);
    }

    /**
     * @param string   $value
     * @param callable $callback
     *
     * @return string
     */
    public static function purifyValueByCallback(string $value, callable $callback): string
    {
        return call_user_func($callback, self::purifyValue($value));
    }

    /**
     * @param array    $params
     * @param callable $callback
     * @param array    $exclude
     *
     * @return array
     */
    public static function purifyParamsByCallback(array $params, callable $callback, array $exclude = []): array
    {
        foreach ($params as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $params[$key] = self::purifyValueByCallback($value, $callback);
        }
        return $params;
    }

    /**
     * @param string $path
     *
     * @return string
     * @see ID-10032 ID-10033 Unify paths more strictly to prevent path traversal.
     */
    public static function preventPathTraversal(string $path): string
    {
        return str_replace('../', '', preg_replace(['~/\.+/~', '~/+~'], ['/', '/'], str_replace('\\', '/', $path)));
    }

    /**
     * Use this method as a mighty alternative to 'strip_tags'.
     *
     * @param string|null $value
     *
     * @return string|null
     * @see ID-11146
     */
    public static function removeHtml(?string $value): ?string
    {
        if ($value === null) {
            return $value;
        }

        return isys_application::instance()->container->get('htmlremover')->purify((string)$value);
    }

    /**
     * @param string $constant
     *
     * @return string
     */
    public static function formatConstant(string $constant): string
    {
        /*
         * Sadly we can not simply use the 'slug' method because
         * it will reduce multiple separators to one ('C___WHATEVER' => 'C_WHATEVER')
         * and that would break a lot of our constants.
         */
        return (new UnicodeString($constant))
            ->ascii(['de-ASCII'])
            ->replaceMatches('/[^A-Za-z0-9]/', '_')
            ->trim('_')
            ->upper()
            ->toString();
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public static function formatFilename(string $filename): string
    {
        $placeholders = [
            '_' => 'placeholder-underscore',
            '.' => 'placeholder-dot',
        ];

        // Do this in order to keep underscores.
        $filename = str_replace(array_keys($placeholders), array_values($placeholders), $filename);

        // Use symfony slugger to format a string. Trim hyphens.
        $formattedFilename = (new AsciiSlugger('de'))->slug($filename)->lower()->toString();

        // Replace the underscore placeholders and trim '_' and '-'.
        return trim(str_replace(array_values($placeholders), array_keys($placeholders), $formattedFilename), '_-');
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function formatUrlPart(string $url): string
    {
        // Do this in order to keep underscores.
        $url = str_replace('_', 'placeholder-underscore', $url);

        // Use symfony slugger to format a string. Trim hyphens.
        $formattedFilename = (new AsciiSlugger('de'))->slug($url)->toString();

        // Replace the underscore placeholders and trim '_' and '-'.
        return trim(str_replace('placeholder-underscore', '_', $formattedFilename), '_-');
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function transliterate(string $value): string
    {
        return (new UnicodeString($value))->ascii(['de-ASCII'])->toString();
    }
}
