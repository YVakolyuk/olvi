<?php 
	session_start();
?>
<html>
	<head>
	<meta charset="UTF-8" />
		<title>O.L.Vi.</title>
		<link rel="stylesheet" href="style.css" type="text/css" />
		<script type="text/javascript"
			src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDZkCGDCptY1ztDe8EyTV5Zn6mkpgrEMRQ&sensor=true">
		</script>
		<script type="text/javascript" src="https://www.google.com/jsapi"> </script>
		<script type="text/javascript" src="http://scriptjava.net/source/scriptjava/scriptjava.js"></script>
		<script type="text/javascript">
		//------------------------------------------It's all GOOGLE EARTH-------------------------------------------
			var url = "http://<?php echo $_SERVER['SERVER_NAME']; ?>/olvi/"
		//	var url = 'http://127.0.0.1/olvi/';
		
			var ge;//main GoogleEarth variable
			var la;//main GoogleEarth look at variable
			var reCord;
			var refIntId;//идентификатор таймера
			var flCreP=0;//флаг создания placemark метки на GoogleEarth. 0-не создавался ранее в этой сессии, 1-создавался
//			var objects_array =[];

			//It's Google Earth observer variables
			var placemark;
			var icon;
			var style;
			var point;
			var lineStringPlacemark;
			var lineString;
			var lineStyle;
		//	var image = url+'plane_on.png';
		
			
			//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			google.load("earth", "1", {"other_params":"sensor=false"});

			function googleEarthInitOnUP() {
				google.earth.createInstance('map3d', initCB, failureCB);
			}
			
			var loadingObjectsTimer; //идентификатор таймера загрузки всех объектов
			function initCB(instance) {
				ge = instance;
				ge.getWindow().setVisibility(true);
				ge.getLayerRoot().enableLayerById(ge.LAYER_BORDERS, true);
	  			ge.getNavigationControl().setVisibility(ge.VISIBILITY_AUTO);
				ge.getNavigationControl().getScreenXY().setXUnits(ge.UNITS_PIXELS);
				ge.getNavigationControl().getScreenXY().setYUnits(ge.UNITS_INSET_PIXELS);
						
				load_objects();
				loadingObjectsTimer = setInterval(function() {load_objects();},2000);
				load_tracks();
				
				la = ge.createLookAt('');
				getNewLook("def");
				function mousePosition(event) {
					var currentLat=event.getLatitude();
					var currentLng=event.getLongitude();
					var currentAlt=event.getAltitude();
					document.getElementById("nav_mousePos").innerHTML = "Latitude: " + currentLat + "</br>Longitute: " + currentLng +
																											"</br>Altitude: " + currentAlt;
				}
				google.earth.addEventListener(ge.getGlobe(), 'mousemove', mousePosition);
				
			}

			function failureCB(errorCode) {
			}
			
			function getNewLook(option, lat, lng, alt) { //функция настраивает новый вид н карту либо по умолчанию, либо на указанные координаты
				if(option=="def") {
					ge.getOptions().setFlyToSpeed(0.2);
					la.set(Number(49.979),Number(36.270), 0, ge.ALTITUDE_RELATIVE_TO_GROUND, 0, 65, 8000);
				} else {
					ge.getOptions().setFlyToSpeed(1);
					la.set(Number(lat),Number(lng), 0, ge.ALTITUDE_RELATIVE_TO_GROUND, 0, 70, (Number(alt)*4));
				}
				//	map.setCenter(pos);
				ge.getView().setAbstractView(la);
			}
			
			var placemarks = []; //массив меток обьектов на глобусе
			var local_point = []; //массив хранящий координаты для каждой из метки
			var local_on_icon;
			var local_ctrl_icon;
			var local_on_style;
			var local_ctrl_style;
			var local_lineStringPlacemark = [];
			var local_lineString = [];
			var local_lineStyle = [];
			var local_creationFlag=0;
			var local_content = [];
			var local_balloon = [];
			var local_event = [];
			var local_sample;
			var objects_array = [];
			function load_objects() {
				$$a({
					type:'get',//тип запроса: get,post либо head
					url: url+"load_objects.php", 
					data: {p: "load"},//url адрес файла обработчика
					response:'text',//тип возвращаемого ответа text либо xml
					error:function(num){
					var arr=['Your browser does not support Ajax',
                        'Request failed',
                        'Address does not exist',
                        'The waiting time left'];
					alert(arr[num]);
					},
					success:function (data) {//возвращаемый результат от сервера
						var objList = document.getElementById("obj_list");
						var countOn = 0;
						var countOff = 0;
						objects_array = JSON.parse(data);
						local_on_icon = ge.createIcon('');
						local_ctrl_icon = ge.createIcon('');
						
						local_on_style = ge.createStyle(''); //создает новый стиль
						local_on_icon.setHref(url+'plane_on.png');
						local_on_style.getIconStyle().setIcon(local_on_icon); //применяет значок к стилю
						
						local_ctrl_style = ge.createStyle(''); //создает новый стиль
						local_ctrl_icon.setHref(url+'plane.png');
						local_ctrl_style.getIconStyle().setIcon(local_ctrl_icon); //применяет значок к стилю
					
					/*	if(local_sample>objects_array.length && local_creationFlag) {
							for(var i=0;i<local_sample;i++) {
								ge.getFeatures().removeChild(placemarks[i]);
								ge.getFeatures().removeChild(local_lineStringPlacemark[i]);
							}
						}*/ 						
						objList.innerHTML = "";
						for(var i=0;i<objects_array.length;i++) {
						//alert("Step "+i);
							if(objects_array[i].status!="off") {
								countOn++;
								objList.innerHTML = objList.innerHTML+"<li><a href='#' onclick='goToControl("+objects_array[i].id_object+");return false;'>"+
																															"<img src='"+url+"plane_on.png' height='20px'>"+
																																objects_array[i].obj_name+"</a></li>";
								if(!placemarks[i] || !local_point[i] || !local_lineStringPlacemark[i] || !local_lineString[i]) {
									placemarks[i] = ge.createPlacemark(''); //создаем метку
									local_point[i] = ge.createPoint('');			//создаем координату
									
									local_lineStringPlacemark[i] = ge.createPlacemark(''); //создаем метку-линию
									local_lineString[i] = ge.createLineString('');
									if(i==objects_array.length-1)
										local_creationFlag=1;
								
								
									local_lineStringPlacemark[i].setGeometry(local_lineString[i]);
									local_lineString[i].setExtrude(true);
									local_lineString[i].setAltitudeMode(ge.ALTITUDE_ABSOLUTE);
									local_lineStringPlacemark[i].setStyleSelector(ge.createStyle(''));
									local_lineStyle[i] = local_lineStringPlacemark[i].getStyleSelector().getLineStyle();
									local_lineStyle[i].setWidth(5);
									local_lineStyle[i].getColor().set('9900ffff');  // формат aabbggrr
								}
								
								if(controlObjId==objects_array[i].id_object) {
									placemarks[i].setStyleSelector(local_ctrl_style); //применяет стиль к метке
								} else {//if (controlObjId != objects_array[i].id_object){
									placemarks[i].setStyleSelector(local_on_style); //применяет стиль к метке
								//	alert("fuck");
								}
								
								local_point[i].setLatitude(Number(objects_array[i].lat)); //устанавливаем широту для координаты
								local_point[i].setLongitude(Number(objects_array[i].lng)); //устанавливаем долготу для координаты
								local_point[i].setAltitudeMode(ge.ALTITUDE_ABSOLUTE); //устанавливаем тип измерения высоты
								local_point[i].setAltitude(Number(objects_array[i].alt)); //устанавливаем высоту для координаты
								
								placemarks[i].setName(objects_array[i].obj_name); //присваиваем метке имя
								placemarks[i].setGeometry(local_point[i]); //устанавливаем координату для метки
								placemarks[i].setDescription(
											'<div class="balloon"><h3><p align=center>'+objects_array[i].obj_name+'</p></h3>' +
											'<b>Status:</b> '+objects_array[i].status+'<br>' +
											'<b>Current position</b><br>' +
											'Latitude: '+objects_array[i].lat+'<br>' +
											'Longitude: '+objects_array[i].lng+'<br>' +
											'Altitude: '+objects_array[i].alt+'<br>' +
											'<p align=center><input class="button_all" type="button" id="newGo" value="Control panel" onclick="goToControl('+objects_array[i].id_object+')"></p></div>');
								balloonsCraetor(placemarks[i]);
								ge.getFeatures().appendChild(placemarks[i]); //выводим метку на глобус
								
								local_lineString[i].getCoordinates().clear();
								local_lineString[i].getCoordinates().pushLatLngAlt(Number(objects_array[i].lat), Number(objects_array[i].lng), Number(objects_array[i].alt));

								ge.getFeatures().appendChild(local_lineStringPlacemark[i]);
								
							}else {
							objList.innerHTML = objList.innerHTML+"<li><a href='#' onclick='goToControl("+objects_array[i].id_object+");return false;'>"+
																															"<img src='"+url+"plane_off.png' height='20px'>"+
																																objects_array[i].obj_name+"</a></li>";
							countOff++;
							}
						}
						local_sample=objects_array.length;
						document.getElementById("footer").innerHTML = "Now enabled "+countOn+" objects. The other "+countOff+" are turned off and in standby mode.";
					}
				});				
			}

			function balloonsCraetor(placemark) {
				google.earth.addEventListener(placemark, 'click', function(event) {
				  // Запрещает открывать всплывающее окно по умолчанию.
				  event.preventDefault();
				  var content = placemark.getDescription();
				  var balloon = ge.createHtmlStringBalloon('');
				  balloon.setFeature(placemark);
				  balloon.setContentString(content);
				  ge.setBalloon(balloon);
				});
			}
			
			function findObject(id) { //функция производит поиск обьекта в массиве placemarks и возвращает его номер в массиве
				for(var i=0;i<objects_array.length;i++) {
					if(objects_array[i].id_object==id) {
						return i;
					}
				}
			}
			
			var loadObjectInfoTimer; //идентификатор таймера обновления информации на странице control panel
			var controlObjId; //идентификатор контролируемого объекта в базе данных
			function goToControl(o_id) { //функция запускает переход на страницу Control panel для выбранного обьекта,
														//создает таймер обновления инфы на странице
				controlObjId = o_id;
				updObjectInfo();
				loadObjectInfoTimer = setInterval(function() {updObjectInfo();},2000);

				showTrackId = objects_array[findObject(o_id)].id_track;
				if (showTrackId!=null) {
					getMarkersForTrack(showTrackId);
					setTimeout(makeFlightPath, 500);
				}
			
				showPage(4);
			}
			var mapsObjImage = url+'plane.png';
			function updObjectInfo() { //функция обновляет информацию в одноименном блоке на странице control panel
				var num = findObject(controlObjId);
				var pos = new google.maps.LatLng(Number(objects_array[num].lat), Number(objects_array[num].lng));
				document.getElementById("up_objNameId").innerHTML = "<h2>Control panel "+objects_array[num].obj_name+"</h2>";
				if(objects_array[num].status == "on") {
					currentObjPosition.setMap(map);
					currentObjPosition.setPosition(pos);
					currentObjPosition.setIcon(mapsObjImage);
					document.getElementById("switchObj").value = "Turn off";
					if(document.getElementById("ctrlTracking").checked) {
						getNewLook("",objects_array[num].lat,objects_array[num].lng,objects_array[num].alt);
						map.setCenter(pos);
					}
				}
				else {
					document.getElementById("switchObj").value = "Turn on";
					currentObjPosition.setMap(null);
				}
				
				var trackName;
				for(var i=0;i<tracks.length;i++) {
					if(tracks[i].id_track == objects_array[num].id_track)
						trackName = tracks[i].track_name;
				}
				document.getElementById("infoBlock").innerHTML = "<p><b>Current position</b></p>"+
																								"<ul id='nav_curPos'>"+
																									"<li>Latitude: "+objects_array[num].lat+";</li>"+
																									"<li>Longitude: "+objects_array[num].lng+";</li>"+
																									"<li>Altitude: "+objects_array[num].alt+".</li>"+
																								"</ul>"+
																								"<p><b>Status </b>"+objects_array[num].status+"</p>"+
																								"<p><b>Follows the track </b>"+trackName+"</p>"+
																								"<p><b>Object id </b>"+objects_array[num].id_object+"</p>";
			}
			
			function objectSwitch() {//функция производит включение или выключение объекта, т.е. изменение записи о статусе в базе данных
				var button = document.getElementById("switchObj");
				if(button.value == "Turn off") {
					postSwitchObject(controlObjId, "off");
					button.value = "Turn on";
					removeChildObj(controlObjId);
					
				} else if(button.value == "Turn on") {
					postSwitchObject(controlObjId, "on");
					button.value = "Turn off";
				}
			}
			
			function removeChildObj(id) {
				var num = findObject(id);
				ge.getFeatures().removeChild(placemarks[num]);
				ge.getFeatures().removeChild(local_lineStringPlacemark[num]);
			}
			
			function postSwitchObject(id,stat) { //функция производит пост запрос к серверу, он предназначен для изменения статуса объекта в БД
				$$a({
					type:'post',//тип запроса: get,post либо head
					url: url+'POSTobjSwitch.php',//url адрес файла обработчика
					data:{'id_object':id,'stat':stat},//параметры запроса
					response:'text',//тип возвращаемого ответа text либо xml
					success:function (data) {//возвращаемый результат от сервера
					
					}
				});
			}

			function removeObj() {
				document.getElementById("delete_obj_form_container").style.display="inline-block";
				document.getElementById('rem_pass').focus();
				document.body.onkeydown = function (e){
					if(navigator.userAgent.match("Gecko")){
						c=e.which;
					}else{
						c=e.keyCode;
					}
					if(c==9){
					//	alert("TAB");
						return false;
					}
				};
			}
	
			function removeFormHide() {
				document.getElementById("rem_pass").value = null;
				document.getElementById("delete_obj_form_container").style.display="none";
				document.body.onkeydown = "";
			}
			
			function removeFormSubmit() {
				var admLogin = '<?php echo $_SESSION['olvi']; ?>';
				var admPass = document.getElementById("rem_pass").value;
				var confirm = document.getElementById("confirm_remove_object");
				if(admPass!="") {
					if(objects_array[findObject(controlObjId)].status == "on") {
						removeChildObj(controlObjId);
					}
					$$a({
						type:'post',//тип запроса: get,post либо head
						url: url+'POSTremoveObject.php',//url адрес файла обработчика
						data:{'id':controlObjId,'login': admLogin, 'admPass': admPass},//параметры запроса
						response:'text',//тип возвращаемого ответа text либо xml
						success:function (data) {//возвращаемый результат от сервера
							document.getElementById("rem_pass").value = null;
							if(data=="deleted") {
								removeFormHide();
								showPage(0);
							} else {
								confirm.innerHTML = data;
								confirm.style.display = "block";
							}
						}
					});
				}
				else {
					confirm.innerHTML = "Enter current password";
					confirm.style.display = "block";
				}
			}
			
			function changeObjTrack(id) {
				if(id!=null){
					$$a({
						type:'post',//тип запроса: get,post либо head
						url: url+'POSTchangeObjTrack.php',//url адрес файла обработчика
						data:{'id': controlObjId, 'id_track': id},//параметры запроса
						response:'text',//тип возвращаемого ответа text либо xml
						success:function (data) {//возвращаемый результат от сервера
							document.getElementById("up_tracksSelector").value="null";
						}
					});
					alert("changeObjTrack -> makeFlightPath");
					getMarkersForTrack(id);
					showTrackId = id;
					setTimeout(makeFlightPath, 500);
				}
			}
			//^^^^^^^^^^^^^^^^^^^^^^^^^It's all GOOGLE EARTH^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			//--------------------------------It's all GOOGLE MAPS---------------------------------
			var polylineOptionsShow = {
				strokeColor: '#0000FF',
				strokeWeight: 2
			};
			var polylineOptionsCreation = {
				strokeColor: '#000000',
				strokeOpacity: 1.0,
				strokeWeight: 3
			};
			var fl_crPath=0;
			var map;
			var currentObjPosition;
			function googleMapsInitOnUP() {
			
				var mapOptions = {
					center: new google.maps.LatLng(49.999, 36.250),
					zoom: 14,
					scaleControl: true,
					zoomControlOptions: {
						style: google.maps.ZoomControlStyle.DEFAULT
					},
					mapTypeId: google.maps.MapTypeId.HYBRID
				};
				map = new google.maps.Map(document.getElementById("mapG"), mapOptions);      
				
				currentObjPosition = new google.maps.Marker({});
				
				flightPath = new google.maps.Polyline(polylineOptionsShow);
				
				google.maps.event.addDomListener(window, 'load', googleMapsInitOnUP);
				
				google.maps.event.addDomListener(document.getElementById('gm_ctrlCreate'), 'click', function() {
				if(fl_crPath==0) {
					fl_crPath=1;
					document.getElementById('gm_ctrlCreate').value="Complete the path";
					createPath();
				}
				else if(fl_crPath==1) {
					fl_crPath=0;
					document.getElementById('gm_ctrlCreate').value="Create a path";
					clearMap();
					firstMarkerCreation_flag=0;
					load_tracks();
				}
			});
				
				var gm_trackSelector = document.getElementById("gm_tracksSelector")
				google.maps.event.addDomListener(gm_trackSelector, 'change', function() {
					if(gm_trackSelector.value!="null") {
						showTrackId = gm_trackSelector.value;
						getMarkersForTrack(gm_trackSelector.value);
						setTimeout(makeFlightPath, 500);
						document.getElementById("gm_removeTrack").style.display="inline-block";
					} else {
						document.getElementById("gm_removeTrack").style.display="none";
						setMarkersToNULL();
					}
					document.getElementById("remTrackConfirm").style.display="none";
				});				
				
				google.maps.event.addListener(map, 'click', addLatLng);
			}
			
			function createPath() {
				document.getElementById("remTrackConfirm").style.display="none";
				document.getElementById("create_track_form_container").style.display="inline-block";
				document.getElementById('path_name').focus();
				document.body.onkeydown = function (e){
					if(navigator.userAgent.match("Gecko")){
						c=e.which;
					}else{
						c=e.keyCode;
					}
					if(c==9){
					//	alert("TAB");
						return false;
					}
				};
			}
	
			function createFormHide(flag) {
				document.getElementById("path_name").value = null;
				document.getElementById("create_track_form_container").style.display="none";
				document.body.onkeydown = "";
				if (!flag) {
					fl_crPath=0;
					document.getElementById('gm_ctrlCreate').value="Create a path";
				}
			}
			
			var polylineCreation = new google.maps.Polyline(polylineOptionsCreation);
			var newPathId;
			function createFormSubmit() {
				var pathName = document.getElementById("path_name").value;
				if(pathName!="") {
					$$a({
						type:'post',//тип запроса: get,post либо head
						url: url+'POSTnewPath.php',//url адрес файла обработчика
						data:{'path_name': pathName},//параметры запроса
						response:'text',//тип возвращаемого ответа text либо xml
						error:function(num){
							var arr=['Your browser does not support Ajax',
								'Request failed',
								'Address does not exist',
								'The waiting time left'];
							alert(arr[num]);
						},
						success:function (data) {//возвращаемый результат от сервера
							newPathId = data;
							setTimeout(createFormHide(1),500);
							polylineCreation = new google.maps.Polyline(polylineOptionsCreation);
							polylineCreation.setMap(map);
						}
					});
				}
			}
			
			var firstMarkerCreation_flag=0;
			var firstMarkerCreation = new google.maps.Marker();
			function addLatLng(event) { //функция создания траектории
				if(fl_crPath==1) {
					var path = polylineCreation.getPath();
					// Because path is an MVCArray, we can simply append a new coordinate
					// and it will automatically appear.
					path.push(event.latLng);
					
					// Add a new marker at the new plotted point on the polyline.
					if(!firstMarkerCreation_flag) {
						firstMarkerCreation = new google.maps.Marker({
							position: event.latLng,
							title: "Coordinates " + event.latLng,
							map: map
						});
						firstMarkerCreation.setIcon(iconUrl+"00a20b");
						firstMarkerCreation_flag=1;
					}
					postMarkerPath(event.latLng.lat(),event.latLng.lng());
				}
			}
			
			function postMarkerPath(latitudePost,longitudePost) {
				$$a({
					type:'post',//тип запроса: get,post либо head
					url: url+'POSTnewMarkerPath.php',//url адрес файла обработчика
					data:{'lat':latitudePost,'lng':longitudePost,'path':newPathId},//параметры запроса
					response:'text',//тип возвращаемого ответа text либо xml
					success:function (data) {//возвращаемый результат от сервера
						
					}
				});
			}
			
			function findTrack(id) { //функция производит поиск обьекта в массиве placemarks и возвращает его номер в массиве
				for(var i=0;i<tracks.length;i++) {
					if(tracks[i].id_track==id) {
						return i;
					}
				}
			}
			
			var showTrackId;
			var tracks;
			function load_tracks() {
				var gm_tracksSelector = document.getElementById("gm_tracksSelector");
				var up_tracksSelector = document.getElementById("up_tracksSelector");
				gm_tracksSelector.innerHTML = "<option value='null'>select</option>";
				up_tracksSelector.innerHTML = "<option value='null'>None</option>";
				$$a({
					type:'get',//тип запроса: get,post либо head
					url: url+'GETallTracks.php',//url адрес файла обработчика
					response:'text',//тип возвращаемого ответа text либо xml
					error:function(num){
						var arr=['Your browser does not support Ajax',
							'Request failed',
							'Address does not exist',
							'The waiting time left'];
						alert(arr[num]);
					},
					success:function (data) {//возвращаемый результат от сервера
						tracks = JSON.parse(data);
						for(var i=0;i<tracks.length;i++) {
							gm_tracksSelector.innerHTML = gm_tracksSelector.innerHTML + 
																			"<option value='"+tracks[i].id_track+"'>"+tracks[i].track_name+"</option>";
							up_tracksSelector.innerHTML = up_tracksSelector.innerHTML + 
																			"<option value='"+tracks[i].id_track+"'>"+tracks[i].track_name+"</option>";
						}
					}
				});
			}
			
			var markers_array;
			function getMarkersForTrack(id) {
				$$a({
					type:'post',//тип запроса: get,post либо head
					url: url+'POSTgetTrackMarkers.php',//url адрес файла обработчика
					data:{'id_track': id},//параметры запроса
					response:'text',//тип возвращаемого ответа text либо xml
					error:function(num){
						var arr=['Your browser does not support Ajax',
							'Request failed',
							'Address does not exist',
							'The waiting time left'];
						alert(arr[num]);
					},
					success:function (data) {//возвращаемый результат от сервера
						markers_array = JSON.parse(data);
					}
				});
			}
			
			function clearMap() {
				setMarkersToNULL();
				document.getElementById("gm_tracksSelector").value="null";
				document.getElementById("gm_removeTrack").style.display="none";
				document.getElementById("remTrackConfirm").style.display="none";
			}
			
			function setMarkersToNULL() {
				flightPath.setMap(null);
				startPoint.setMap(null);
				finishPoint.setMap(null);
				polylineCreation.setMap(null);
				firstMarkerCreation.setMap(null);
				currentObjPosition.setMap(null);
			}
			
			var flightPlanCoordinates=[];
			var flightPath = new google.maps.Polyline(polylineOptionsShow);
			var startPoint = new google.maps.Marker();
			var finishPoint = new google.maps.Marker();
			var iconUrl = "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|";
			function makeFlightPath() {
			alert("makeFlightPath");
				setMarkersToNULL();
				flightPlanCoordinates = [];
				
				for(var i=0;i<markers_array.length;i++) {
					var myLatlng=new google.maps.LatLng(markers_array[i].latitude,markers_array[i].longitude);
					flightPlanCoordinates.push(myLatlng);
				}
				
				flightPath.setPath(flightPlanCoordinates);
				flightPath.setMap(map);
				
				var myLatlng=new google.maps.LatLng(markers_array[markers_array.length-1].latitude,markers_array[markers_array.length-1].longitude);
				finishPoint.setPosition(myLatlng);
				finishPoint.setMap(map);
				finishPoint.setTitle('Finish track: '+ tracks[findTrack(showTrackId)].track_name);
				finishPoint.setIcon(iconUrl+"ff0000");
				
				myLatlng=new google.maps.LatLng(markers_array[0].latitude,markers_array[0].longitude);
				startPoint.setPosition(myLatlng);
				startPoint.setMap(map);
				startPoint.setTitle('Start track: '+ tracks[findTrack(showTrackId)].track_name);
				startPoint.setIcon(iconUrl+"00a20b");

				map.panTo(myLatlng);
			}
			
			function removeTrack() {
				var confirm = document.getElementById("remTrackConfirm");
				$$a({
					type:'post',//тип запроса: get,post либо head
					url: url+'POSTremoveTrack.php',//url адрес файла обработчика
					data:{'track': showTrackId},//параметры запроса
					response:'text',//тип возвращаемого ответа text либо xml
					success:function (data) {//возвращаемый результат от сервера
						if(data=="deleted") {
							clearMap();
							load_tracks();
						} else {
							confirm.innerHTML = data;
							confirm.style.display = "inline-block";
						}
					}
				});
			}
			

			//^^^^^^^^^^^^^^^^^^^^^^^^^^It's all GOOGLE MAPS^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			//-------------------------------PAGES BLOCK--------------------------------------------
			function initialize() { //начальная инициализация мепс и ерс
				googleEarthInitOnUP();
				googleMapsInitOnUP();
				
			//	alert(location.href);
			}
			
			function replaceDOM(what,where) { //функция перемещения DOM элементов, в часности div maps&earth по страницам
				var repl = document.getElementById(what);
				var reference = document.getElementById(where);
				if (reference.nextSibling != repl) {
					reference.parentNode.insertBefore(repl, reference.nextSibling);
				}
			}

			function showPage(numPage) {
				if(numPage==0) {
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="block";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("UnionPage").style.display="none";
					
					replaceDOM("map3d", "map3d_ins_tag");
					getNewLook("def");
					clearInterval(loadObjectInfoTimer);
				}
				if(numPage==1) {
					document.getElementById("settings").style.display="block";
					document.getElementById("GoogleEarth").style.display="none";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("UnionPage").style.display="none";
					
					clearInterval(loadObjectInfoTimer);
				}
				if(numPage==3) { //show all objects control page
					document.getElementById("GoogleMaps").style.display="block";
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="none";
					document.getElementById("UnionPage").style.display="none";

					replaceDOM("mapG", "map_ins_tag");
					google.maps.event.trigger(map, 'resize');  
					clearInterval(loadObjectInfoTimer);
					clearMap();
					document.getElementById("ctrlTracking").checked = false;
				}
				if(numPage==4) { //show control page
					document.getElementById("UnionPage").style.display="block";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="none";

					replaceDOM("map3d", "map3d_ins_tag_up");
					replaceDOM("mapG", "map_ins_tag_up");
					google.maps.event.trigger(map, 'resize');  
					clearMap();
				}
			}
			//^^^^^^^^^^^^^^^^^^^^^^^^^PAGES BLOCK^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			
			function addObject() {
				var objName = document.getElementById("obj_name").value;
				var confirm = document.getElementById("obj_add_confirm");
				if(objName!="") {
					$$a({
						type:'post',//тип запроса: get,post либо head
						url:url+'POSTnewObject.php',//url адрес файла обработчика
						data:{'name':objName},//параметры запроса
						response:'text',//тип возвращаемого ответа text либо xml
						success:function (data) {//возвращаемый результат от сервера
							confirm.innerHTML = "Object "+objName+" added with id "+data+".";
							document.getElementById("obj_name").value=null;
							confirm.className = "set_confirm";
							confirm.style.display = "block";
						}
					});
				}
				else {
					confirm.innerHTML = "Enter some name for object!";
					confirm.className = "set_error";
					confirm.style.display = "block";
				}
			}
			
			function addUser() {
			 
				var userLogin = document.getElementById("user_login").value;
				var userName = document.getElementById("user_name").value;
				var userPassword = document.getElementById("user_password").value;
				var userRePassword = document.getElementById("user_repassword").value;
				var admLogin = '<?php echo $_SESSION['olvi']; ?>';
				var admPass = document.getElementById("admin_password").value;
				var confirm = document.getElementById("user_add_confirm");
				if(userLogin!="" && userName!="" && userPassword!="" && admPass!="") {
					if(userPassword == userRePassword) {
						$$a({
							type:'post',//тип запроса: get,post либо head
							url:url+'registration.php',//url адрес файла обработчика
							data:{'login':userLogin, 'userName':userName, 'userPassword':userPassword, 'admLogin': admLogin, 'admPass':admPass },//параметры запроса
							response:'text',//тип возвращаемого ответа text либо xml
							success:function (data) {//возвращаемый результат от сервера
								var answ = JSON.parse(data);
								if(Number(answ.code)==0) {
									confirm.innerHTML = answ.answ;
									confirm.className = "set_confirm";
									confirm.style.display = "block";
									document.getElementById("user_login").value=null;
									document.getElementById("user_name").value=null;
									document.getElementById("user_password").value=null;
									document.getElementById("user_repassword").value=null;
									document.getElementById("admin_password").value=null;
								}
								else {
									confirm.innerHTML = answ.answ;
									confirm.className = "set_error";
									confirm.style.display = "block";
								}
							}
						});
					}
					else {
						confirm.innerHTML = "Passwords must match!";
						confirm.className = "set_error";
						confirm.style.display = "block";
					}
				}
				else {
					confirm.innerHTML = "Some fields are empty!";
					confirm.className = "set_error";
					confirm.style.display = "block";
				}
			}
			
			function changePass() {
				var admNewPass = document.getElementById("admin_new_password").value;
				var admNewRePass = document.getElementById("admin_new_repassword").value;
				var admLogin = '<?php echo $_SESSION['olvi']; ?>';
				var admPass = document.getElementById("admin_current_password").value;
				var confirm = document.getElementById("pass_change_confirm");
				if(admNewPass!="" && admNewRePass!="" && admPass!="") {
					if(admNewPass == admNewRePass) {
						$$a({
							type:'post',//тип запроса: get,post либо head
							url:url+'POSTchPass.php',//url адрес файла обработчика
							data:{'login':admLogin, 'password':admPass, 'newPassword':admNewPass},//параметры запроса
							response:'text',//тип возвращаемого ответа text либо xml
							success:function (data) {//возвращаемый результат от сервера
								var answ = JSON.parse(data);
									if(Number(answ.code)==0) {
									confirm.innerHTML = answ.answ;
									confirm.className = "set_confirm";
									confirm.style.display = "block";
									document.getElementById("admin_new_password").value=null;
									document.getElementById("admin_new_repassword").value=null;
									document.getElementById("admin_current_password").value=null;
								}
								else {
									confirm.innerHTML = answ.answ;
									confirm.className = "set_error";
									confirm.style.display = "block";
								}
							}
						});
					}
					else {
						confirm.innerHTML = "Passwords must match!";
						confirm.className = "set_error";
						confirm.style.display = "block";
					}
				}
				else {
					confirm.innerHTML = "Some fields are empty!";
					confirm.className = "set_error";
					confirm.style.display = "block";
				}
			}
			
			function mes(th) { alert("Wow "+th); }
			
			var eggg=0;
			function egg() {
				eggg++;
				document.getElementById("camera1").src="camera.png";
				if(eggg==5) {
					eggg=0;
					document.getElementById("camera1").src="http://www.asn-news.ru/uploads/news/photo/l/Eye_of_Sauron.jpeg";
					setTimeout(egg, 2000);

				}
			}
		</script>

	</head>
	<body onload="initialize()">
			<?php 
				if(!isset($_SESSION['olvi'])) {
			?>
					<div class="login">
						<h2>Authorization:</h2>
						<form action="login.php" method="POST" >
							<input name="login" type="text" placeholder=" | Enter your login" size="45" required /><br/>
							<input name="password" type="password" placeholder=" | Enter your password" size="45" required />
							<input class="submit" type="submit" value="Enter"/>
						</form>
					</div>
					<?php
					if (isset($_SESSION['log'])) {
					?>
						<div class="error">
							<span>Wrong login or password</span>
						</div>
						
					<?php } ?>
				
				
			<?php } else {?>
	
		<section id="main_menu">
			<p align=center><input class="button_all" type="button" id="mm_ge" value="Objects control" onclick="showPage(0)">&nbsp
									<input class="button_all" type="button" id="mm_gm" value="Path constructor" onclick="showPage(3)">&nbsp
									<input class="button_all" type="button" id="mm_set" value="Settings" onclick="showPage(1)">&nbsp
									<input class="button_all" type="button" value="Logout" onclick="window.location='logout.php';"></p>
		</section>
		<section id="GoogleEarth">
			<div id="ge_left">
				<h2>List of objects</h2>
				<ul id="obj_list">
					<li>Loading...</li>
				</ul>
			</div>
			<div id="ge_right">
				<div id="map3d_ins_tag"></div>
				<div id="map3d"></div>
			</div>
		</section>
		
		<section id="settings" class="hidden">
			<div id="settings_wrapper">
				
				<div id="set_obj_area">
					<h3>Add new object</h3>
					<input type="text" id="obj_name" placeholder=" | Enter object name" size="45" /><br>
					<p align=center><input class="button_all" type="button" id="obj_add" value="Add" onclick="addObject()" /></p>
					<div class="set_error" id="obj_add_confirm"></div>
				</div>
				<div id="set_user_area">
					<h3>Add new operator</h3>
						<input type="text" id="user_login" placeholder=" | Enter login" size="45" /><br>
						<input type="text" id="user_name" placeholder=" | Enter name" size="45" /><br>
						<input type="password" id="user_password" placeholder=" | Enter password" size="45" /><br>
						<input type="password" id="user_repassword" placeholder=" | Retype password" size="45" /><br>
						<input type="password" id="admin_password" placeholder=" | Enter current password" size="45" /><br>
						<p align=center><input class="button_all" type="button" id="user_register" value="Register" onclick="addUser()"/></p>
					<div class="set_error" id="user_add_confirm"></div>
				</div>
				<div id="change_pass_area">
					<h3>Change your password</h3>
						<input type="password" id="admin_new_password" placeholder=" | Enter password" size="45" /><br>
						<input type="password" id="admin_new_repassword" placeholder=" | Retype password" size="45" /><br>
						<input type="password" id="admin_current_password" placeholder=" | Enter current password" size="45" /><br>
						<p align=center><input class="button_all" type="button" id="change_password" value="Change password" onclick="changePass()" /></p>
					<div class="set_error" id="pass_change_confirm"></div>
				</div>
			</div>
		</section>
		
		<section id="GoogleMaps" class="hidden">
			<div id="gm_menu">
				<input type="button" id="gm_ctrlCreate" class="button_all" value="Create a path"> 
				<label>Show track:<select id="gm_tracksSelector" class="button_all">
					<option value="null">select</option>
				</select></label>
				<input type="button" id="gm_removeTrack" class="button_all hidden" value="Remove track" onclick="removeTrack()">
				<div id="remTrackConfirm" class="hidden"></div>
				<input type="button" class="button_all" value="Clear map" onclick="clearMap()">
			</div>
			<div id="map_wrapper">
			<div id="map_ins_tag"></div>
			</div>
		</section>
		
		<section id="UnionPage" class="hidden">
			<!---->
			<div id="up_left">
				<div id="up_objNameId"></div>
				<div id="up_controls">
					<h3>Controller</h3>
					<div id="arrows_block">
						<input type="button" class="button_all" value="Up"></br>
						<input type="button" class="button_all" value="Left"><input type="button" class="button_all" value="Right"></br>
						<input type="button" class="button_all" value="Down">
					</div>
					<div id="controlBlock4">
						<input type="button" class="button_all" value="^"></br>
						<input type="button" class="button_all" value="Get"></br>
						<input type="button" class="button_all" value="v">
					</div>
					<div id="controlBlock2">
						<label>Follow track id:<select id="up_tracksSelector" onchange="changeObjTrack(this.value)" class="button_all">
							<option value="null">None</option>
						</select></label></br>
					</div>
					<div id="controlBlock1">
						<label><input type="checkbox" id="ctrlTracking"> Tracking</label>
					</div>
					<div id="controlBlock4">
						<input type="button" id="switchObj" onclick="objectSwitch()" class="button_all" value="">
						<input type="button" onclick="removeObj()" class="button_all" value="Remove">
					</div>
				</div>
				<div id="up_info">
					<h3>Information</h3>
					<div id="infoBlock">
						<p><b>Current position</b></p>
						<ul id="nav_curPos">
							<li>Latitude:</li>
							<li>Longitude:</li>
							<li>Altitude:</li>
						</ul>
						<p><b>Status </b></p>
						<p><b>Follows the track </b></p>
						<p><b>Id </b></p>
					</div>
				</div>
				<div id="up_cameras">
					<h3>Cameras</h3>
					<p align="center"><img id="camera1" src="camera.png" width="250px" onclick="egg()"></p>
				</div>
				
			</div>
			<div id="up_right">
				<div id="up_gEarth"><!-- It's gEarth paste block -->
					<div id="map3d_ins_tag_up"></div>
					
				</div>
				<div id="up_gMaps"><!-- It's gMaps paste block -->
					<div id="map_ins_tag_up"></div>
					<div id="mapG"></div>
				</div>
			</div>
		<!--	-->
		</section>
		<footer id="footer">O.L.Vi.</footer>
		<div id="delete_obj_form_container" class="hidden">
			<form id="delete_obj_prompt_form">
				You are confident in your decision? You can't cancel this action!</br>
				<input type="password" id="rem_pass" placeholder=" | Enter current password" required /></br>
				<input type="button" onclick="removeFormSubmit()" class="button_all" value="Yes">
				<input type="button" onclick="removeFormHide()" class="button_all" value="Cancel">
				<div id="confirm_remove_object"></div>
			</form>
		</div>
		<div id="create_track_form_container" class="hidden">
			<form id="create_track_prompt_form">
				Enter name for new path</br>
				<input type="type" id="path_name" placeholder=" | Enter path name" required /></br>
				<input type="button" onclick="createFormSubmit()" class="button_all" value="Create">
				<input type="button" onclick="createFormHide(0)" class="button_all" value="Cancel">
				<div id="confirm_create_object"></div>
			</form>
		</div>
		<?php } ?>
	</body>
</html>