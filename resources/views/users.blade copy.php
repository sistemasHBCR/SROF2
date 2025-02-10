@extends('layout.main')

@section('title')
    Usuarios
@endsection

@section('css')
    <!-- Toast CSS-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
    <!-- Vendors CSS SWEET ALERT-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
    <style>
        /*Ver escala de tablas mas pequeñas*/
        #dataUsers table td,
        #dataUsers table th {
            font-size: 0.9em;
        }
    </style>
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"></span>Usuarios
        </h4>
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-sm-6 col-lg-3">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-2" id="usersregisters">{{ $users->count() }}</h3>
                                    <p class="mb-0" >Registrados</p>
                                </div>
                                <div class="avatar me-sm-4">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="bx bx-group bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none me-4">
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div
                                class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
                                <div>
                                    <h3 class="mb-2" id="userswithoutroles">
                                        @php
                                            $countUsersWithoutRoles = 0;
                                        @endphp

                                        @foreach ($users as $user)
                                            @if ($user->roles->isEmpty())
                                                {{-- Incrementamos el contador si el usuario no tiene roles --}}
                                                @php
                                                    $countUsersWithoutRoles++;
                                                @endphp
                                            @endif
                                        @endforeach
                                        {{ $countUsersWithoutRoles }}
                                    </h3>
                                    <p class="mb-0">Sin Autorizar</p>
                                </div>
                                <div class="avatar me-sm-4">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="bx bx-error-alt bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-2" id="usersactives">
                                        @php
                                            $usersWithoutSuspendedRole = $users->reject(function ($user) {
                                                return $user->roles->contains('name', 'Suspendido');
                                            });
                                            // Obtener el número de usuarios con rol "Suspendido"
                                            $countUsersWithoutSuspendedRole = $usersWithoutSuspendedRole->count();
                                        @endphp
                                        {{ $countUsersWithoutSuspendedRole }}
                                    </h3>
                                    <p class="mb-0">Activos</p>
                                </div>
                                <div class="avatar me-lg-4">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="bx bx-user-check bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none">
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="mb-2" id="userssuspends">
                                        @php
                                            $usersWithSuspendedRole = $users->filter(function ($user) {
                                                return $user->roles->contains('name', 'Suspendido');
                                            });
                                            // Obtener el número de usuarios con rol "Suspendido"
                                            $countUsersWithSuspendedRole = $usersWithSuspendedRole->count();
                                        @endphp
                                        {{ $countUsersWithSuspendedRole }}

                                    </h3>
                                    <p class="mb-0">Suspendidos</p>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="bx bx-user-x bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users List Table -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title">Lista de usuarios</h5>
            </div>
            <div 
            class="card-datatable table-responsive" id="dataUsers">
                <table class="datatables-users table" id="tb-users">
                    <thead class="border-top">
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Role</th>
                            <th>Username</th>
                            <th>Email Verificado</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $idx => $user)
                            <tr row="{{ $user->id }}">
                                <td>{{ $idx + 1 }}</td>
                                <td class="date">
                                    {{ \Carbon\Carbon::parse($user->created_at)->locale('es')->format('d M Y H:i') }}</td>
                                <td class="name">
                                    <div class="d-flex justify-content-start align-items-center order-name text-nowrap">
                                        <div class="avatar-wrapper">
                                            <div class="avatar me-2">
                                                <span
                                                    class="avatar-initial rounded-circle bg-label-{{ $user->avatar_class }}">
                                                    {{ substr($user->name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="m-0">
                                                <a href="#" class="text-body">
                                                    {{ $user->name }} {{ $user->last_name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="roles">
                                    @foreach ($user->roles as $role)
                                        {{ $role->name }}
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </td>
                                <td class="username">{{ $user->username }}</td>

                                <td class="email_verified">
                                    @if (!empty($user->email_verified_at))
                                        Si
                                    @else
                                        No
                                    @endif
                                </td>
                                <td>
                                    @if (Auth::user()->id != $user->id)
                                        @canany(['users.edit'])
                                            <button type="button" class="data-user btn btn-primary btn-sm"
                                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser"
                                                id="{{ $user->id }}">Editar</button>
                                        @endcanany
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Offcanvas to add new user -->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser"
                aria-labelledby="offcanvasAddUserLabel">
                <div class="offcanvas-header border-bottom">
                    <h6 id="offcanvasAddUserLabel" class="offcanvas-title">Nuevo usuario</h6>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                    <input type="hidden" id="userid" value="">
                </div>
                <div class="offcanvas-body mx-0 flex-grow-0">
                    <form class="add-new-user pt-0" id="formusers" onsubmit="return false">
                        <div class="mb-3">
                            <label class="form-label" for="name">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                placeholder="Nombre" name="nombre" id="name" aria-label="" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="last_name">Apellido</label>
                            <input type="text" class="form-control  @error('last_name') is-invalid @enderror"
                                id="last_name" placeholder="Apellidos" name="last_name" aria-label="" required />
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email (No obligatorio)</label>
                            <input type="text" id="email"
                                class="form-control @error('email') is-invalid @enderror" placeholder="user@example.com"
                                aria-label="juser@example.com" name="email" />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="username">Usuario</label>
                            <input type="text" id="username"
                                class="form-control @error('username') is-invalid @enderror" placeholder="Usuario unico"
                                aria-label="username" name="username" required />
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="country">Roles</label>
                            <select id="roles" name="roles"class="form-select @error('roles') is-invalid @enderror"
                                required>
                                <option value="">Default select</option>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                                @endforeach
                            </select>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="form-check form-check-inline mt-3 change_password">
                                <input class="form-check-input" type="checkbox" id="change_password" value="">
                                <label class="form-check-label form-text text-primary" for="change_password">Nueva
                                    contraseña</label>
                            </div>
                            <div class="form-check form-check-inline change_next_login">
                                <input class="form-check-input" type="checkbox" id="change_next_login" value="Y">
                                <label class="form-check-label form-text text-primary" for="change_next_login">Cambiar al
                                    siguiente login</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" placeholder="Nueva contraseña" name="password" aria-label="" required
                                autocomplete="new-password" />
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                            <input type="password"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                id="password_confirmation" placeholder="Confirmar contraseña"
                                name="password_confirmation" aria-label="" required autocomplete="new-password" />
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" id="btnupdate" class="update btn btn-primary">Guardar</button>
                        <button type="submit" id="btnnew" class="new btn btn-primary">Crear</button>
                        <button type="reset" id="" class="btn-cancel btn btn-label-secondary"
                            data-bs-dismiss="offcanvas">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <!-- Toast JS-->
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
    <!-- JS SWEET ALERT -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/users.js') }}"></script>
@endsection
