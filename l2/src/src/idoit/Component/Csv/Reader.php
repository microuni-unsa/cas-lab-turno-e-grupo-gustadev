<?php

namespace idoit\Component\Csv;

use isys_tenantsettings;
use League\Csv\InvalidArgument;
use League\Csv\Reader as CsvReader;
use League\Csv\UnavailableStream;
use SplFileInfo;
use SplFileObject;
use Stringable;

/**
 * i-doit CSV reader.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Reader extends CsvReader
{
    /**
     * @param resource|SplFileInfo|SplFileObject|string $filename
     * @param string                                    $mode
     * @param null                                      $context
     * @return static
     * @throws InvalidArgument
     * @throws UnavailableStream
     */
    public static function from($filename, string $mode = 'r', $context = null): static
    {
        return parent::from($filename, $mode, $context)->configure();
    }

    /**
     * @param Stringable|string $content
     * @return static
     * @throws InvalidArgument
     */
    public static function fromString(Stringable|string $content = ''): static
    {
        return parent::fromString($content)->configure();
    }

    /**
     * @param string        $path
     * @param string|null   $open_mode
     * @param resource|null $context
     * @return Reader
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @deprecated Use 'Reader::from()'. Will be removed in i-doit 40!
     */
    public static function createFromPath(string $path, ?string $open_mode = null, $context = null): static
    {
        return parent::createFromPath($path, $open_mode ?: 'r+', $context)->configure();
    }

    /**
     * @param Stringable|string $content
     * @return Reader
     * @throws InvalidArgument
     * @deprecated Use 'Reader::fromString()'. Will be removed in i-doit 40!
     */
    public static function createFromString(Stringable|string $content = ''): static
    {
        return parent::createFromString($content)->configure();
    }

    /**
     * @param SplFileObject $file
     * @return Reader
     * @throws InvalidArgument
     * @deprecated Use 'Reader::from()'. Will be removed in i-doit 40!
     */
    public static function createFromFileObject(SplFileObject $file): static
    {
        return parent::createFromFileObject($file)->configure();
    }

    /**
     * @return Reader
     * @throws InvalidArgument
     */
    private function configure(): static
    {
        return $this
            ->setOutputBOM(self::BOM_UTF8)
            ->setDelimiter(isys_tenantsettings::get('system.csv-export-delimiter', ';'));
    }
}
