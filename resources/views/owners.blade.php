@extends('layout.main')

@section('title')
    Dueños
@endsection
<!-- Vendors CSS Datatable -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
<!-- Toast CSS-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
<!-- Vendors CSS SWEET ALERT-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />

@section('css')
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"></span> Dueños
        </h4>
        <!-- Lista importaciones   -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <div class="card-datatable table-responsive pt-0">
                                        <table id="tb-owner" class="table table-sm ">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Active</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($owners as $idx => $item)
                                                    <tr row="{{ $item->id }}">
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td class="name">{{ $item->name }}</td>
                                                        <td class="email">{{ $item->email }}</td>
                                                        <td class="status"> <a
                                                                class="active bx {{ $item->active == 'Y' ? 'bx-check text-success' : 'bx-x text-danger' }} bx-sm me-2"></a>
                                                        </td>
                                                        <td>
                                                            @canany(['owners.edit'])
                                                                <button type="button"
                                                                    class="data-owner  btn btn-primary btn-sm"
                                                                    data-bs-toggle="modal" data-bs-target="#dataOwner"
                                                                    id="{{ $item->id }}">Editar</button>
                                                            @endcanany
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Lista importaciones -->


        <!-- Modal owners-->
        <div class="modal fade" id="dataOwner" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <form class="modal-content" id="formowners" onsubmit="return false">
                    <div class="modal-header">
                        <input type="hidden" id="ownerid" />
                        <h5 class="modal-title" id="ownerTitle">Dueño</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 ">
                            <div class="col mb-0">
                                <label for="owner" class="form-label">Dueño</label>
                                <input type="text" id="name" name="name" maxlength="50" class="form-control"
                                    value="" required />
                            </div>
                            <div class="col mb-0">
                                <label for="email" class="form-label">Email (Opcional)</label>
                                <input type="email" id="email" name="email" class="form-control" value="" />
                            </div>
                            <div class="col mb-1">
                                <label for="active" class="form-label">Activo</label>
                                <select id="active" name="active" class="form-select" required>
                                    <option value="">Default select</option>
                                    <option value="Y">Si</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="owner_btnupdate" class="owner_update btn btn-primary">Guardar</button>
                        <button type="submit" id="owner_btnnew" class="owner_new btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    @endsection
    @section('script')
        <!-- Vendors JS Datatable -->
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
        <!-- Vendors JS MOMENT -->
        <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
        <!-- Toast JS-->
        <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
        <!-- JS SWEET ALERT -->
        <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
        <!-- Page JS -->
        <script src="{{ asset('assets/jsv3/owners.js') }}"></script>
    @endsection
