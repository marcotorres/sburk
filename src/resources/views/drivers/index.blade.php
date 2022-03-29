@extends('layouts.app')

@section('content')
    @can('create', App\Driver::class)
        <drivers-index v-bind:can-create-driver="true"></drivers-index>
    @else
        <drivers-index v-bind:can-create-driver="false"></drivers-index>
    @endcan
    
@endsection
