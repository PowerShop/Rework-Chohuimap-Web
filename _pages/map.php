<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenStreetMap with OpenLayers</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v10.2.1/ol.css">
    <script src="https://unpkg.com/@turf/turf/turf.min.js"></script>
    <style>
        #map {
            width: 100%;
            height: 80vh;
        }

        .ol-control button {
            background-color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .center-user-location {
            top: 80px;
            /* Position below the zoom controls */
            left: .5em;
        }

        .info-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-item img {
            width: 20px;
            height: 20px;
        }

        .card {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 20px;
        }

        .card-text img {
            width: 25px;
            height: 25px;
        }

        .card-text b {
            font-size: 1.1rem;
        }

        .step-indicator img {
            width: 15px;
            height: 15px;
        }

        .user-location-marker {
            color: redc !important;
            /* Customize the color as needed */
            font-size: 24px !important;
            /* Customize the size as needed */
        }
    </style>
</head>

<body>
    <div id="map"></div>
    <script src="https://cdn.jsdelivr.net/npm/ol@v10.2.1/dist/ol.js"></script>
    <script>
        // Add loading with SweetAlert2 until the map is loaded and the user's position is obtained and the route is drawn and then close the loading
        Swal.fire({
            title: 'กำลังโหลดแผนที่',
            html: 'กรุณารอสักครู่...',
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });


        // When the map is loaded, close the loading
        var map = new ol.Map({
            target: 'map',
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            view: new ol.View({
                center: start_coordinate,
                zoom: 18
            })
        });


        // Coordinates for the destination (ร้านเจ๊จันทร์ บ้านห้วยผาก)
        // Get latitude and longitude from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const lat = urlParams.get('lat');
        const lon = urlParams.get('lon');

        // Set the destination coordinates
        var destination = [lon, lat];

        // Get the user's current location
        var start_coordinate = '';

        function userLocation() {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;
                var coordinate = lon + ',' + lat;
                start_coordinate = coordinate;
                // console.log('ตำแหน่งปัจจุบัน: ', coordinate);
            });
            return start_coordinate;
        }

        // Set the map center once the user's position is obtained
        getUserPosition()
            .then(userPosition => {
                var userLocation = ol.proj.fromLonLat(userPosition);
                map.getView().setCenter(userLocation);
            })
            .catch(error => console.error('Error getting user position:', error));

        // Function to get the user's current position
        function getUserPosition() {
            return new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        position => {
                            var lat = position.coords.latitude;
                            var lon = position.coords.longitude;
                            var coordinate = lon + ',' + lat;
                            start_coordinate = coordinate;
                            console.log('ตำแหน่งปัจจุบัน: ', coordinate);
                            resolve([position.coords.longitude, position.coords.latitude]);
                        },
                        error => {
                            reject(error);
                        }
                    );
                } else {
                    reject(new Error("Geolocation is not supported by this browser."));
                }
            });
        }



        // Function to draw the route
        function drawRoute(startPoint, destination) {

            // Get the route details
            getRouteDetails(startPoint, destination);

            // Get the route steps
            getRouteSteps(startPoint, destination);

            var routeUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[0]},${startPoint[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson`;
            // Use OSRM API to get the route

            fetch(routeUrl)
                .then(response => response.json())
                .then(data => {

                    var routeCoords = data.routes[0].geometry.coordinates.map(coord => ol.proj.fromLonLat(coord));
                    var routeFeature = new ol.Feature({
                        geometry: new ol.geom.LineString(routeCoords)
                    });

                    var vectorSource = new ol.source.Vector({
                        features: [routeFeature]
                    });

                    var vectorLayer = new ol.layer.Vector({
                        source: vectorSource,
                        style: new ol.style.Style({
                            stroke: new ol.style.Stroke({
                                color: '#00aaff',
                                width: 4
                            })
                        })
                    });

                    map.addLayer(vectorLayer);

                    // Section for adding the custom control to the map

                    // Add icon for start point (user's location) and destination
                    var startIcon = new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([data.routes[0].geometry.coordinates[0][0], data.routes[0].geometry.coordinates[0][1]]))
                    });

                    var destinationIcon = new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([data.routes[0].geometry.coordinates[data.routes[0].geometry.coordinates.length - 1][0], data.routes[0].geometry.coordinates[data.routes[0].geometry.coordinates.length - 1][1]]))
                    });

                    var iconStyleStart = new ol.style.Style({
                        image: new ol.style.Icon({
                            anchor: [0.5, 1],
                            src: '../_dist/_img/start.png',
                            scale: 0.05
                        })
                    });

                    var iconStyleStop = new ol.style.Style({
                        image: new ol.style.Icon({
                            anchor: [0.5, 1],
                            src: '../_dist/_img/end.png',
                            scale: 0.05
                        })
                    });

                    startIcon.setStyle(iconStyleStart);
                    destinationIcon.setStyle(iconStyleStop);

                    var vectorSource = new ol.source.Vector({
                        features: [startIcon, destinationIcon]
                    });

                    var vectorLayer = new ol.layer.Vector({
                        source: vectorSource
                    });

                    map.addLayer(vectorLayer);
                })
                .catch(error => console.error('Error fetching route:', error));
        }
    </script>

    <div class="container mt-3" id="route_detial">
        <div class="card border border-0">
            <div class="card-body">
                <!-- <h4 class="card-title">Title</h4> -->
                <!-- <button class="btn btn-primary" onclick="searchRoute()">ค้นหาตำแหน่งปัจจุบัน</button> -->
                <p class="card-text">
                    <img src="_dist/_img/circle_blue.png" alt="" srcset=""> <b><span id="start" style="vertical-align: middle; font-size: 18px;" class="ms-2">ตำแหน่งของคุณ</span></b>
                </p>
                <div style="text-align: start; padding-left: 7.5px;">
                    <p><img src="_dist/_img/circle_gray_mini.png" alt=""></p>
                    <p><img src="_dist/_img/circle_gray_mini.png" alt=""></p>
                    <p><img src="_dist/_img/circle_gray_mini.png" alt=""></p>
                </div>
                <p class="card-text">
                    <?php
                    $storeName = isset($_GET['storename']) ? $_GET['storename'] : 'Unknown Store';
                    ?>
                    <img src="_dist/_img/circle_orange.png" alt="" srcset=""> <b><span id="destination" style="vertical-align: middle; font-size: 18px;" class="ms-2"><?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?></span></b>
                </p>
                <p class="text-center">
                <div class="info-container">
                    <span class="info-item">
                        <img src="_dist/_img/pin.png" alt="">
                        <span id="distance"></span>
                    </span>

                    <span class="info-item">
                        <img src="_dist/_img/clock.png" alt="">
                        <span id="duration"></span>
                    </span>
                </div>
                </p>
            </div>
        </div>

    </div>

    <div class="container mt-3" id="route_steps">
        <div class="card border border-0">
            <div class="card-body">
                <h4 class="card-title">ขั้นตอนการเดินทาง</h4>
                <div id="steps" class="step-indicator">
                    <!-- <img src="_dist/_img/step.png" alt=""> -->
                </div>
            </div>
        </div>
    </div>
    <script>
        // Function to get the route details
        function getRouteDetails(startPoint, destination) {

            // Use OSRM API to get the route
            var routeUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[0]},${startPoint[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson`;
            fetch(routeUrl)
                .then(response => response.json())
                .then(data => {
                    var distance = data.routes[0].distance / 1000;
                    var duration = data.routes[0].duration / 60;

                    document.getElementById('distance').innerText = `${distance.toFixed(2)} กิโลเมตร`;
                    document.getElementById('duration').innerText = `${duration.toFixed(0)} นาที`;
                })
                .catch(error => console.error('Error fetching route:', error));
        }

        // Function to get the route steps
        function getRouteSteps(startPoint, destination) {
            // Use OSRM API to get the route
            var routeUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[0]},${startPoint[1]};${destination[0]},${destination[1]}?steps=true&overview=full&geometries=geojson`;
            console.log("routeUrl", routeUrl);
            fetch(routeUrl)
                .then(response => response.json())
                .then(data => {
                    var steps = data.routes[0].legs[0].steps;
                    var stepsContainer = document.getElementById('steps');
                    stepsContainer.innerHTML = ''; // Clear previous steps

                    for (var i = 0; i < steps.length; i++) {
                        var step = steps[i];

                        // Display step instructions
                        var ref = step.ref;
                        var turns = step.maneuver.modifier;
                        var icon = '';

                        // Convert turns to Thai and set icons
                        switch (turns) {
                            case 'straight':
                                turns = 'ตรง';
                                icon = 'fas fa-arrow-up';
                                break;
                            case 'left':
                                turns = 'เลี้ยวซ้าย';
                                icon = 'fas fa-arrow-left';
                                break;
                            case 'right':
                                turns = 'เลี้ยวขวา';
                                icon = 'fas fa-arrow-right';
                                break;
                            case 'uturn':
                                turns = 'เลี้ยว U-turn';
                                icon = 'fas fa-undo';
                                break;
                            case 'sharp left':
                                turns = 'เลี้ยวซ้ายทันที';
                                icon = 'fas fa-arrow-left';
                                break;
                            case 'sharp right':
                                turns = 'เลี้ยวขวาทันที';
                                icon = 'fas fa-arrow-right';
                                break;
                            case 'slight left':
                                turns = 'เลี้ยวซ้ายเล็กน้อย';
                                icon = 'fas fa-arrow-left';
                                break;
                            case 'slight right':
                                turns = 'เลี้ยวขวาเล็กน้อย';
                                icon = 'fas fa-arrow-right';
                                break;
                            case 'fork left':
                                turns = 'เลี้ยวซ้ายที่แยกทาง';
                                icon = 'fas fa-arrow-left';
                                break;
                            case 'fork right':
                                turns = 'เลี้ยวขวาที่แยกทาง';
                                icon = 'fas fa-arrow-right';
                                break;
                            case 'roundabout left':
                                turns = 'เลี้ยวซ้ายที่วงเวียน';
                                icon = 'fas fa-arrow-left';
                                break;
                            case 'roundabout right':
                                turns = 'เลี้ยวขวาที่วงเวียน';
                                icon = 'fas fa-arrow-right';
                                break;
                            case 'exit left':
                                turns = 'เลี้ยวซ้ายที่ทางออก';
                                icon = 'fas fa-arrow-left';
                                break;
                            case 'exit right':
                                turns = 'เลี้ยวขวาที่ทางออก';
                                icon = 'fas fa-arrow-right';
                                break;
                            default:
                                turns = '';
                                icon = 'fas fa-map-marker-alt';
                        }

                        // Check if ref is undefined
                        if (ref === undefined) {
                            ref = turns + 'ที่ถนน ' + 'ไม่ทราบชื่อ';
                        } else {
                            ref = turns + 'ที่ถนน ' + ref;
                        }

                        // Add start point
                        if (i === 0) {
                            ref = 'จุดเริ่มต้น';
                            turns = '';
                            icon = 'fas fa-flag-checkered';
                        }

                        // Last step is destination
                        if (i === steps.length - 1) {
                            ref = 'จุดหมายปลายทาง';
                            turns = '';
                            icon = 'fas fa-flag-checkered';
                        }

                        // console.log(ref);
                        var stepText = new ol.Overlay({
                            position: ol.proj.fromLonLat([step.maneuver.location[0], step.maneuver.location[1]]),
                            element: document.createElement('div')
                        });

                        // Use ref as the step text
                        stepText.getElement().innerHTML = `<img src="_dist/_img/pin.png" alt=""> <span>${ref}</span>`;
                        stepsContainer.appendChild(stepText.getElement());

                        // Add icon to the step text
                        var iconElement = document.createElement('i');
                        iconElement.className = icon;
                        iconElement.style.color = '#007bff';
                        iconElement.style.marginLeft = '5px'; // Add space between icon and text
                        iconElement.style.transform = 'scale(1.2)'; // Scale icon up
                        // stepText.getElement().appendChild(iconElement);

                        // Add a line break
                        stepText.getElement().appendChild(document.createElement('br'));

                    }
                })
                .catch(error => console.error('Error fetching route:', error));
        }


        // Function to snap the user's location to the nearest point on the route using Turf.js
        function snapToRoute(userLocation, routeCoords) {
            const userPoint = turf.point(userLocation);
            const line = turf.lineString(routeCoords);
            const snapped = turf.nearestPointOnLine(line, userPoint);
            return snapped.geometry.coordinates;
        }

        // Function to track the user's location in real-time
        function trackUserLocation(routeCoords) {
            const marker = new ol.Overlay({
                positioning: 'center-center',
                element: document.createElement('div'),
                stopEvent: false
            });
            marker.getElement().className = 'user-location-marker';
            marker.getElement().innerHTML = '<i class="fas fa-map-marker-alt" aria-hidden="true"></i>';
            map.addOverlay(marker);

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    position => {
                        var userLocation = [position.coords.longitude, position.coords.latitude];
                        var snappedLocation = snapToRoute(userLocation, routeCoords);

                        marker.setPosition(ol.proj.fromLonLat(snappedLocation));

                        map.getView().setCenter(ol.proj.fromLonLat(snappedLocation));
                    },
                    error => {
                        console.error('Error tracking user location:', error);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 5000
                    }
                );
            } else {
                console.error('Geolocation is not supported by this browser.');
            }
        }

        // Get the user's position and draw the route
        getUserPosition()
            .then(userPosition => {
                var startPoint = ol.proj.fromLonLat(userPosition);
                drawRoute(userPosition, destination);
                getRouteSteps(userPosition, destination);

                // Fetch the route coordinates for snapping
                var routeUrl = `https://router.project-osrm.org/route/v1/driving/${userPosition[0]},${userPosition[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson&steps=true`;
                fetch(routeUrl)
                    .then(response => response.json())
                    .then(data => {
                        var routeCoords = data.routes[0].geometry.coordinates;
                        trackUserLocation(routeCoords); // Start tracking the user's location
                    })
                    .catch(error => console.error('Error fetching route:', error));
            })
            .catch(error => console.error('Error getting user position:', error));

        // Custom control for centering on user location
        class CenterUserLocationControl extends ol.control.Control {
            constructor(opt_options) {
                const options = opt_options || {};

                const button = document.createElement('button');
                button.innerHTML = '<i class="fas fa-map-marker-alt" aria-hidden="true"></i>';

                const element = document.createElement('div');
                element.className = 'ol-control ol-unselectable center-user-location';
                element.appendChild(button);

                super({
                    element: element,
                    target: options.target
                });

                button.addEventListener('click', this.handleCenterUserLocation.bind(this), false);
            }

            handleCenterUserLocation() {
                getUserPosition()
                    .then(userPosition => {
                        var userLocation = [userPosition[0], userPosition[1]];

                        // Fetch the route coordinates for snapping
                        var routeUrl = `https://router.project-osrm.org/route/v1/driving/${userPosition[0]},${userPosition[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson`;
                        fetch(routeUrl)
                            .then(response => response.json())
                            .then(data => {
                                var routeCoords = data.routes[0].geometry.coordinates;
                                var snappedLocation = snapToRoute(userLocation, routeCoords);

                                map.getView().setCenter(ol.proj.fromLonLat(snappedLocation));
                                map.getView().setZoom(18);

                                // Create a marker element
                                const marker = new ol.Overlay({
                                    position: ol.proj.fromLonLat(snappedLocation),
                                    positioning: 'center-center',
                                    element: document.createElement('div'),
                                    stopEvent: false
                                });
                                marker.getElement().className = 'user-location-marker';
                                marker.getElement().innerHTML = '<i class="fas fa-map-marker-alt" aria-hidden="true"></i>';

                                // Add the marker to the map
                                map.addOverlay(marker);
                            })
                            .catch(error => console.error('Error fetching route:', error));
                    })
                    .catch(error => console.error('Error getting user position:', error));
            }
        }

        // Add the custom control to the map
        map.addControl(new CenterUserLocationControl());

        // Remove the loading when the map is loaded
        map.on('postrender', function() {
            Swal.close();
        });
    </script>
</body>

</html>