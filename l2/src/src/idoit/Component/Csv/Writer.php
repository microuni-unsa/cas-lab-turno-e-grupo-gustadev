<?php

namespace idoit\Component\Csv;

use isys_tenantsettings;
use League\Csv\EncloseField;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\UnavailableStream;
use League\Csv\Writer as CsvWriter;
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
class Writer extends CsvWriter
{
    /**
     * @param resource|SplFileInfo|SplFileObject|string $filename
     * @param string                                    $mode
     * @param null                                      $context
     * @return static
     * @throws InvalidArgument
     * @throws UnavailableStream
     */
    public static function from($filename, string $mode = 'r+', $context = null): static
    {
        return parent::from($filename, $mode, $context)
            ->configure()
            ->forceEnclosure();
    }

    /**
     * @param Stringable|string $content
     * @return static
     * @throws InvalidArgument
     */
    public static function fromString(Stringable|string $content = ''): static
    {
        return parent::fromString($content)
            ->configure()
            ->forceEnclosure();
    }

    /**
     * @param string      $path
     * @param string|null $open_mode
     * @param null        $context
     * @return Writer
     * @throws Exception
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @deprecated Use 'Writer::from()'. Will be removed in i-doit 40!
     */
    public static function createFromPath(string $path, ?string $open_mode = null, $context = null): static
    {
        $writer = parent::createFromPath($path, $open_mode ?: 'r+', $context)->configure();
        EncloseField::addTo($writer, "\t\x1f");

        return $writer;
    }

    /**
     * @param string|Stringable $content
     * @return Writer
     * @throws InvalidArgument
     * @throws Exception
     * @deprecated Use 'Writer::fromString()'. Will be removed in i-doit 40!
     */
    public static function createFromString(string|Stringable $content = ''): static
    {
        $writer = parent::createFromString($content)->configure();
        EncloseField::addTo($writer, "\t\x1f");

        return $writer;
    }

    /**
     * Forced enclosure does not work with SplFileObject
     *
     * @param SplFileObject $file
     * @return Writer
     * @throws InvalidArgument
     * @deprecated Use 'Writer::from()'. Will be removed in i-doit 40!
     */
    public static function createFromFileObject(SplFileObject $file): static
    {
        return parent::createFromFileObject($file)->configure();
    }

    /**
     * @return Writer
     * @throws InvalidArgument
     */
    private function configure(): static
    {
        return $this
            ->setOutputBOM(self::BOM_UTF8)
            ->setDelimiter(isys_tenantsettings::get('system.csv-export-delimiter', ';'));
    }
}
