@extends('layouts.app')

<script src="https://maps.googleapis.com/maps/api/js?key={{ $GOOGLE_MAPS_API_KEY }}&libraries=places&callback="></script>

@section('content')
    <parents-map></parents-map>
@endsection
