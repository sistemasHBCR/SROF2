@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized'))
@section('btn')
<a href="{{ url('/') }}" class="btn btn-primary mb-3">Regresar a inicio</a>
<a href="javascript(0);"onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>

<form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
    @csrf
</form>
@endsection
