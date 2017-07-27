<?php
/**
 * Created by wir_wolf.
 * User: Andru Cherny
 * Date: 15.03.17
 * Time: 15:57
 */

namespace Redefinitions\Components\Web;

use yii\base\ErrorException;

/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *          - replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *          - fixed a edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *          - fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *          - Added support for CDATA section using @cdata instead of @value.
 * Version: 0.5 (07 December 2011)
 *          - Changed logic to check numeric array indices not starting from 0.
 * Version: 0.6 (04 March 2012)
 *          - Code now doesn't @cdata to be placed in an empty array
 * Version: 0.7 (24 March 2012)
 *          - Reverted to version 0.5
 * Version: 0.8 (02 May 2012)
 *          - Removed htmlspecialchars() before adding to text node or attributes.
 */
class XmlResponseFormatter extends \yii\web\XmlResponseFormatter
{

    /**
     * @var bool
     */
    public $expanded = false;

    /**
     * @var bool
     */
    public $expandedAttributesKey = '@attributes';

    /**
     * @var string
     */
    public $expandedCDataKey = '@cdata';

    /**
     * @var string
     */
    public $expandedValueKey = '@value';

    /**
     * @var bool
     */
    public $allKeysUpperKeys = false;


    /**
     * @param \yii\web\Response $response
     * @return string|void
     */
    public function format($response) {
        if ($this->expanded) {
            $response->content = $this->convertExpandedXml($response);
            return;
        }
        parent::format($response);
    }

    /**
     * @var null
     */
    private $xml = false;

    /**
     * @param $response
     * @return string
     */
    private function convertExpandedXml($response) {
        $xml = $this->getDocument();
        $xml->appendChild($this->convert($this->rootTag, $response->data));
        return $xml->saveXML();
    }

    /**
     * @return \DOMDocument|null
     */
    private function getDocument() {
        if(false === $this->xml) {
            $this->xml = new \DOMDocument($this->version, $this->encoding);
            $this->xml->formatOutput = true;
        }
        return $this->xml;

    }


    /**
     * @param $tag
     * @param array $arr
     * @return \DOMElement
     * @throws ErrorException
     */
    private function convert($tag, $arr = []) {
        if($this->allKeysUpperKeys) {
            $tag = strtoupper($tag);
        }
        $xml = $this->getDocument();
        $node = $xml->createElement($tag);

        if (is_array($arr)) {
            // get the attributes first.;
            if (isset($arr[$this->expandedAttributesKey])) {
                foreach ($arr[$this->expandedAttributesKey] as $key => $value) {
                    if (!self::isValidTagName($key)) {
                        throw new ErrorException(
                            'Illegal character in attribute name. Attribute: ' . $key . ' in node: ' . $tag
                        );
                    }
                    $node->setAttribute($key, self::bool2str($value));
                }
                unset($arr[$this->expandedAttributesKey]); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if (isset($arr[$this->expandedValueKey])) {
                $node->appendChild($xml->createTextNode(self::bool2str($arr[$this->expandedValueKey])));
                unset($arr[$this->expandedValueKey]);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } else {
                if (isset($arr[$this->expandedCDataKey])) {
                    $node->appendChild($xml->createCDATASection(self::bool2str($arr[$this->expandedCDataKey])));
                    unset($arr[$this->expandedCDataKey]);    //remove the key from the array once done.
                    //return from recursion, as a note with cdata cannot have child nodes.
                    return $node;
                }
            }
        }

        //create subnodes using recursion
        if (is_array($arr)) {
            // recurse to get the node for that key
            foreach ($arr as $key => $value) {
                if (!self::isValidTagName($key)) {
                    if(is_numeric($key)) {
                        $node->appendChild($this->convert($this->itemTag, $value));
                        unset($arr[$key]); //remove the key from the array once done.
                        continue;
                    } else {
                        throw new ErrorException('Illegal character in tag name. tag: ' . $key . ' in node: ' . $tag);
                    }
                }
                if (is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach ($value as $k => $v) {
                        $node->appendChild($this->convert($key, $v));
                    }
                } else {
                    // ONLY ONE NODE OF ITS KIND
                    $node->appendChild($this->convert($key, $value));
                }
                unset($arr[$key]); //remove the key from the array once done.
            }
        }

        // after we are done with all the keys in the array (if it is one)
        // we check if it has any text value, if yes, append it.
        if (!is_array($arr)) {
            $node->appendChild($xml->createTextNode(self::bool2str($arr)));
        }

        return $node;
    }


    /*
     * Get string representation of boolean value
     */
    private static function bool2str($v) {
        //convert boolean to text value.
        $v = $v === true ? 'true' : $v;
        $v = $v === false ? 'false' : $v;
        return $v;
    }

    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     */
    private static function isValidTagName($tag) {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}