@extends('layouts.app')

@section('content')
    <?php 
    if($can_create) {?>
        <schools-index :can_create_school=true></schools-index>
    <?php } else { ?>
        <schools-index :can_create_school=false></schools-index>
    <?php } ?>
@endsection
