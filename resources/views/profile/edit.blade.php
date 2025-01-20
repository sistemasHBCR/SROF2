@extends('layout.main')

@section('title')
    Perfil
@endsection

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />

@section('css')
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light">
                <font style="vertical-align: inherit;">
                    <font style="vertical-align: inherit;">Perfil de usuario /</font>
                </font>
            </span>
            <font style="vertical-align: inherit;">
                <font style="vertical-align: inherit;"> Perfil
                </font>
            </font>
        </h4>


        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills flex-column flex-md-row mb-3">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i class="bx bx-user me-1"></i> Cuenta</a>
                    </li>
                </ul>
                <!--name-email-->
                <div class="card mb-4">
                    <h5 class="card-header">Detalles del perfil</h5>
                    <div class="card-body">
                        <!--
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src="../../assets/img/avatars/1.png" alt="user-avatar" class="d-block rounded"
                                            height="100" width="100" id="uploadedAvatar" />
                                        <div class="button-wrapper">
                                            <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                                <span class="d-none d-sm-block">Subir nueva foto</span>
                                                <i class="bx bx-upload d-block d-sm-none"></i>
                                                <input type="file" id="upload" class="account-file-input" hidden
                                                    accept="image/png, image/jpeg" />
                                            </label>
                                            <button type="button" class="btn btn-label-secondary account-image-reset mb-4">
                                                <i class="bx bx-reset d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </button>

                                            <p class="mb-0">JPG, GIF o PNG permitidos. Tamaño máximo de 800K</p>
                                        </div>
                                    </div>
                                -->
                    </div>
                    <hr class="my-0" />
                    <div class="card-body">
                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')
                            <div class="row">
                                @if (session('status'))
                                    <div class="alert alert-primary alert-dismissible d-flex align-items-center" role="alert">
                                        {{ session('status') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label">Nombre</label>
                                        <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ Auth::user()->name }}" autofocus required  @can('profile.details')  @else readonly @endcan/>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3 col-md-6">
                                    <label for="last_name" class="form-label">Apellido</label>
                                        <input class="form-control @error('last_name') is-invalid @enderror" type="text" name="last_name" id="last_name" value="{{ Auth::user()->last_name }}" required  @can('profile.details')  @else readonly @endcan/>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3 col-md-6">
                                    <label for="username" class="form-label">Username</label>
                                        <input class="form-control @error('username') is-invalid @enderror" type="text" name="username" id="username" value="{{ Auth::user()->username }}" required  @can('profile.details')  @else readonly @endcan/>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                        <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" id="email" value="{{ Auth::user()->email }}"  @can('profile.details')  @else readonly @endcan/>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                        <div>
                                            <p class="text-sm mt-2 text-gray-800">
                                                {{ __('Tu dirección de correo electrónico no ha sido verificada.') }}
                        
                                                @can('profile.details')
                                                    <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                                                    </button>
                                                @endcan
                                            </p>
                        
                                            @if (session('status') === 'verification-link-sent')
                                                <p class="mt-2 font-medium text-sm text-green-600">
                                                    {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        
                            @can('profile.details')
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary me-2">Guardar cambios</button>
                                    <button type="reset" class="btn btn-label-secondary">Cancelar</button>
                                </div>
                            @endcan
                        </form>
                        
                    </div>
                </div>

                <!--Password-->
                @can('profile.password')
                <div class="card mb-4">
                    <h5 class="card-header">Actualizar contraseña</h5>
                    <div class="card-body">
                        <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('put')
                            <div class="row">
                                @if (session('password-updated'))
                                    <div class="alert alert-primary alert-dismissible d-flex align-items-center"
                                        role="alert">
                                        {{ session('password-updated') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif
                                <div class="mb-3 col-md-4">
                                    <label for="update_password_current_password" class="form-label">Contraseña
                                        actual</label>
                                    <input class="form-control @error('current_password') is-invalid @enderror"
                                        type="password" id="update_password_current_password" name="current_password"
                                        autocomplete="current-password" value="" required/>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="update_password_password" class="form-label">Nueva contraseña</label>
                                    <input class="form-control @error('password') is-invalid @enderror" type="password"
                                        id="update_password_password" name="password" autocomplete="new-password"
                                        value="" required/>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="update_password_password_confirmation" class="form-label">Confirmar
                                        contraseña</label>
                                    <input class="form-control @error('password_confirmation') is-invalid @enderror"
                                        type="password" id="update_password_password_confirmation"
                                        name="password_confirmation" autocomplete="new-password" value="" required/>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan
                <!--Desactivar cuenta-->
                @can('profile.desactivate')
                <div class="card">
                    <h5 class="card-header">Desactivar cuenta</h5>
                    <div class="card-body">
                        <div class="mb-3 col-12 mb-0">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading mb-1">¿Estás seguro?</h6>
                                <p class="mb-0">Una vez realizada la accion, no podras volver a acceder al sistema hasta
                                    que un administrador te habilite de nuevo. Por favor esté seguro.</p>
                            </div>
                        </div>
                        <form id="formAccountDeactivation">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="accountActivation"
                                    id="accountActivation" />
                                <label class="form-check-label" for="accountActivation">Confirmo la desactivación de mi
                                    cuenta</label>
                            </div>
                            <button type="submit" class="btn btn-danger deactivate-account">Desactivar cuenta</button>
                        </form>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        var accountDesactivation = "{{ route('profile.desactive') }}";
    </script>


    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>


    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/pages-account-settings-account.js') }}"></script>
@endsection
