@extends(backpack_view('blank'))


@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
        integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
        crossorigin="" />

    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
        integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
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
                <li class="breadcrumb-item text-capitalize"><a href="/admin/route">stops</a></li>
                <li class="breadcrumb-item text-capitalize active" aria-current="page">locate</li>
            </ol>
        </nav>
        <div id="map"></div>
    </div>

    <script>

        var map = L.map('map').setView([58.43027114868165, 37.91595181464992], 5);

        var tiles = L.tileLayer('http://geo.asmantiz.com/tile/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = L.marker([{{ $stop->location->latitude}}, {{ $stop->location->longitude}}]).addTo(map)
            .bindPopup('{{ $stop->name }}').openPopup();


    </script>

@endsection
