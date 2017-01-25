<?php
include_once 'xml2jsonKS.php';

if (empty($_GET)) {
	echo 'Usage:<br>';
	echo 'baseUrl: the base URL of the WFS to be queried, Example: http://data.datacove.eu:8080/<br>';
	echo 'featureId: gml id of the feature to be filtered. Example: AT_lfst_r_lfu3rt_age_sex_2015<br>';
	echo 'Alternatively, a uri-encoded full WFS URL can be provided<br>';
	echo 'wfsUrl: full WFS URL. Example: http://data.datacove.eu:8080/geoserver/ows?service=WFS&version=2.0&request=GetFeature&typeNames=pd:StatisticalDistribution&featureId=AT_lfst_r_lfu3rt_age_sex_2015<br>';
	echo 'Items to filter by for each classification. Note: if items are not provided for all classes, no values will be returned<br>';
	echo 'classItem: CSV list of the individual items. Example: classItem=http://code.datacove.eu/codeList/age/Y_GE25,http://code.datacove.eu/codeList/sex/M<br>';
	echo '<br>';
	echo 'Examples:<br>';
	echo '<a target="_blank" href="http://bolegweb.geof.unizg.hr/danubehack2/pd-viewer/php/PD_Filter.php?classItem=http://code.datacove.eu/codeList/age/Y_GE25,http://code.datacove.eu/codeList/sex/M&featureId=AT_lfst_r_lfu3rt_age_sex_2015">PD_Filter.php?classItem=http://code.datacove.eu/codeList/age/Y_GE25,http://code.datacove.eu/codeList/sex/M&featureId=AT_lfst_r_lfu3rt_age_sex_2015</a><br><br>';
	echo '<a target="_blank" href="http://bolegweb.geof.unizg.hr/danubehack2/pd-viewer/php/PD_Filter.php?classItem=http://code.datacove.eu/codeList/age/Y_GE25,http://code.datacove.eu/codeList/sex/M&wfsUrl=http%3A%2F%2Fdata.datacove.eu%3A8080%2Fgeoserver%2Fows%3Fservice%3DWFS%26version%3D2.0%26request%3DGetFeature%26typeNames%3Dpd%3AStatisticalDistribution%26featureId%3DAT_lfst_r_lfu3rt_age_sex_2015">PD_Filter.php?classItem=http://code.datacove.eu/codeList/age/Y_GE25,http://code.datacove.eu/codeList/sex/M&wfsUrl=http%3A%2F%2Fdata.datacove.eu%3A8080%2Fgeoserver%2Fows%3Fservice%3DWFS%26version%3D2.0%26request%3DGetFeature%26typeNames%3Dpd%3AStatisticalDistribution%26featureId%3DAT_lfst_r_lfu3rt_age_sex_2015</a><br>';
} else {
$baseUrl = 'http://data.datacove.eu:8080/';
$wfsUrl = 'http://data.datacove.eu:8080/geoserver/ows?service=WFS&version=2.0&request=GetFeature&typeNames=pd:StatisticalDistribution&featureId=AT_lfst_r_lfu3rt_age_sex_2015';
$baseUrl = (empty($_GET['baseUrl'])?$baseUrl:$_GET['baseUrl']);
$featTypeName = 'pd:StatisticalDistribution';
$featureId = (empty($_GET['featureId'])?'':$_GET['featureId']);

if (!($featTypeName=='')) {
	$wfsUrl = $baseUrl . 'geoserver/ows?service=WFS&version=2.0&request=GetFeature&typeNames=' . $featTypeName;
	if (!($featureId=='')) {
		$wfsUrl = $wfsUrl . '&featureId=' . $featureId;
	}
}


$wfsUrl = (empty($_GET['wfsUrl'])?$wfsUrl:$_GET['wfsUrl']);
$classItem = (empty($_GET['classItem'])?$wfsUrl:$_GET['classItem']);
$classes = str_getcsv($classItem);
/*
echo $featTypeName, '<br>';
echo $wfsUrl, '<br>';
echo $classItem, '<br>';
*/
$xml = simplexml_load_file($wfsUrl);
  
$arrayData = xmlToArrayKS($xml, $classes);

echo json_encode($arrayData);
}