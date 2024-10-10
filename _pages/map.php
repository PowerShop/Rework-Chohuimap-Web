<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenStreetMap with OpenLayers</title>


    <!-- Custom JS -->
    <script src="../_dist/_js/_goToTop.js"></script>

    <!-- Custom CSS For MAP Page -->
    <link rel="stylesheet" href="../_dist/_css/_map.css">
</head>

<body>
    <div id="map"></div>
    <div class="container mt-3" id="route_detial">
        <div class="card border border-0">
            <div class="card-body">
                <p class="card-text">
                    <img src="_dist/_img/circle_blue.png" alt="" srcset="">
                    <b><span id="start" style="vertical-align: middle; font-size: 18px;" class="ms-2">ตำแหน่งของคุณ</span></b>
                </p>
                <div style="display: flex; flex-direction: column; align-items: flex-start; margin-left: 3px;">
                    <p style="display: flex; align-items: center;">
                        <img src="_dist/_img/pin.png" alt="" width="16px" height="16px">
                        <span class="info-item">
                            &nbsp;<span id="distance"></span>
                        </span>
                    </p>
                    <p style="display: flex; align-items: center;">
                        <img src="_dist/_img/clock.png" alt="" width="16px" height="16px">
                        <span class="info-item">
                            &nbsp;<span id="duration"></span>
                        </span>
                    </p>
                </div>
                <p class="card-text">
                    <?php
                    $storeName = isset($_GET['storename']) ? $_GET['storename'] : 'ร้านค้าที่ไม่ระบุชื่อ';
                    ?>
                    <img src="_dist/_img/circle_orange.png" alt="" srcset="">
                    <b><span id="destination" style="vertical-align: middle; font-size: 18px;" class="ms-2"><?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?></span></b>
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

    <button onclick="topFunction()" id="go-to-top" title="Go to top"><i class="fa-solid fa-arrow-up"></i></button>
    <script>
        // Go to top function
        let mybutton = document.getElementById("go-to-top");

        // Check if the mybutton element exists before accessing its style property
        if (mybutton) {
            // When the user scrolls down 20px from the top of the document, show the button
            window.addEventListener("scroll", scrollFunction);

            function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    mybutton.style.display = "block";
                } else {
                    mybutton.style.display = "none";
                }
            }
        }
    </script>

    <!-- Main Script -->
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
                zoom: 16
            }),
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

            // Use OSRM API to get the route
            var routeUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[0]},${startPoint[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson`;
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

                    // Add new route and icon layers
                    map.addLayer(routeLayer);
                    map.addLayer(iconLayer);
                })
                .catch(error => {
                    console.error('Error fetching route:', error);

                    // If error = Failed to fetch or ERR_CONNECTION_TIMED_OUT try to fetch again
                    if (error.message === 'Failed to fetch' || error.message === 'ERR_CONNECTION_TIMED_OUT') {
                        console.log('Retrying to fetch route...');
                        drawRoute(startPoint, destination);
                    }
                });


            // Get the route details    
            getRouteDetails(startPoint, destination);

            // Get the route steps
            getRouteSteps(startPoint, destination);
        }
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

        var stepText;

        // Function to get the route steps
        function getRouteSteps(startPoint, destination) {
            // Use OSRM API to get the route
            const routeUrl = `https://router.project-osrm.org/route/v1/driving/${startPoint[0]},${startPoint[1]};${destination[0]},${destination[1]}?steps=true&overview=full&geometries=geojson`;
            // console.log("routeUrl", routeUrl);
            fetch(routeUrl)
                .then(response => response.json())
                .then(data => {

                    const steps = data.routes[0].legs[0].steps;
                    const stepsContainer = document.getElementById('steps');
                    stepsContainer.innerHTML = ''; // Clear previous steps

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
                        let ref = step.ref ? `${turns} ที่ถนน ${step.ref}` : `${turns} ที่ถนน ไม่ทราบชื่อ`;

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
                            element: document.createElement('div'),
                            positioning: 'center-center', // Position the text above the marker
                            stopEvent: false
                        });

                        const stepElement = document.createElement('div');
                        stepElement.className = 'step-text';
                        stepElement.innerHTML = `<i class="${icon}"></i> ${ref}`;
                        stepText.getElement().appendChild(stepElement);

                        map.addOverlay(stepText); // Add the overlay to the map

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

        // เพิ่มตัวแปรสำหรับเก็บสถานะว่าผู้ใช้ถึงจุดหมายแล้วหรือไม่
        let hasReachedDestination = false;
        var userLocation = [];
        var routeCoords = [];

        // Function to track the user's location in real-time
        function trackUserLocation(routeCoords) {
            const marker = new ol.Overlay({
                positioning: 'center-center',
                element: document.createElement('div'),
                stopEvent: false
            });

            const overLayText = new ol.Overlay({
                positioning: 'center-center',
                element: document.createElement('div'),
                stopEvent: false
            });

            // ใส่ข้อความบนตัว marker (รถ)
            overLayText.getElement().className = 'user-location-text';
            overLayText.getElement().innerHTML = 'คุณ';
            map.addOverlay(overLayText);

            // ตั้งค่าสำหรับ marker (รถ)
            marker.getElement().className = 'user-location-marker';
            marker.getElement().innerHTML = '<div class="car-animation" aria-hidden="true"></div>';
            map.addOverlay(marker);

            //  If user out of route, try to fetch route again
            if (userLocation.length > 0) {
                var snappedLocation = snapToRoute(userLocation, routeCoords);
                if (snappedLocation[0] === 0 && snappedLocation[1] === 0) {
                    console.log('User out of route, retrying to fetch route...');
                    drawRoute(userLocation, destination);
                }
            }

            // Rotate the marker based on the user's heading but only if the user is moving
            function rotateMarker(heading) {
                marker.getElement().style.transform = `rotate(${heading}deg)`;
            }

            // ฟังก์ชันสำหรับติดตามตำแหน่งปัจจุบันของผู้ใช้
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    position => {
                        userLocation = [position.coords.longitude, position.coords.latitude];
                        var snappedLocation = snapToRoute(userLocation, routeCoords);

                        // เรียก OSRM API เพื่อคำนวณเส้นทางจริง
                        var osrmUrl = `https://router.project-osrm.org/route/v1/driving/${userLocation[0]},${userLocation[1]};${destination[0]},${destination[1]}?overview=false&geometries=geojson`;

                        // หมุนรถตามทิศทางของผู้ใช้
                        // Rotate marker based on heading if available
                        if (position.coords.heading !== null) {
                            rotateMarker(position.coords.heading);
                        }

                        // New route layer
                        // var routeFeature = new ol.Feature({
                        //     geometry: new ol.geom.LineString([ol.proj.fromLonLat(userLocation)])
                        // });

                        // var routeSource = new ol.source.Vector({
                        //     features: [routeFeature]
                        // });

                        // var routeLayer = new ol.layer.Vector({
                        //     source: routeSource,
                        //     style: new ol.style.Style({
                        //         stroke: new ol.style.Stroke({
                        //             color: '#fe0000',
                        //             width: 4
                        //         })
                        //     })
                        // });

                        console.log('ตำแหน่งปัจจุบัน (userLocation): ', userLocation);

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
                        // map.getLayers().forEach(layer => {
                        //     if (layer.get('name') === 'route') {
                        //         map.removeLayer(layer);
                        //     }
                        // });

                        marker.setPosition(ol.proj.fromLonLat(snappedLocation));
                        overLayText.setPosition(ol.proj.fromLonLat(snappedLocation));
                        map.getView().setCenter(ol.proj.fromLonLat(snappedLocation));

                        // Add new route and icon layers
                        // map.addLayer(routeLayer);
                        map.addLayer(iconLayer);

                        // Debug update user location
                        console.log('ตำแหน่งปัจจุบัน (sanppedLocation): ', snappedLocation);

                        // เรียก API และคำนวณระยะทางจริง
                        fetch(osrmUrl)
                            .then(response => response.json())
                            .then(data => {
                                if (data.routes.length > 0) {
                                    var distance = data.routes[0].distance / 1000; // ระยะทางในหน่วยกิโลเมตร
                                    console.log('Distance to destination (OSRM):', distance);

                                    // อัพเดทระยะทางและเวลาที่เหลือ
                                    if (distance < 1) {
                                        document.getElementById('distance').innerText = `${(distance * 1000).toFixed(0)} เมตร`;
                                    } else {
                                        document.getElementById('distance').innerText = `${distance.toFixed(2)} กิโลเมตร`;
                                    }
                                    document.getElementById('duration').innerText = `${(data.routes[0].duration / 60).toFixed(0)} นาที`;

                                    // ตรวจสอบว่าผู้ใช้ถึงจุดหมายหรือไม่ และยังไม่ได้แจ้งเตือน
                                    if (distance < 1 && !hasReachedDestination) {

                                        // สร้างวงกลมรัศมี 50 เมตร รอบจุดหมายปลายทาง
                                        var circle = new ol.geom.Circle(ol.proj.fromLonLat(destination), 50);
                                        var circleFeature = new ol.Feature(circle);
                                        var circleSource = new ol.source.Vector({
                                            features: [circleFeature]
                                        });
                                        var circleLayer = new ol.layer.Vector({
                                            source: circleSource,
                                            style: new ol.style.Style({
                                                fill: new ol.style.Fill({
                                                    color: 'rgba(255, 0, 0, 0.1)'
                                                }),
                                                stroke: new ol.style.Stroke({
                                                    color: 'rgba(255, 0, 0, 0.5)',
                                                    width: 1
                                                })
                                            })
                                        });

                                        map.addLayer(circleLayer);

                                        Swal.fire({
                                            html: '<img src="../_dist/_img/travelling.gif" width="96px" height="96px"><br>คุณเดินทางถึงที่หมายปลายทางแล้ว',
                                            showConfirmButton: true,
                                            allowOutsideClick: false,
                                            confirmButtonText: 'สรุปผลการเดินทาง',
                                            // หลังจากกดปุ่ม OK ให้ส่งข้อมูลการเดินทางไปยังหน้า result.php
                                            // preConfirm: () => {
                                            //     window.location.href = `?page=result&start=${start_coordinate}&end=${destination}&distance=${distance}&duration=${data.routes[0].duration}`;
                                            // }
                                        });

                                        // เปลี่ยนสถานะเป็นถึงจุดหมายแล้ว เพื่อหยุดการแจ้งเตือนซ้ำ
                                        hasReachedDestination = true;
                                    }
                                }
                            });

                    },
                    error => {
                        console.error('Error tracking user location:', error);
                        // ทำการลองเรียกฟังก์ชันอีกครั้งหากเกิดข้อผิดพลาด
                        trackUserLocation(routeCoords);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 5000,
                    }
                );
            } else {
                console.error('Geolocation is not supported by this browser.');
            }
        }


        // ดึงตำแหน่งปัจจุบันของผู้ใช้
        getUserPosition()
            .then(userPosition => {
                var startPoint = ol.proj.fromLonLat(userPosition);
                drawRoute(userPosition, destination);
                getRouteSteps(userPosition, destination);

                // ดึงเส้นทางจาก OSRM API
                var routeUrl = `https://router.project-osrm.org/route/v1/driving/${userPosition[0]},${userPosition[1]};${destination[0]},${destination[1]}?overview=full&geometries=geojson&steps=true`;
                fetch(routeUrl)
                    .then(response => response.json())
                    .then(data => {
                        routeCoords = data.routes[0].geometry.coordinates;
                        trackUserLocation(routeCoords); // Start tracking the user's location
                    })
                    .catch(error => console.error('Error fetching route:', error));
            })
            .catch(
                error => console.error('Error getting user position:', error),

                // แสดงข้อความเมื่อไม่สามารถระบุตำแหน่งปัจจุบันได้
                Swal.fire({
                    html: '<img src="../_dist/_img/maps.gif" width="96px" height="96px"><br>กรุณาเปิด GPS หรืออนุญาตให้เว็บไซต์เข้าถึงตำแหน่งปัจจุบันของคุณ',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                }),

                // ลองเรียกฟังก์ชันอีกครั้งหากเกิดข้อผิดพลาด
                getUserPosition()

            );

        // Custom control to center the map on the user's location

        class CenterUserLocationControl extends ol.control.Control {
            constructor(opt_options) {
                const options = opt_options || {};

                const button = document.createElement('button');
                button.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';

                const toggleStepsButton = document.createElement('button');
                toggleStepsButton.innerHTML = '<i class="fa-solid fa-eye"></i>'; // Toggle icon

                const selectMarkerButton = document.createElement('button');
                selectMarkerButton.innerHTML = '<i class="fa-solid fa-map-marker-alt"></i>'; // Marker selection icon

                const element = document.createElement('div');
                element.className = 'ol-control ol-unselectable center-user-location';
                element.appendChild(button);
                element.appendChild(toggleStepsButton);
                element.appendChild(selectMarkerButton);

                super({
                    element: element,
                    target: options.target
                });

                this.stepsVisible = true; // Default to steps visible
                this.selectedMarker = null; // Default to no marker selected

                // Click event to center the map on the user's location
                button.addEventListener('click', this.handleCenterUserLocation.bind(this), false);

                // Click event to toggle route steps visibility
                toggleStepsButton.addEventListener('click', this.toggleRouteSteps.bind(this), false);

                // Click event to select a marker
                selectMarkerButton.addEventListener('click', this.handleSelectMarker.bind(this), false);
            }

            handleCenterUserLocation() {
                var snappedLocation = snapToRoute(userLocation, routeCoords);

                // Smoothly animate the map to the new center
                map.getView().animate({
                    center: ol.proj.fromLonLat(snappedLocation),
                    zoom: 18,
                    duration: 3000 // duration in milliseconds
                });
            }

            toggleRouteSteps() {
                this.stepsVisible = !this.stepsVisible;
                const toggleStepsButton = this.element.querySelector('button:nth-child(2)');
                toggleStepsButton.innerHTML = this.stepsVisible ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>';

                // Show or hide the route steps
                document.querySelectorAll('.step-text').forEach(step => {
                    step.style.display = this.stepsVisible ? 'block' : 'none';
                });
            }

            handleSelectMarker() {
                // Logic to select a marker
                // For demonstration, we'll just log a message
                console.log('Marker selection mode activated');
                // SweetAlert From to change car icon like motorcycle, bicycle, etc.
            }

            // Method to be called periodically to auto-center the map
            autoCenterMap() {
                if (this.autoCenterEnabled) {
                    var snappedLocation = snapToRoute(userLocation, routeCoords);
                    map.getView().setCenter(ol.proj.fromLonLat(snappedLocation));
                }
            }
        }

        map.addControl(new CenterUserLocationControl());
    </script>
</body>

</html>