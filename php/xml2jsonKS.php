<?php

$classitem = array (
/*
	'http://code.datacove.eu/codeList/age/Y_GE25' => false,
	'http://code.datacove.eu/codeList/sex/T' => false
	*/
);
$lastemtpy = false;
$first = true;

function clearArray($inArray) {
	$outArray = array();
	foreach ($inArray as $k => $v) {
		$outArray[$k] = false;
	}
	return $outArray;
}

function checkArray($inArray) {
	$retVal = true;
	foreach($inArray as $k=>$v) {
		if (!$v) $retVal = false;
	}

	return $retVal;
}

function xmlToArrayKS($xml, $classes, $options = array()) {
	global $classitem;
	global $lastemtpy;
	global $first;
	$amFirst=false;
	
	if ($first) {
		$amFirst=true;
		$first = false;
		/**/
		foreach ($classes as $k => $v) {
			$classitem[$v] = false;
		}
		
	}
	$thisName = $xml->getName();
    $defaults = array(
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(),   //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => '$',       //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch' => 'featureMembers',       //optional search and replace on tag and attribute names
            'keyReplace' => 'member'       //replace values for above search values (as passed to str_replace())
    );
	
	$realtags = array (
		'wfs:FeatureCollection' => 1,
		'wfs:member' => 1,
		'pd:StatisticalDistribution' => 1,
		'pd:periodOfReference' => 1,
		'gml:TimePeriod' => 1,
		'gml:beginPosition' => 1,
		'pd:value' => 1,
		'pd:StatisticalValue' => 1,
		'pd:flags' => 1,
		'pd:status' => 1,
		'pd:dimensions' => 1,
		'pd:Dimensions' => 1,
		'pd:spatial' => 1,
		'pd:domain' => 1,
		'gco:CharacterString' => 1,
		'pd:measure' => 1,
		'pd:measurementMethod' => 1,
		'pd:measurementUnit' => 1,
		'pd:generalStatus' => 1,
//		'' => 1,
//		'pd-s:t2' => 1
	);
	
	$repeattag = 'StatisticalValue';
	
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces();
    $namespaces[''] = null; //add base (empty) namespace

    //get attributes from all namespaces
    $attributesArray = array();
	
	if ($thisName == $repeattag) {
		$classitem = clearArray($classitem);
	} 

    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) {
                $attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            }
            $attributeKey = $options['attributePrefix']
            . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
            . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }
	if ($thisName == 'type') {
		foreach ($attributesArray as $k => $v) {
			if (array_key_exists($v,$classitem)){
				$classitem[$v] = true;
			}
		}
		checkArray($classitem);
	}

	//get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = xmlToArrayKS($childXml, $classes, $options);
            list($childTagName, $childProperties) = each($childArray);

            //replace characters in tag name
            if ($options['keySearch']) {
                $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            }
            //add namespace prefix, if any
            if ($prefix) {
                $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
            }
			
			//if (strcmp($childTagName, 'pd-s:t1')) {
			if (array_key_exists($childTagName, $realtags)){
				if ($lastemtpy) {
					$lastemtpy = false;
				} else {
					if (!(($childTagName == 'pd:StatisticalValue') && !(checkArray($classitem)))) {
						if (!isset($tagsArray[$childTagName])) {
							//only entry with this key
							//test if tags of this type should always be arrays, no matter the element count
							$tagsArray[$childTagName] =
							in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
							? array($childProperties) : $childProperties;
						} elseif (
								is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
								=== range(0, count($tagsArray[$childTagName]) - 1)
						) {
							//key already exists and is integer indexed array
							$tagsArray[$childTagName][] = $childProperties;
						} else {
							//key exists so convert to integer indexed array with previous value in position 0
							$tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
						}
					} else {
						$lastemtpy = true;
					}
				}
			} //else {$attributesArray = array();$tagsArray = array();}
	
        }
    }

    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') {
		$textContentArray[$options['textContent']] = $plainText;
	}

    //stick it all together
	$propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
	? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
	if ($amFirst) {
		if (!$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')) {
			$classArray = array();
			
			foreach ($classitem as $k => $v) {
				if (!isset($classArray['pd:classification'])) {
					$classArray['pd:classification'] = $k;
				} elseif (is_array($classArray['pd:classification']) && array_keys($classArray['pd:classification'])
								=== range(0, count($classArray['pd:classification']) - 1) ) {
					//key already exists and is integer indexed array
					$classArray['pd:classification'][] = $k;
				} else {
					//key exists so convert to integer indexed array with previous value in position 0
					$classArray['pd:classification'] = array($classArray['pd:classification'], $k);
				}
			}			
			$propertiesArray = array_merge($propertiesArray, $classArray);
		}
	}

    //return node as array
    return array(
            $xml->getName() => $propertiesArray
    );
}