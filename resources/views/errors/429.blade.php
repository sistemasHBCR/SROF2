@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Too Many Requests'))
@section('btn')
    <a href="{{ url('/') }}" class="btn btn-primary mb-3">Regresar a inicio</a>
    <a href="javascript(0);"onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar
        sesión</a>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>
@endsection
