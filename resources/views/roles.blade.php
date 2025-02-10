@extends('layout.main')

@section('title')
    Roles
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
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />

    <style>
        .dtrg-group {
            background-color: #e3e8ee !important;
        }
    </style>
@endsection

@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-2">Lista de roles</h4>

        <p>Un rol proporciona acceso a menús y funciones predefinidos para que, dependiendo del rol asignado,
            <br>un administrador pueda tener acceso a lo que el usuario necesita.
        </p>
        <!-- Role cards -->
        <div class="row g-4">
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="fw-normal">Usuarios totales: {{ $admins->users()->count() }}</h6>
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                @php
                                    $limit = 9; // Límite de usuarios a mostrar directamente
                                    $additionalCount = $admins->users->count() - $limit; // Cantidad de usuarios adicionales
                                @endphp

                                @foreach ($admins->users->sortBy('name')->take($limit) as $index => $user)
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        title="{{ $user->name }} {{ $user->last_name }}"
                                        class="avatar avatar-sm pull-up">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $user->avatar_class }}">
                                            {{ substr($user->name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                        </span>
                                    </li>
                                @endforeach

                                @if ($additionalCount > 0)
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        title="{{ $additionalCount }} más" class="avatar avatar-sm pull-up">
                                        <span
                                            class="avatar-initial rounded-circle bg-label-white"><b>+{{ $additionalCount }}</b></span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="role-heading">
                                <h4 class="mb-1">Administrador</h4>
                                <a href="javascript:;"><small>Todos los permisos</small></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="fw-normal">Usuarios totales: {{ $suspends->users()->count() }}</h6>
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                @php
                                    $limit = 9; // Límite de usuarios a mostrar directamente
                                    $additionalCount = $suspends->users->count() - $limit; // Cantidad de usuarios adicionales
                                @endphp

                                @foreach ($suspends->users->sortBy('name')->take($limit) as $index => $user)
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        title="{{ $user->name }} {{ $user->last_name }}"
                                        class="avatar avatar-sm pull-up">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $user->avatar_class }}">
                                            {{ substr($user->name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                        </span>
                                    </li>
                                @endforeach

                                @if ($additionalCount > 0)
                                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                        title="{{ $additionalCount }} más" class="avatar avatar-sm pull-up">
                                        <span
                                            class="avatar-initial rounded-circle bg-label-white"><b>+{{ $additionalCount }}</b></span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="role-heading">
                                <h4 class="mb-1">Suspendido</h4>
                                <a href="javascript:;"><small>Sin ningun acceso</small></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @foreach ($roles as $role)
                <div class="col-xl-4 col-lg-6 col-md-6" id="role{{ $role->id }}">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-normal">Total usuarios: {{ $role->users()->count() }}</h6>
                                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                    @php
                                        $limit = 9; // Límite de usuarios a mostrar directamente
                                        $additionalCount = $role->users->count() - $limit; // Cantidad de usuarios adicionales
                                    @endphp

                                    @foreach ($role->users->sortBy('name')->take($limit) as $index => $user)
                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                            title="{{ $user->name }} {{ $user->last_name }}"
                                            class="avatar avatar-sm pull-up">
                                            <span class="avatar-initial rounded-circle bg-label-{{ $user->avatar_class }}">
                                                {{ substr($user->name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                            </span>
                                        </li>
                                    @endforeach

                                    @if ($additionalCount > 0)
                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                            title="{{ $additionalCount }} más" class="avatar avatar-sm pull-up">
                                            <span
                                                class="avatar-initial rounded-circle bg-label-white"><b>+{{ $additionalCount }}</b></span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="role-heading">
                                    <h4 id="CardRolename{{ $role->id }}" class="mb-1">{{ $role->name }}</h4>
                                    @canany(['roles.edit'])
                                        <a href="javascript:;" data-bs-toggle="modal" data-id="{{ $role->id }}"
                                            data-bs-target="#addRoleModal" class="role-edit-modal mr-2"><small>Editar
                                                Rol</small></a> |
                                    @endcanany
                                    @canany(['roles.destroy'])
                                        <a href="javascript:;" data-id="{{ $role->id }}"
                                            class="role-remove-modal text-danger"><small>Eliminar Rol</small></a>
                                    @endcanany
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @can(['roles.create'])
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="row h-100">
                        <div class="col-sm-5">
                            <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-3">
                                <img src="../../assets/img/illustrations/bd.png" class="img-fluid" alt="Image"
                                    width="100" data-app-light-img="illustrations/bd.png"
                                    data-app-dark-img="illustrations/bd.png" />
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="card-body text-sm-end text-center ps-sm-0">
                                <button data-bs-target="#addRoleModal" data-bs-toggle="modal"
                                    class="btn btn-primary mb-3 text-nowrap add-new-role">
                                    Nuevo Rol
                                </button>
                                <p class="mb-0">Agregar rol, si no existe</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
            <div class="col-12">
                <!-- Permission Table -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title">Lista de permisos</h5>
                    </div>
                    <div class="card-header">
                        <h5 class="card-title">Filtros</h5>
                        <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                            <div class="col-md-4 product_status">
                                <select id="modules" class="form-select text-capitalize">
                                    <option value="">Modulos</option>
                                    @foreach (collect($permissions)->groupBy('module') as $module => $permission)
                                        <option value="{{ $module }}">{{ $module }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 product_category"></div>
                            <div class="col-md-4 product_stock"></div>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="tb-permissions"
                            class="datatables-permissions table border-top datatables-products table">
                            <thead class="border-top">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Modulo</th>
                                    <th>Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $idx => $permission)
                                    <tr>
                                        <td>{{ $permission->name }}</td>
                                        <td>{{ $permission->description }}</td>
                                        <td>{{ $permission->module }}</td>
                                        <td>
                                            @foreach ($permission->roles as $role)
                                                <span class="badge m-1"
                                                    style="background-color: {{ $role->color }}">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--/ Permission Table -->
            </div>
        </div>
        <!--/ Role cards -->

        <!-- Add Role Modal -->
        <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
                <div class="modal-content p-3 p-md-5">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <input type="hidden" id="roleid" />
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <h3 class="role-title">Agregar nuevo rol
                            </h3>
                            <p>Establecer permisos de roles</p>
                        </div>
                        <!-- Add role form -->
                        <form id="addRoleForm" class="row g-3">
                            <div class="col-12 mb-4">
                                <label class="form-label" for="modalRoleName">NOMBRE DE ROL</label>
                                <input type="text" id="modalRoleName" name="modalRoleName" class="form-control"
                                    placeholder="Nombre del rol" tabindex="-1" />
                            </div>
                            <div class="col-12">
                                <h5>Permisos del rol</h5>
                                <!-- Permission table -->
                                <div class="table-responsive">
                                    <table class="table table-flush-spacing">
                                        <tbody>
                                            <tr>
                                                <td class="text-nowrap fw-medium">
                                                    Acceso administrativo
                                                    <i class="bx bx-info-circle bx-xs" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Todos los accesos del sistema"></i>
                                                </td>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAll" />
                                                        <label class="form-check-label" for="selectAll"> Seleccionar todo
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            @foreach ($permissions as $idx => $permission)
                                                <tr>
                                                    <td class="text-nowrap fw-medium">{{ $permission->name }}</td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <div class="form-check me-3 me-lg-5">
                                                                <input class="form-check-input permission" type="checkbox"
                                                                    id="permission{{ $permission->id }}"
                                                                    value="{{ $permission->id }}" />
                                                                <label class="form-check-label"
                                                                    for="permission{{ $permission->id }}">
                                                                    {{ $permission->description }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Permission table -->
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" id="btnupdate" class="update btn btn-primary">Guardar</button>
                                <button type="submit" id="btnnew" class="new btn btn-primary">Crear</button>
                                <button type="reset" class="btn-cancel btn btn-label-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                        <!--/ Add role form -->
                    </div>
                </div>
            </div>
        </div>
        <!--/ Add Role Modal -->

        <!-- Edit Permission Modal -->
        <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3 p-md-5">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <h3>Editar permiso</h3>
                            <p>Edit permission as per your requirements.</p>
                        </div>
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading mb-2">Aviso</h6>
                            <p class="mb-0">
                                Dado que la funcionalidad de un permiso se basa en su nombre, Si editas el mismo, podrías
                                interrumpir la funcionalidad de los usuarios asociados a este. Asegúrate de estar
                                absolutamente seguro antes de continuar.
                            </p>
                        </div>
                        <form id="editPermissionForm" class="row" onsubmit="return false">
                            <div class="col-sm-9">
                                <label class="form-label" for="editPermissionName">Nombre del permiso</label>
                                <input type="text" id="editPermissionName" name="editPermissionName"
                                    class="form-control" placeholder="Permission Name" tabindex="-1" />
                            </div>
                            <div class="col-sm-3 mb-3">
                                <label class="form-label invisible d-none d-sm-inline-block">Button</label>
                                <button type="submit" class="btn btn-primary mt-1 mt-sm-0">Update</button>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editCorePermission" />
                                    <label class="form-check-label" for="editCorePermission"> Set as core permission
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Edit Permission Modal -->


    </div>
@endsection
@section('script')
    <!-- Toast JS-->
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
    <!-- JS SWEET ALERT -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/roles.js') }}"></script>
    <script src="{{ asset('assets/jsv3/modal-add-role.js') }}"></script>
@endsection
