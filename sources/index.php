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
			var ge;//main GoogleEarth variable
			var la;//main GoogleEarth look at variable
			var reCord;
			var refIntId;//идентификатор таймера
			var flCreP=0;//флаг создания placemark метки на GoogleEarth. 0-не создавался ранее в этой сессии, 1-создавался
			var objects_array;

			//It's Google Earth observer variables
			var placemark;
			var icon;
			var style;
			var point;
			var lineStringPlacemark;
			var lineString;
			var lineStyle;
			//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			google.load("earth", "1", {"other_params":"sensor=false"});

			function googleEarthInitOnUP() {
				google.earth.createInstance('map3d', initCB, failureCB);
			}

			function initCB(instance) {
				ge = instance;
				ge.getWindow().setVisibility(true);
				ge.getLayerRoot().enableLayerById(ge.LAYER_BORDERS, true);
	  			ge.getNavigationControl().setVisibility(ge.VISIBILITY_AUTO);
				la = ge.createLookAt('');

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
			var placemarks = []; //массив меток обьектов на глобусе
			var local_point = []; //массив хранящий координаты для каждой из метки
			function load_objects() {
				$$a({
					type:'get',//тип запроса: get,post либо head
					url:'http://127.0.0.1/olvi/load_objects.php',//url адрес файла обработчика
					response:'text',//тип возвращаемого ответа text либо xml
					success:function (data) {//возвращаемый результат от сервера
						objects_array = JSON.parse(data);
						for(var i=0;i<objects_array.length;i++) {
							placemarks[i] = ge.createPlacemark(''); //создаем метку
							local_point[i] = ge.createPoint('');			//создаем координату
							
							local_point[i].setLatitude(Number(objects_array[i].lat)); //устанавливаем широту для координаты
							local_point[i].setLongitude(Number(objects_array[i].lng)); //устанавливаем долготу для координаты
							local_point[i].setAltitudeMode(ge.ALTITUDE_ABSOLUTE); //устанавливаем тип измерения высоты
							local_point[i].setAltitude(Number(objects_array[i].alt)); //устанавливаем высоту для координаты
							
							placemarks[i].setName(objects_array[i].obj_name); //присваиваем метке имя
							placemarks[i].setGeometry(local_point[i]); //устанавливаем координату для метки
							
							ge.getFeatures().appendChild(placemarks[i]); //выводим метку на глобус
						}
					}
				});				
			}
			
			function getPosition(flag) {
			$$a({
				type:'get',//тип запроса: get,post либо head
				url:'http://127.0.0.1/olvi/GETlocalPosition.php',//url адрес файла обработчика
				response:'text',//тип возвращаемого ответа text либо xml
				error:function(num){
					var arr=['Your browser does not support Ajax',
                        'Request failed',
                        'Address does not exist',
                        'The waiting time left'];
					alert(arr[num]);
				},
				success:function (data) {//возвращаемый результат от сервера
					if(flag) {
						reCord = JSON.parse(data);
//						alert("Inner "+reCord);
						getFly();
					}
				}
			});
			}
			
			var timerSet_flag=0;//флаг установки таймера 0-не установлен, 1-установлен
			function observingTimerSet(th) { //функция мост между двумя чекбоксами, устанавливает связь если отмечен один из чекбоксов, отмечается и другой
																												//кроме того в функции происходит щапуск таймера обновления или его остановка
				var chkB1 = document.getElementById('ctrlReGetPosition');
				var chkB2 = document.getElementById('ge_ctrlReGetPosition');
				var chkB3 = document.getElementById('gm_ctrlReGetPosition');
				if(th.checked&&!timerSet_flag) {
					getPosition(true);
					refIntId = setInterval(function() {getPosition(true);},2000);
					timerSet_flag=1;
					
					chkB1.checked = true;
					chkB2.checked = true;
					chkB3.checked = true;
				}
				else {
					timerSet_flag=0;
					clearInterval(refIntId);
					chkB1.checked = false;
					chkB2.checked = false;
					chkB3.checked = false;
				}
			}
			
			function setTrackingFlag(th) {  //функция мост между двумя чекбоксами, устанавливает связь если отмечен один из чекбоксов, отмечается и другой
				var chkB1 = document.getElementById('ctrlTracking');
				var chkB2 = document.getElementById('ge_ctrlTracking');
				var chkB3 = document.getElementById('gm_ctrlTracking');
				if(th.checked) {
					chkB1.checked = true;
					chkB2.checked = true;
					chkB3.checked = true;
				}
				else
				{
					chkB1.checked = false;
					chkB2.checked = false;
					chkB3.checked = false;
				}
			}
			
			var flightPlanCoordinates=[];
			var flightPath;
			function getFly() {
					//alert("getFly()"+reCord);
					var image = 'http://127.0.0.1/olvi/plane.png';
					var pos = new google.maps.LatLng(Number(reCord.latitude), Number(reCord.longitude));
					if(!flCreP) {
						placemark = ge.createPlacemark('');
						icon = ge.createIcon('');
						style = ge.createStyle(''); //создает новый стиль
						point = ge.createPoint('');
						
						lineStringPlacemark = ge.createPlacemark('');
						lineString = ge.createLineString('');
						
						
						icon.setHref(image);
						style.getIconStyle().setIcon(icon); //применяет значок к стилю
						placemark.setStyleSelector(style); //применяет стиль к метке
						
						lineStringPlacemark.setGeometry(lineString);
						lineString.setExtrude(true);
						lineString.setAltitudeMode(ge.ALTITUDE_ABSOLUTE);
						lineStringPlacemark.setStyleSelector(ge.createStyle(''));
						lineStyle = lineStringPlacemark.getStyleSelector().getLineStyle();
						lineStyle.setWidth(5);
						lineStyle.getColor().set('9900ffff');  // формат aabbggrr
						flCreP=1;
					}
					placemark.setName("Current altitude "+reCord.altitude+ " m");
					
					if(document.getElementById('ctrlTracking').checked) {
						la.set(Number(reCord.latitude),Number(reCord.longitude), 0, ge.ALTITUDE_RELATIVE_TO_GROUND, 0, 70, (Number(reCord.altitude)*4));
						ge.getView().setAbstractView(la);
						map.setCenter(pos);
					}
					
					point.setLatitude(Number(reCord.latitude));
					point.setLongitude(Number(reCord.longitude));
					point.setAltitudeMode(ge.ALTITUDE_ABSOLUTE);
					point.setAltitude(Number(reCord.altitude));
					placemark.setGeometry(point);
					ge.getFeatures().appendChild(placemark);

					lineString.getCoordinates().clear();
					lineString.getCoordinates().pushLatLngAlt(Number(reCord.latitude), Number(reCord.longitude), Number(reCord.altitude));

					ge.getFeatures().appendChild(lineStringPlacemark);
					
					
					locPoint.setMap(map);
					locPoint.setPosition(pos);
					locPoint.setIcon(image);
					
					if(document.getElementById('gm_ctrlDrawTrack').checked) {
						flightPlanCoordinates.push(pos);
						flightPath.setPath(flightPlanCoordinates);
					//	flightPath.setOptions(polylineOptions);
						flightPath.setMap(map);
					}
					
					document.getElementById("nav_curPos").innerHTML = "Latitude: " + reCord.latitude + "</br>Longitute: " + reCord.longitude +
																											"</br>Altitude: " + reCord.altitude;
			}
			//^^^^^^^^^^^^^^^^^^^^^^^^^It's all GOOGLE EARTH^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			//--------------------------------It's all GOOGLE MAPS---------------------------------
			var locPoint;
			var polylineOptions = {
				strokeColor: '#0000FF',
				strokeWeight: 2
			};
			var map;
			function googleMapsInitOnUP() {
			
				var mapOptions = {
					center: new google.maps.LatLng(49.999, 36.250),
					zoom: 17,
					scaleControl: true,
					zoomControlOptions: {
						style: google.maps.ZoomControlStyle.DEFAULT
					},
					mapTypeId: google.maps.MapTypeId.HYBRID
				};
				map = new google.maps.Map(document.getElementById("mapG"), mapOptions);      
				
				locPoint = new google.maps.Marker({});
				flightPath = new google.maps.Polyline(polylineOptions);
				
				google.maps.event.addDomListener(window, 'load', googleMapsInitOnUP);
			}

			//^^^^^^^^^^^^^^^^^^^^^^^^^^It's all GOOGLE MAPS^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			//---------------------------------It's all UNION PAGE------------------------------------
			function camera_load() { //функция включения камеры
				if(document.getElementById("cameraOnOff").checked==true) 
					document.getElementById("camera1").style.display="block";
				else
					document.getElementById("camera1").style.display="none";
			}
			
			//^^^^^^^^^^^^^^^^^^^^^^^^^^^It's all UNION PAGE^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			//-------------------------------PAGES BLOCK--------------------------------------------
			function initialize() { //начальная инициализация мепс и ерс
				googleEarthInitOnUP();
				googleMapsInitOnUP();
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
				//	document.getElementById("main_page").style.display="none";
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="block";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("UnionPage").style.display="none";
					
					replaceDOM("map3d", "map3d_ins_tag");
					document.getElementById("map3d").style.height= '95%';
				}
				if(numPage==1) {
					document.getElementById("settings").style.display="block";
				//	document.getElementById("main_page").style.display="none";
					document.getElementById("GoogleEarth").style.display="none";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("UnionPage").style.display="none";
				}
				if(numPage==2) {
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="none";
					document.getElementById("main_page").style.display="block";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("UnionPage").style.display="none";
				}
				if(numPage==3) {
					document.getElementById("GoogleMaps").style.display="block";
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="none";
				//	document.getElementById("main_page").style.display="none";
					document.getElementById("UnionPage").style.display="none";

					replaceDOM("mapG", "map_ins_tag");
					document.getElementById("mapG").style.height= '95%';
					google.maps.event.trigger(map, 'resize');  
				}
				if(numPage==4) {
					document.getElementById("UnionPage").style.display="block";
					document.getElementById("GoogleMaps").style.display="none";
					document.getElementById("settings").style.display="none";
					document.getElementById("GoogleEarth").style.display="none";
				//	document.getElementById("main_page").style.display="none";

					replaceDOM("map3d", "map3d_ins_tag_up");
					replaceDOM("mapG", "map_ins_tag_up");
					google.maps.event.trigger(map, 'resize');  
				}
			}
			//^^^^^^^^^^^^^^^^^^^^^^^^^PAGES BLOCK^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			
			
			function addObject() {
				var objName = document.getElementById("obj_name").value;
				var confirm = document.getElementById("obj_add_confirm");
				if(objName!="") {
					$$a({
						type:'post',//тип запроса: get,post либо head
						url:'http://127.0.0.1/olvi/POSTnewObject.php',//url адрес файла обработчика
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
//					alert('<?php echo $_SESSION['olvi']; ?>');
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
							url:'http://127.0.0.1/olvi/registration.php',//url адрес файла обработчика
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
							url:'http://127.0.0.1/olvi/POSTchPass.php',//url адрес файла обработчика
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
			<p align=center><!--<input type="button" id="mm_mp" value="Main Page" onclick="showPage(2)">&nbsp-->
									<input class="button_all" type="button" id="mm_ge" value="Google Earth" onclick="showPage(0)">&nbsp
									<input class="button_all" type="button" id="mm_gm" value="Google Maps" onclick="showPage(3)">&nbsp
									<!--<input type="button" id="mm_cp" value="Control Page" onclick="showPage(4)">&nbsp-->
									<input class="button_all" type="button" id="mm_set" value="Settings" onclick="showPage(1)">&nbsp
									<input class="button_all" type="button" value="Logout" onclick="window.location='logout.php';"></p>
		</section>
		<!--<section id="main_page">
			<p align=center>It's main page. You can choose one of menu buttons. And may the force be with you!</br>
			<img src="http://imgs.steps.dragoart.com/how-to-draw-chibi-darth-vader-step-6_1_000000013000_5.jpg" width=200px></p>
			
		</section>-->
		<section id="GoogleEarth">
			<div id="ge_menu">
				<input type="button" onclick="load_objects()" id='sCenter' value="Go" /> <!--кнопка одиночного запуска слежения, без таймера-->
				&nbsp <input type="checkbox" onclick="observingTimerSet(this)" id="ge_ctrlReGetPosition">Observation
				&nbsp <input type="checkbox" onclick="setTrackingFlag(this)" id="ge_ctrlTracking">Tracking 
				<!--&nbsp <input type="checkbox" onclick="gps()" id="ctrlGPS" value="Load position">Test GPS data-->
			</div>
			<div id="map3d_ins_tag"></div>
			<div id="map3d"></div>
		</section>
		
		<section id="settings" class="hidden">
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
		</section>
		
		<section id="GoogleMaps" class="hidden">
			<div id="gm_menu">
				&nbsp <input type="checkbox" onclick="observingTimerSet(this)" id="gm_ctrlReGetPosition">Observation
				&nbsp <input type="checkbox" onclick="setTrackingFlag(this)" id="gm_ctrlTracking">Tracking
				&nbsp | &nbsp
				&nbsp <input type="button" id="gm_ctrlCreate" value="Create a path"> 
				&nbsp <input type="checkbox" id="gm_ctrlDrawTrack">Draw track 
				&nbsp <input type="button" id="gm_ctrlClear" value="Clear map" onclick="reInitialize()">
			</div>
			<div id="map_ins_tag"></div>
		</section>
		
		<section id="UnionPage" class="hidden">
			<!---->
			<div id="up_right">
				<div id="up_gEarth"><!-- It's gEarth paste block -->
					<div id="map3d_ins_tag_up"></div>
					
				</div>
				<div id="up_gMaps"><!-- It's gMaps paste block -->
					<div id="map_ins_tag_up"></div>
					<div id="mapG"></div>
				</div>
			</div>
			<div id="up_left">
				<div id="up_cameras">
					<h3>Cameras</h3>
					<img id="camera1" class="hidden" src="IMG_1934.jpg" height=85%>
				</div>
				<div id="nav_data">
					<h3>Navigation</h3>
					<div id="navBlock1">
						<p>Current position</p>
						<p id="nav_curPos">Latitude:</br>
						Longitude:</br>
						Altitude:</p>
					</div>
					<div id="navBlock2">
						<p>Mouse position</p>
						<p id="nav_mousePos">Latitude:</br>
						Longitude:</p>
					</div>
				</div>
				<div id="up_controls">
					<h3>Control panel</h3>
					<div id="controlBlock1">
						<input type="checkbox" onclick="observingTimerSet(this)" id="ctrlReGetPosition">Observation</br>
						<input type="checkbox" onclick="setTrackingFlag(this)" id="ctrlTracking">Tracking 
					</div>
					<div id="controlBlock2">
						<input type="checkbox" onclick="camera_load()" id="cameraOnOff">Camera</br>
						<input type="checkbox" id="onBoardGPS">OnBoard GPS
					</div>
					<div id="controlBlock3">
						<input type="button" value="Up"></br>
						<input type="button" value="Left"><input type="button" value="Right"></br>
						<input type="button" value="Down">
					</div>
					<div id="controlBlock4">
						<input type="button" value="^"></br>
						<input type="button" value="v">
					</div>
					<div id="controlBlock5">
						<input type="button" value="Get">
					</div>
				</div>
			</div>
		<!--	-->
		</section>
		<?php } ?>
	</body>
</html>