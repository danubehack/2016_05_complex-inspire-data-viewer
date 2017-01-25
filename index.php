<?php
?>

<!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<style>
				.divCenter {
					position: absolute;
					padding: 10px;
					position: fixed;
					top: 20%;
					left: 40%;
					border: 1px solid black;
				}
				.h1 {
					position: absolute;
					left: 50%;

				}
			</style>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
			<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
				<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
				<script>
				var selectedMeasure;
			
				
				function rowValueChange()
				{
							classItem = [];			
					$.each(  $('.snippetRow') , function(key, val){
							var className = $(val).find('td:first').text();
							var itemValue = $(val).find('select').val();
							classItem.push('http://code.datacove.eu/codeList/'+ className +'/'+ itemValue);  
					});
					console.log( "classItem PARAMETER START");
					console.log( classItem );
					console.log( "classItem PARAMETER END");
					//return classItem;
				};
				
				function getGmlId(selectedMeasure)
				{
					// GET GML IDs FOR MEASURE PER COUNTRY AND YEAR
					gmlID = [];
					country = [];
					var url = "http://bolegweb.geof.unizg.hr:2017/geoserver/ows?service=WFS&version=2.0.0&request=GetFeature&STOREDQUERY_ID=GetStatdistForMeasureYear&measure="+ selectedMeasure +"&year=2012-01-01&outputFormat=application/json";
					var sendData = {};
					$.getJSON(url, sendData, function(data){
						$.each(data.features, function(){	
						  gmlID.push(this.properties.gmlid);
						  
						  var countryCode = this.properties.gmlid.split('_')[0];
						  country.push(countryCode);
						   
					  });
					console.log( "featureId PARAMETER START");
					console.log( gmlID );
					console.log( "featureId PARAMETER END");
					});	
					
				}
				function sendPdPhpFilter(){
					var url = "http://bolegweb.geof.unizg.hr/danubehack2/pd-viewer/php/PD_Filter.php?classItem=http://code.datacove.eu/codeList/age/Y_1,http://code.datacove.eu/codeList/sex/F&featureId=AT_demo_r_mlifexp_sex_age_2012";
					var sendData = {};
					$.getJSON(url, sendData, function(data){
						$.each(data.features, function(){	
						  var gmlID = this.properties.gmlid;
						  console.log("GML ID:" +gmlID);
						  var country = gmlID.split('_')[0];
						  console.log("Country:" +country);   
					  });						
					});
					
				}
				
				$( document ).ready(function(){
					var geoserverURL = 'http://bolegweb.geof.unizg.hr:2017/geoserver';
					var url = "http://bolegweb.geof.unizg.hr:2017/geoserver/pd-s/ows?service=WFS&version=2.0.0&request=GetFeature&typeNames=pd-s:sd_statdistmeasure_test&outputFormat=application/json"
					var sendData = {};
					$.getJSON(url, sendData, function(data){
						$.each(data.features, function(){
						  $("#selectMeasure").append($("<option></option>").attr("value",this.properties.measure).text(this.properties.domain));
						  //console.log(this.properties.domain);
					  });						
					});
					$("#selectMeasure").change(function(){
						var zgrupnute = {};
						
						var snippet;
						var gmlID;
						// remove old rows
						$('.snippetRow').remove();
						$('#addToMap').hide();
						
						if ($("#selectMeasure option:selected").text() == 'No measure selected'){
						$("#selectClass").parent().parent().hide();
						$("#selectFeat").parent().parent().hide();
						}
						else {
							$("#selectClass").find('option').remove();
							selectedMeasure = $("#selectMeasure option:selected").val();
							var url = "http://bolegweb.geof.unizg.hr:2017/geoserver/ows?service=WFS&version=2.0.0&request=GetFeature&STOREDQUERY_ID=GetStatdistclassitemForMeasureTest&measure="+ selectedMeasure +"&outputFormat=application/json";
							sendData = {};
							
							$.getJSON(url, sendData, function(res){
								//console.log(res); // res.features[i].properties.itemclass
								// prvy each na zgrupovanie
								$.each(res.features, function(key, feature){
									
									var itemClass = feature.properties.itemclass;
									if( $.type( zgrupnute[itemClass] ) !== "array" )
									{
										zgrupnute[itemClass] = [];
									}
									
									zgrupnute[itemClass].push(feature);
									
									
									
								});
								//console.log(zgrupnute);
								// druhy each prejde zgrupovanymi a vypise co treba
								$.each(zgrupnute, function(key, group){
									snippet = "";
									snippet += '<tr class=snippetRow>';
									snippet += '<td>'+key+'</td>';
									snippet += '<td>';
									snippet +='<select id="'+key+'" onchange="rowValueChange();getGmlId(selectedMeasure)">'									
									$.each(group, function(key, feature){
										var item = feature.properties.item;
										snippet += '<option value="'+item+'">'+item+'</option>';
									});
									snippet += '</select>';
									snippet += '</td>';
									snippet += '</tr>';
									//snippet += '<button type="button">ADD FEATURES TO MAP</button>';
																	
									$('#queryDef').append(snippet);
									
									$('#addToMap').show();
								});
								rowValueChange();
								getGmlId(selectedMeasure);
								
								
								
							});
							
						}
					});
				});
			
				
				</script>
				
				
			</head>
			<body>
				<div class="divCenter">
					<table id="queryDef">
						<tr>
							<td>
						Select Measure:
							</td>
							<td>
								<select id="selectMeasure">
									<option value="noMeasure" selected="selected">No measure selected</option>
								</select>
							</td>
						</tr>
						<!--
						<tr style="display: none">
							<td>
						Select Clasification:
							</td>
							<td>
								<select id="selectClass">
									<option value="noClass" selected="selected">No classification selected</option>
								</select>
							</td>
						</tr>
						<tr style="display: none">
							<td>
						Select Features:
							</td>
							<td>
								<select id="selectFeat">
									<option value="noFeat" selected="selected">No feature selected</option>
								</select>
							</td>
						</tr>
						-->
					</table>
					<button id="addToMap" style="display: none; float:right" type="button" onclick="myFunction()">ADD FEATURES TO MAP</button>
				</div>


			</body>
		</html>