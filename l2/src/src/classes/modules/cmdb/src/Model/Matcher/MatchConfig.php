<?php

namespace idoit\Module\Cmdb\Model\Matcher;

use idoit\Component\Provider\DiInjectable;
use idoit\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class MatchConfig
{
    use DiInjectable;

    /**
     * Match profile id
     *
     * @var int|null
     */
    protected $id = null;

    /**
     * Title of this matching profile
     *
     * @var string|null
     */
    protected $title = null;

    /**
     * Bitwise storage for matching identifiers (Based on idoit\Module\Cmdb\Model\Matcher\Identifier)
     *
     * @var int|null
     */
    protected $bits = null;

    /**
     * Minmum amount of matches
     *
     * @var int|null
     */
    protected $minMatch = null;

    /**
     * Bitwise storage for new objects filter
     *
     * @var int|null
     */
    protected ?int $filter = null;

    /**
     * Minimum amount of filter matches
     *
     * @var int|null
     */
    protected ?int $filterMin = null;

    /**
     * @var MatchDao
     */
    protected $dao;

    /**
     * MatchConfig constructor.
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->setDi($di);
        $this->dao = new MatchDao($di->get('database'));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getBits()
    {
        return $this->bits;
    }

    /**
     * @param int $bits
     *
     * @return $this
     */
    public function setBits($bits)
    {
        $this->bits = $bits;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinMatch()
    {
        return $this->minMatch;
    }

    /**
     * @param int $minMatch
     *
     * @return $this
     */
    public function setMinMatch($minMatch)
    {
        $this->minMatch = $minMatch;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFilter(): ?int
    {
        return $this->filter;
    }

    /**
     * @param int|null $filter
     *
     * @return self
     */
    public function setFilter(?int $filter = null): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFilterMin(): ?int
    {
        return $this->filterMin;
    }

    /**
     * @param int|null $filterMin
     *
     * @return self
     */
    public function setFilterMin(?int $filterMin = null): self
    {
        $this->filterMin = $filterMin;

        return $this;
    }

    /**
     * @return MatchDao
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param MatchDao $dao
     *
     * @return $this
     */
    public function setDao($dao)
    {
        $this->dao = $dao;

        return $this;
    }

    /**
     * Return isys_obj_match entry by id.
     *
     * @param int $isysObjMatchId
     *
     * @return $this
     */
    public function load($isysObjMatchId)
    {
        $data = $this->dao->retrieve('SELECT * FROM isys_obj_match WHERE isys_obj_match__id = ' . $this->dao->convert_sql_int($isysObjMatchId))
            ->get_row();

        if (!$data) {
            throw new Exception(sprintf('Matching profile "%s" was not found', $isysObjMatchId));
        }

        $this->id = $data['isys_obj_match__id'];
        $this->title = $data['isys_obj_match__title'];
        $this->bits = $data['isys_obj_match__bits'];
        $this->minMatch = $data['isys_obj_match__min_match'];
        $this->filter = $data['isys_obj_match__filter'];
        $this->filterMin = $data['isys_obj_match__filter_min'];

        return $this;
    }

    /**
     * @param int             $profileId
     * @param Container       $di
     *
     * @return MatchConfig
     */
    public static function factory($profileId, Container $di)
    {
        $config = new self($di);

        return $config->load($profileId);
    }
}
