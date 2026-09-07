<?php

/**
 * Build xml structure using the  headerinformation main node and optional header information.
 *
 * @package     i-doit
 * @subpackage  Components_XML
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_xml_structure
{
    /**
     * @var  array
     */
    private $arrXmlHeaderAttribute;

    /**
     * @var  isys_component_xml_node
     */
    private $objMainNode;

    /**
     * @var  string
     */
    private $strEncoding = "UTF-8";

    /**
     * @param  $p_strConstEncoding
     */
    public function set_encoding($p_strConstEncoding)
    {
        //$p_strConstEncoding
        $this->arrXmlHeaderAttribute['encoding'] = $p_strConstEncoding;
        $this->strEncoding = $this->arrXmlHeaderAttribute['encoding'];
    }

    /**
     * Encode the given parameter.
     *
     * @param   string $p_strValue
     *
     * @return  string
     */
    public function encode($p_strValue)
    {
        $l_strReturn = "";

        switch ($this->strEncoding) {
            case "UTF-8":
                $l_strReturn = $p_strValue;

                // no break
            default:
        }

        return $l_strReturn;
    }

    /**
     * Each class which extends isys_component_xml_object has to override this methode.
     *
     * @return  string
     */
    public function output()
    {
        $l_strHeader = "";

        foreach ($this->arrXmlHeaderAttribute as $l_key => $l_value) {
            $l_strHeader .= "$l_key=\"$l_value\" ";
        }

        return "<?xml " . $l_strHeader . "?>" . $this->encode($this->objMainNode->get_object());
    }

    /**
     * Contruct isys_component_xml_structure.
     *
     * @param  isys_component_xml_node $p_obj_node
     * @param  array                   $p_arrXmlHeaderAttribute
     */
    public function __construct(
        $p_obj_node,
        $p_arrXmlHeaderAttribute = [
            "version"  => "1.0",
            "encoding" => "UTF-8"
        ]
    ) {
        // start node
        $this->objMainNode = $p_obj_node;

        // header information
        $this->arrXmlHeaderAttribute = $p_arrXmlHeaderAttribute;
    }
}
