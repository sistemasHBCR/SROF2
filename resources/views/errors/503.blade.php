@extends('errors::minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __('Service Unavailable'))
@section('btn')
    <a href="{{ url('/') }}" class="btn btn-primary mb-3">Regresar a inicio</a>
    <a href="javascript(0);"onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar
        sesión</a>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>
@endsection
