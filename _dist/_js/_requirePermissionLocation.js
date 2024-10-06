// Check if Geolocation is supported
if ("geolocation" in navigator) {
    // Request the user's location
    navigator.geolocation.getCurrentPosition(
        function (position) {
            // Success callback
            console.log("Latitude: " + position.coords.latitude);
            console.log("Longitude: " + position.coords.longitude);
        },
        function (error) {
            // Error callback
            console.error("Error Code = " + error.code + " - " + error.message);
        }
    );
} else {
    console.error("Geolocation is not supported by this browser.");
}