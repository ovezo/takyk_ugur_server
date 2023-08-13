
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">

  <meta charset="utf-8">
  <meta name='viewport' content='width=device-width, initial-scale=1'>

  <title>GPS</title>
  <style>
      *{
          width:100%;
          height: 100%;
          z-index: 1
      }
      form{
        width: 20%;
        position: absolute;
        z-index: 1000;
        top: 15%;
        background: #fff;
        left: 1%;
        border-radius: 15px;
      }
  </style>


  <script>
  if (document.location.search.match(/type=embed/gi)) {
    window.parent.postMessage("resize", "*");
  }
</script>


  <style>
    html { font-size: 15px; }
    html, body { margin: 0; padding: 0; min-height: 100%; }
    body { height:100%; display: flex; flex-direction: column; }
    .referer-warning {
      background: black;
      box-shadow: 0 2px 5px rgba(0,0,0, 0.5);
      padding: 0.75em;
      color: white;
      text-align: center;
      font-family: var(--cp-font-family);
      line-height: 1.2;
      font-size: 1rem;
      position: relative;
      z-index: 2;
    }
    .referer-warning h1 { font-size: 1.2rem; margin: 0; }
    .referer-warning a { color: #56bcf9; } /* $linkColorOnBlack */
  </style>
</head>

<body class="">

  <div id="result-iframe-wrap" role="main">

    <iframe
      id="result"
      srcdoc="
<!DOCTYPE html>
<html lang=&quot;en&quot; >

<head>

  <meta charset=&quot;UTF-8&quot;>

  <title>GPS</title>


  <link rel='stylesheet' href='https://unpkg.com/leaflet@1.3.4/dist/leaflet.css'>

<style>
@import url(&quot;https://fonts.googleapis.com/css?family=IBM+Plex+Sans:400,700&quot;);
* {
  margin: 0;
}

* + * {
  margin-top: 10px;
}

body {
  background-color: #eee;
  margin: 0;
}

body, button, input {
  font-family: &quot;IBM Plex Sans&quot;, sans-serif;
  font-size: 1.2rem;
  line-height: 1.5;
}

button, input {
  background-color: #eee;
  border: 1px #999 solid;
  border-radius: 4px;
  cursor: pointer;
  padding: 5px 15px;
  transition: all 250ms;
}

form{
    padding: 40px;
    width: 20%;
    position: absolute;
    z-index: 1000;
    top: 15%;
    background: #fff;
    left: 1%;
    border-radius: 15px;
}

label {
  display: block;
}

#map * + * {
  margin: 0;
}
</style>

  <script>
  window.console = window.console || function(t) {};
</script>



  <script>
  if (document.location.search.match(/type=embed/gi)) {
    window.parent.postMessage(&quot;resize&quot;, &quot;*&quot;);
  }
</script>


</head>

<body translate=&quot;no&quot; >
  <form>
  <div><label for=&quot;lat&quot;>Latitude</label><input type=&quot;text&quot; name=&quot;lat&quot; id=&quot;lat&quot;></div>
  <div><label for=&quot;lng&quot;>Longitude</label><input type=&quot;text&quot; name=&quot;lng&quot; id=&quot;lng&quot;></div>
</form>
    <script src=&quot;https://cpwebassets.codepen.io/assets/common/stopExecutionOnTimeout-1b93190375e9ccc259df3a57c1abc0e64599724ae30d7ea4c6877eb615f89387.js&quot;></script>

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.4/leaflet.js'></script>
      <script id=&quot;rendered-js&quot; >
var map;
var pin;
//var tilesURL = 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
var tilesURL = 'http://geo.asmantiz.com/tile/{z}/{x}/{y}.png';
var mapAttrib = '&amp;copy; <a href=&quot;http://www.openstreetmap.org/copyright&quot;>OpenStreetMap</a>';

// add map container
$('body').prepend('<div id=&quot;map&quot; style=&quot;height:100vh;z-index;width:100%;&quot;></div>');
MapCreate();

function MapCreate() {
  // create map instance
  if (!(typeof map == &quot;object&quot;)) {
    map = L.map('map', {
      center: [40, 0],
      zoom: 3 });

  } else
  {
    map.setZoom(3).panTo([40, 0]);
  }
  // create the tile layer with correct attribution
  L.tileLayer(tilesURL, {
    attribution: mapAttrib,
    maxZoom: 19 }).
  addTo(map);
}

var myIcon = L.icon({
  iconUrl: 'moving.svg',
  iconSize: [38, 95],
  iconAnchor: [22, 94],
  popupAnchor: [-3, -76],
});

map.on('click', function (ev) {
  $('#lat').val(ev.latlng.lat);
  $('#lng').val(ev.latlng.lng);
  if (typeof pin == &quot;object&quot;) {
    pin.setLatLng(ev.latlng);
  } else
  {
    pin = L.marker(ev.latlng, { riseOnHover: true, draggable: true });
    pin.addTo(map);
    pin.on('drag', function (ev) {
      $('#lat').val(ev.latlng.lat);
      $('#lng').val(ev.latlng.lng);
    });
  }
});

$('#lat').on('click', function(){
    var copyText = document.getElementById('lat');

    copyText.select();
    //copyText.setSelectionRange(0, 99999);

    //navigator.clipboard.writeText(copyText.value);
    document.execCommand('copy');

    alert('Copied the text: ' + copyText.value);
});

$('#lng').on('click', function(){
    var copyText = document.getElementById('lng');

    copyText.select();
    //copyText.setSelectionRange(0, 99999);

    //navigator.clipboard.writeText(copyText.value);
    document.execCommand('copy');

    alert('Copied the text: ' + copyText.value);
});
//# sourceURL=pen.js
    </script>



</body>

</html>

"
      sandbox="allow-downloads allow-forms allow-modals allow-pointer-lock allow-popups allow-presentation  allow-scripts allow-top-navigation-by-user-activation" allow="accelerometer; camera; encrypted-media; display-capture; geolocation; gyroscope; microphone; midi; clipboard-read; clipboard-write" allowTransparency="true"
      allowpaymentrequest="true" allowfullscreen="true" class="result-iframe">
      </iframe>

  </div>
</body>
</html>
