@extends(backpack_view('blank'))

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
        integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
        crossorigin="" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.css"
        crossorigin="" />

    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
        integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
        crossorigin=""></script>

    <script src="/leaflet.draw-src.js"
        crossorigin=""></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"
        crossorigin=""></script>

    <style>
        #map {
            height: 80vh;
            width: 100%
        }

    </style>

    <div>
        <nav aria-label="breadcrumb" class="d-none d-lg-block">
            <ol class="breadcrumb bg-transparent p-0 justify-content-end">
                <li class="breadcrumb-item text-capitalize"><a href="/admin/dashboard">Admin</a></li>
                <li class="breadcrumb-item text-capitalize"><a href="/admin/route">routes</a></li>
                <li class="breadcrumb-item text-capitalize active" aria-current="page">Draw {{$type}}</li>
            </ol>
        </nav>
        <div id="map"></div>
        <input type="hidden" name="type" id="type" value="{{$type}}">
        <input type="hidden" name="id" id="id" value="{{$id}}">
    </div>

    <script>
        var map = L.map('map').setView([58.43027114868165, 37.91595181464992], 5);
        var shiftLeft
        var editableLayers = L.featureGroup().addTo(map);
        var drawControl = new L.Control.Draw({
            edit: {
                featureGroup: editableLayers,
                remove: false
            },
            draw: {
                polygon: false,
                rectangle: false,
                circle: false,
                marker: false,
                circlemarker: false,
            }
        }).addTo(map);


        L.tileLayer('http://geo.asmantiz.com/tile/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('draw:created', function (e){
            var type = e.layerType,
            layer = e.layer;
            console.log(e.layer.toGeoJSON().geometry);
            if(type === 'polyline'){
                editableLayers.addLayer(layer);
            }
        });

        map.on('draw:edited', function (e) {
            var layers = e.layers;
            layers.eachLayer(function (layer) {
                if (layer instanceof L.Polyline){
                    $.ajax({
                        url: "http://localhost:8000/api/draw_post",
                        type: "post",
                        data: {
                            type: $('#type').val(),
                            id: $('#id').val(),
                            obj: layer.toGeoJSON().geometry
                        },
                        success: function (response) {
                            console.log(response)
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                        }
                    });
                }
            });
        });
    </script>

@endsection
