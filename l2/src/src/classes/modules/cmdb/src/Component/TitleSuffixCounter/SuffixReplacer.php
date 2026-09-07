<?php

namespace idoit\Module\Cmdb\Component\TitleSuffixCounter;

/**
 * Class SuffixReplacer
 *
 * suitable candidate to replace the
 *  generate_title_as_array()
 * function declared in
 *  isys_smarty_plugin_f_title_suffix_counter
 *
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Oscar Pohl<opohl@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SuffixReplacer
{
    /**
     * total suffix count
     *
     * @var int
     */
    private $count = 0;

    /**
     * current suffix position
     *
     * @var int
     */
    private $currentPos = 1;

    /**
     * suffix schema
     *
     * @var string
     */
    private $schema = '';

    /**
     * starting position
     *
     * @var int
     */
    private $startingPos = 0;

    /**
     * suffix title
     *
     * @var string
     */
    private $title = '';

    /**
     * suffix type
     *
     * @var string
     */
    private $type = '';

    /**
     * number of leading zeros
     *
     * @var int
     */
    private $zeros = 0;

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function setCount(int $count): SuffixReplacer
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPos(): int
    {
        return $this->currentPos;
    }

    /**
     * @param int $currentPos
     *
     * @return $this
     */
    public function setCurrentPos(int $currentPos): SuffixReplacer
    {
        $this->currentPos = $currentPos;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     *
     * @return $this
     */
    public function setSchema(string $schema): SuffixReplacer
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartingPos(): int
    {
        return $this->startingPos;
    }

    /**
     * @param int $startingPos
     *
     * @return $this
     */
    public function setStartingPos(int $startingPos): SuffixReplacer
    {
        if ($startingPos >= 0) {
            $this->startingPos = $startingPos;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): SuffixReplacer
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): SuffixReplacer
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getZeros(): int
    {
        return $this->zeros;
    }

    /**
     * @param int $zeros
     *
     * @return $this
     */
    public function setZeros(int $zeros): SuffixReplacer
    {
        $this->zeros = $zeros;
        return $this;
    }

    public function next(): SuffixReplacer
    {
        $this->currentPos++;
        return $this;
    }

    /**
     * generates the current title
     *
     * @return string|bool
     */
    public function generateCurrentTitle()
    {
        $currentPos = $this->getCurrentPos();
        $count = $this->getCount();
        if ($currentPos >= $count + $this->getStartingPos()) {
            return false;
        }
        $title = $this->getTitle();
        $type = $this->getType();

        if ($count > 1 && $type != '') {
            $counter = $this->getGeneratedSuffixCounter();

            if ($type == -1) {
                return $title . str_replace('##COUNT##', $counter, $this->getSchema());
            }
            return $title . $counter;
        }
        return $title;
    }

    /**
     * generates next title
     *
     * @return string
     */
    public function generateNextTitle(): string
    {
        return $this->next()->generateCurrentTitle();
    }

    /**
     * generates the title at a given position
     *
     * @param int $position
     *
     * @return bool|string
     */
    public function generateTitleAtPos(int $position)
    {
        return $this->setCurrentPos($position)->generateCurrentTitle();
    }

    /**
     * generates an array of titles
     *
     * @return array
     */
    public function generateAllTitles(): array
    {
        $startingPos = $this->getStartingPos();
        $count = $this->getCount();
        $title = $this->getTitle();
        $type = $this->getType();

        $titleArray = [];

        for ($i = $startingPos; $i < $count + $startingPos; $i++) {
            $counter = $this->getGeneratedSuffixCounter($i);

            if ($count > 1 && $type != '') {
                if ($type == -1) {
                    $titleArray[$counter] = $title . str_replace('##COUNT##', $counter, $this->getSchema());
                } else {
                    $titleArray[$counter] = $title . $counter;
                }
            } else {
                $titleArray[$counter] = $title;
            }
        }
        return $titleArray;
    }

    /**
     * returns a string of a given position
     *
     * @return string
     */
    public function getGeneratedSuffixCounter($position = null): string
    {
        if ($position !== null) {
            return $this->generateLeadingZeros($position) . $position;
        }
        $currentPos = $this->getCurrentPos();
        return $this->generateLeadingZeros($currentPos) . $currentPos;
    }

    /**
     * generates string of leading zeros at given position
     *
     * @param int $position
     *
     * @return string
     */
    private function generateLeadingZeros(int $position): string
    {
        $zeros = $this->getZeros();
        if ($zeros > 0) {
            return str_repeat('0', $zeros + 1 - strlen(strval($position)));
        }
        return '';
    }

    /**
     * Function to prepare the suffix data by $_POST
     *
     * @param string $suffixIdent
     * @param string $titleTag
     */
    public function prepareSuffixByPost(string $suffixIdent, string $titleTag): void
    {
        $count = $_POST[$suffixIdent . '__SUFFIX_COUNT'];
        $schema = $_POST[$suffixIdent . '__SUFFIX_SUFFIX_TYPE_OWN'];
        $startingPos = $_POST[$suffixIdent . '__SUFFIX_COUNT_STARTING_AT'];
        $title = $_POST[$titleTag];
        $type = $_POST[$suffixIdent . '__SUFFIX_SUFFIX_TYPE'];
        $enableZeros = $_POST[$suffixIdent . '__SUFFIX_ZERO_POINT_CALC'];
        $zeros = $_POST[$suffixIdent . '__SUFFIX_ZERO_POINTS'];

        if (isset($count)) {
            $this->setCount($count);
        }
        if (isset($schema)) {
            $this->setSchema($schema);
        }
        if (isset($startingPos)) {
            $this->setStartingPos($startingPos);
            $this->setCurrentPos($startingPos);
        }
        if (isset($title)) {
            $this->setTitle($title);
        }
        if (isset($type)) {
            $this->setType($type);
        }
        if (isset($enableZeros) && $enableZeros > 0 && isset($zeros)) {
            $this->setZeros($zeros);
        }
    }
}
