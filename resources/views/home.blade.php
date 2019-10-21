@extends('layouts.app')

@section('content')
    <chat :auth_user="{{Auth::user()}}"></chat>
@endsection
