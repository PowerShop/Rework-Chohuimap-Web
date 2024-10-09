<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenStreetMap with OpenLayers</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v10.2.1/ol.css">
    <script src="https://unpkg.com/@turf/turf/turf.min.js"></script>

    <!-- Map box -->

    <script src='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css' rel='stylesheet' />



    <!-- Custom CSS For MAP Page -->
    <link rel="stylesheet" href="../_dist/_css/_map.css">
</head>

<body>
    <div id="map"></div>
    <script src="https://cdn.jsdelivr.net/npm/ol@v10.2.1/dist/ol.js"></script>
    <script>
        // Load the OpenLayers map
        var layername = 'icons';
        var proj = 'epsg3857';
        var map = new ol.Map({
            target: 'map',
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.XYZ({
                        url: "http://ms.longdo.com/mmmap/img.php?zoom={z}&x={x}&y={y}&mode=" + layername + "&key=e5b6c5354d7dea400d9c2304526fae94&proj=" + proj
                    })
                    // source: new ol.source.OSM()
                })
            ],
            view: new ol.View({
                center: start_coordinate,
                zoom: 18
            }),
            loadTilesWhileAnimating: true,
            loadTilesWhileInteracting: true
        });

        // Get latitude and longitude from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const lat = urlParams.get('lat');
        const lon = urlParams.get('lon');

        // Set the destination coordinates
        var destination = [lon, lat];

        // Get the user's current location
        var start_coordinate = '';

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
                            // console.log('ตำแหน่งปัจจุบัน: ', coordinate);
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

            // SweetAlert loading
            Swal.fire({
                html: '<img src="../_dist/_img/pathway.gif" width="96px" height="96px"><br><b>กำลังค้นหาเส้นทาง</b>',
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Swal.showLoading()
                }
            });


            // Get the route details    
            getRouteDetails(startPoint, destination);

            // Get the route steps
            getRouteSteps(startPoint, destination);
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
                    $storeName = isset($_GET['storename']) ? $_GET['storename'] : 'ร้านค้าที่ไม่ระบุชื่อ';
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
            const routeUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[0]},${startPoint[1]};${destination[0]},${destination[1]}?steps=true&overview=full&geometries=geojson`;
            // console.log("routeUrl", routeUrl);
            fetch(routeUrl)
                .then(response => response.json())
                .then(data => {
                    const steps = data.routes[0].legs[0].steps;
                    const distance = data.routes[0].legs[0].distance / 1000; // Convert meters to kilometers
                    const stepsContainer = document.getElementById('steps');
                    stepsContainer.innerHTML = ''; // Clear previous steps

                    // If the user is within 0.01 km of the destination, show a success message
                    if (distance < 0.01) {
                        Swal.fire({
                            icon: 'success',
                            title: 'คุณเดินทางถึงที่หมายแล้ว',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }

                    // Debugging
                    console.log('Distance to destination:', distance);

                    const turnTranslations = {
                        'straight': {
                            text: 'ตรง',
                            icon: 'fas fa-arrow-up'
                        },
                        'left': {
                            text: 'เลี้ยวซ้าย',
                            icon: 'fas fa-arrow-left'
                        },
                        'right': {
                            text: 'เลี้ยวขวา',
                            icon: 'fas fa-arrow-right'
                        },
                        'uturn': {
                            text: 'เลี้ยว U-turn',
                            icon: 'fas fa-undo'
                        },
                        'sharp left': {
                            text: 'เลี้ยวซ้ายทันที',
                            icon: 'fas fa-arrow-left'
                        },
                        'sharp right': {
                            text: 'เลี้ยวขวาทันที',
                            icon: 'fas fa-arrow-right'
                        },
                        'slight left': {
                            text: 'เลี้ยวซ้ายเล็กน้อย',
                            icon: 'fas fa-arrow-left'
                        },
                        'slight right': {
                            text: 'เลี้ยวขวาเล็กน้อย',
                            icon: 'fas fa-arrow-right'
                        },
                        'fork left': {
                            text: 'เลี้ยวซ้ายที่แยกทาง',
                            icon: 'fas fa-arrow-left'
                        },
                        'fork right': {
                            text: 'เลี้ยวขวาที่แยกทาง',
                            icon: 'fas fa-arrow-right'
                        },
                        'roundabout left': {
                            text: 'เลี้ยวซ้ายที่วงเวียน',
                            icon: 'fas fa-arrow-left'
                        },
                        'roundabout right': {
                            text: 'เลี้ยวขวาที่วงเวียน',
                            icon: 'fas fa-arrow-right'
                        },
                        'exit left': {
                            text: 'เลี้ยวซ้ายที่ทางออก',
                            icon: 'fas fa-arrow-left'
                        },
                        'exit right': {
                            text: 'เลี้ยวขวาที่ทางออก',
                            icon: 'fas fa-arrow-right'
                        },
                        'default': {
                            text: '',
                            icon: 'fas fa-map-marker-alt'
                        }
                    };

                    steps.forEach((step, index) => {
                        let {
                            text: turns,
                            icon
                        } = turnTranslations[step.maneuver.modifier] || turnTranslations['default'];
                        let ref = step.ref ? `${turns}ที่ถนน ${step.ref}` : `${turns}ที่ถนน ไม่ทราบชื่อ`;

                        if (index === 0) {
                            ref = 'จุดเริ่มต้น';
                            turns = '';
                            icon = 'fas fa-flag-checkered';
                        } else if (index === steps.length - 1) {
                            ref = 'จุดหมายปลายทาง';
                            turns = '';
                            icon = 'fas fa-flag-checkered';
                        }

                        const stepText = new ol.Overlay({
                            position: ol.proj.fromLonLat([step.maneuver.location[0], step.maneuver.location[1]]),
                            element: document.createElement('div')
                        });

                        const stepElement = document.createElement('div');
                        stepElement.className = 'step-text';
                        stepElement.innerHTML = `<i class="${icon}"></i> ${ref}`;
                        stepsContainer.appendChild(stepElement);

                        const iconElement = document.createElement('i');
                        iconElement.className = icon;
                        iconElement.style.color = '#007bff';
                        iconElement.style.marginLeft = '5px'; // Add space between icon and text
                        iconElement.style.transform = 'scale(1.2)'; // Scale icon up

                        stepText.getElement().appendChild(document.createElement('br'));
                    });
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
            marker.getElement().innerHTML = '<div class="car-animation" aria-hidden="true"></div>';
            map.addOverlay(marker);

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    position => {
                        var userLocation = [position.coords.longitude, position.coords.latitude];
                        var snappedLocation = snapToRoute(userLocation, routeCoords);

                        marker.setPosition(ol.proj.fromLonLat(snappedLocation));

                        map.getView().setCenter(ol.proj.fromLonLat(snappedLocation));

                        // Update route line
                        var routeUrl = `https://router.project-osrm.org/route/v1/driving/${snappedLocation[0]},${snappedLocation[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson`;
                        fetch(routeUrl)
                            .then(response => response.json())
                            .then(data => {
                                Swal.close(); // Close the loading alert
                                var routeCoords = data.routes[0].geometry.coordinates.map(coord => ol.proj.fromLonLat(coord));
                                // New route layer
                                var routeFeature = new ol.Feature({
                                    geometry: new ol.geom.LineString(routeCoords)
                                });

                                var routeSource = new ol.source.Vector({
                                    features: [routeFeature]
                                });

                                var routeLayer = new ol.layer.Vector({
                                    source: routeSource,
                                    style: new ol.style.Style({
                                        stroke: new ol.style.Stroke({
                                            color: '#00aaff',
                                            width: 4
                                        })
                                    })
                                });

                                // Add end point icon
                                var destinationIcon = new ol.Feature({
                                    geometry: new ol.geom.Point(ol.proj.fromLonLat([destination[0], destination[1]]))
                                });

                                var iconStyleStop = new ol.style.Style({
                                    image: new ol.style.Icon({
                                        anchor: [0.5, 1],
                                        src: '../_dist/_img/end.png',
                                        scale: 0.05
                                    })
                                });

                                destinationIcon.setStyle(iconStyleStop);

                                var iconSource = new ol.source.Vector({
                                    features: [destinationIcon]
                                });

                                var iconLayer = new ol.layer.Vector({
                                    source: iconSource
                                });

                                // Remove existing route and icon layers
                                map.getLayers().forEach(layer => {
                                    if (layer.get('name') === 'route' || layer.get('name') === 'icon') {
                                        map.removeLayer(layer);
                                    }
                                });

                                // Add new route and icon layers
                                map.addLayer(routeLayer);
                                map.addLayer(iconLayer);
                                // map.addLayer(circleLayer);
                            })
                            .catch(error => console.error('Error fetching route:', error));

                        // Update the route details
                        getRouteDetails(snappedLocation, destination);
                        // หมุนแผนที่ตามทิศทางการเดินทาง
                        // var heading = position.coords.heading;
                        // marker.getElement().style.transform = `rotate(${heading}deg)`;
                        // map.getView().setRotation(-heading * Math.PI / 180);

                        // debug update user location
                        console.log('ตำแหน่งปัจจุบัน: ', snappedLocation);


                    },
                    error => {
                        console.error('Error tracking user location:', error);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 5000,
                        distanceFilter: 1,

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
            .catch(
                error => console.error('Error getting user position:', error),

                // แสดงข้อความเมื่อไม่สามารถระบุตำแหน่งปัจจุบันได้
                Swal.fire({
                    // icon: 'error',
                    html: '<img src="../_dist/_img/maps.gif" width="96px" height="96px"><br>กรุณาเปิด GPS หรืออนุญาตให้เว็บไซต์เข้าถึงตำแหน่งปัจจุบันของคุณ',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                }),

                // Try to get the user's location again
                getUserPosition()

            );

        // Custom control for centering on user location
        class CenterUserLocationControl extends ol.control.Control {
            constructor(opt_options) {
                const options = opt_options || {};

                const button = document.createElement('button');
                button.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';

                const element = document.createElement('div');
                element.className = 'ol-control ol-unselectable center-user-location';
                element.appendChild(button);

                super({
                    element: element,
                    target: options.target
                });

                // Click event to center the map on the user's location
                button.addEventListener('click', this.handleCenterUserLocation.bind(this), false);

            }

            handleCenterUserLocation() {
                // SweetAlert loading
                Swal.fire({
                    html: '<img src="../_dist/_img/pathway.gif" width="96px" height="96px"><br>กำลังค้นหาตำแหน่งปัจจุบัน',
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        // Swal.showLoading()
                    }
                });
                getUserPosition()
                    .then(userPosition => {
                        var userLocation = [userPosition[0], userPosition[1]];

                        // Use Turf.js to snap the user's location to the nearest point on the route
                        var routeUrl = `https://router.project-osrm.org/route/v1/driving/${userPosition[0]},${userPosition[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson`;
                        fetch(routeUrl)
                            .then(response => response.json())
                            .then(data => {
                                var routeCoords = data.routes[0].geometry.coordinates;
                                var snappedLocation = snapToRoute(userLocation, routeCoords);

                                // Close the loading alert once the animation is done
                                Swal.close();

                                // Smoothly animate the map to the new center
                                map.getView().animate({
                                    center: ol.proj.fromLonLat(snappedLocation),
                                    zoom: 18,
                                    duration: 3000 // duration in milliseconds
                                });
                            })
                            .catch(error => console.error('Error fetching route:', error));
                    })
                    .catch(error => console.error('Error getting user position:', error));
            }
        }

        // Add the custom control to the map
        map.addControl(new CenterUserLocationControl());
    </script>
</body>

</html>