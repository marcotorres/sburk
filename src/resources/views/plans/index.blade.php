@extends('layouts.app')

@section('content')
    <?php 
    if($can_create) {?>
        <plans-index :can_create_plan=true></plans-index>
    <?php } else { ?>
        <plans-index :can_create_plan=false></plans-index>
    <?php } ?>
@endsection
