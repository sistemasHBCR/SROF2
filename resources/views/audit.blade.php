@extends('layout.main')

@section('title')
    Auditoria
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
@endsection

@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-2">Auditoria de registros</h4>


        <div class="col-12">
            <!-- Table -->
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title">Filtros</h5>
                    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                        <div class="col-md-4 username">
                            <select id="select_username" class="form-select text-capitalize">
                                <option value="">USUARIO</option>
                                @foreach ($users as $username)
                                    <option value="{{ $username }}">{{ $username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 panel">
                            <select id="select_panel" class="form-select text-capitalize">
                                <option value="">PANEL</option>
                                @foreach ($panels as $panel)
                                    <option value="{{ $panel }}">{{ $panel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 module">
                            <select id="select_module" class="form-select text-capitalize">
                                <option value="">Modulo</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module }}">{{ $module }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-datatable table-responsive">
                    <table id="tb-audit" class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Panel</th>
                                <th>Modulo</th>
                                <th>Descripción</th>
                                <th>datatable</th>
                                <th>Fecha</th>
                                {{--<th></th>--}}
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0" id="audit-table-body">
                            @foreach ($data as $idx => $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->user->username }}</td>
                                    <td>{{ $data->action }}</td>
                                    <td>{{ $data->panel }}</td>
                                    <td>{{ $data->module }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->datatable }}</td>
                                    <td>{{ $data->created_at }}</td>
                                    {{-- -<td>
                                        <i id="details" style='cursor: pointer;' data-id="{{$data->id}}" class='text-primary bx bxs-plus-circle'
                                            data-bs-toggle="modal" data-bs-target="#modaldetails"></i>
                                        </td>
                                        - --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!--/  Table -->
        </div>
    </div>
    </div>
    <!--modal -->
    <div class="modal fade" id="modaldetails" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <h5>Metadata original:</h5>
                    <form class="row g-3" onsubmit="return false">
                        <div class="col-lg-10">
                            <label class="form-label" for="before">Datos en la base de datos antes de la
                                interracción</label>
                        </div>
                        <div class="col-lg-2 d-flex align-items-end">
                            <textarea id="txtbefore" class="form-control" rows="3" readonly></textarea>
                        </div>
                    </form>
                    <br>
                    <h5>Metadata creada:</h5>
                    <form class="row g-3" onsubmit="return false">
                        <div class="col-lg-10">
                            <label class="form-label" for="after">Datos en la base de datos despues de la
                                interracción</label>
                        </div>
                        <div class="col-lg-2 d-flex align-items-end">
                            <textarea id="txtafter" class="form-control " rows="3" readonly></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- / Pmodal -->
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
    <script src="{{ asset('assets/jsv3/audit.js') }}"></script>
    <script>
        var metadata = "{{ route('audit.data') }}";
    </script>
@endsection
