@extends('layouts.app')

<script src="https://js.stripe.com/v3/"></script>
@section('content')
    <pay stripe_publishable_key={{$stripe_publishable_key}}></pay>
@endsection