<?php declare(strict_types = 1);

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage API
 * @author     Selcuk Kekec <skekec@i-doit.de>
 * @version    1.10
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Exception\Exception;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreMergeModifierInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ModelProcessor
 */
class LocationProcessor extends AbstractProcessor implements PreMergeModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__LOCATION';

    /**
     * @var string[]
     */
    protected static $categoryDependency = ['C__CATG__FORMFACTOR'];

    /**
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     */
    public function preMergeModify(array $syncData, int $objectId): array
    {
        // @see  API-190  Find the "real" HE to position the object.
        if (isset($syncData['properties']['pos']) && is_numeric($syncData['properties']['pos'][C__DATA__VALUE])) {
            $enclosureData = [];
            $position = $syncData['properties']['pos'][C__DATA__VALUE];
            // First we need to get some information about the location parent.
            if (isset($this->dependentSyncData['C__CATG__FORMFACTOR'][0], $this->dependentSyncData['C__CATG__FORMFACTOR'][0]['rack_units'])) {
                $enclosureData['objectHeight'] = $this->dependentSyncData['C__CATG__FORMFACTOR'][0]['rack_units'][C__DATA__VALUE];
            }

            // If there is a newly defined parent, we have to get the correct height of that object.
            if (isset($syncData['properties']['parent'][C__DATA__VALUE])) {
                $rackData = $this->retrieveRackData($syncData['properties']['parent'][C__DATA__VALUE]);

                // Retrieve the rack height and slot sorting of the new parent.
                $enclosureData['rackHeight'] = $rackData['rackHeight'];
                $enclosureData['slotSorting'] = $rackData['slotSorting'];
            }

            // Set the default value, if no rackunits are set.
            if (!isset($enclosureData['objectHeight']) || !is_numeric($enclosureData['objectHeight'])) {
                $enclosureData['objectHeight'] = 1;
            }

            // Set the default value, if no rackunits are set.
            if (!is_numeric($enclosureData['rackHeight'])) {
                $enclosureData['rackHeight'] = 1;
            }

            // @todo  Use `isys_cmdb_dao_category_s_enclosure::C__SLOT_SORTING__DESC` in the future, available starting i-doit 1.13.1.
            if ($enclosureData['slotSorting'] === 'asc') {
                // We don't need further logic, since the rack is sorted ascending.
                return $syncData;
            }

            /*
             * @see  API-190
             *
             * Calculate the new position for this object. This is necessary since the position is ALWAYS counted from 1 to <rack height> but in the frontend the position
             * can (visibly) change when the slot sorting is set to "descending". So in this example we have a object with 3 HE and a enclosure with 40 HE.
             *
             * We always count ascending - that means if we want to position something on HE 30, the object would go from 33 to 30 (this was defined by product).
             *
             * The formula is: ((<rack height> - <desired position>) - <object height>) + 2
             * (+ 2 since we start with 1 instead of 0 and also we need to add one since we subtract the object height)
             *
             * 1) Want to position in HE 40: ((40 - 40) - 3) + 2 = -1 -> Should trigger an error during validation (since only values between 1 and 40 are allowed)
             * 2) Want to position in HE 38: ((40 - 38) - 3) + 2 =  1 (visually from 40 to 38)
             * 3) Want to position in HE  1: ((40 -  1) - 3) + 2 = 38 (visually from 3 to 1)
             * 4) Want to position in HE 20: ((40 - 20) - 3) + 2 = 19 (visually from 24 to 22)
             */
            $syncData['properties']['pos'][C__DATA__VALUE] = (($enclosureData['rackHeight'] - $position) - $enclosureData['objectHeight']) + 2;

            // Provide some validation, since the location DAO does not do this.
            if ($syncData['properties']['pos'][C__DATA__VALUE] > $enclosureData['rackHeight']) {
                $message = 'The calculated position inside the rack lies outside the rack.
                    Position needs to be between 1 and ' . $enclosureData['rackHeight'] . ', got ' . $position . '.';
                throw new Exception($message, Response::HTTP_BAD_REQUEST);
            }
        }

        return $syncData;
    }

    /**
     * @param int $rackObjectId
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function retrieveRackData($rackObjectId)
    {
        $objectId = $this->getDao()->convert_sql_id($rackObjectId);

        $sql = "SELECT
            isys_catg_formfactor_list__rackunits AS rackHeight,
            isys_cats_enclosure_list__slot_sorting AS slotSorting
            FROM isys_catg_formfactor_list
            INNER JOIN isys_cats_enclosure_list ON isys_cats_enclosure_list__isys_obj__id = isys_catg_formfactor_list__isys_obj__id
            WHERE isys_catg_formfactor_list__isys_obj__id = {$objectId}
            LIMIT 1;";

        return $this->getDao()->retrieve($sql)->get_row();
    }
}
