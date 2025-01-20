@extends('layout.main')

@section('title')
    Residencias
@endsection
<!-- Vendors CSS Datatable -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
<!-- Toast CSS-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
<!-- Vendors CSS SWEET ALERT-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<!--SELECT-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />

@section('css')
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"></span> Residencias
        </h4>

        <!-- Lista residences  -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <div class="card-datatable table-responsive pt-0">
                                        <table id="tb-residence" class="table table-sm ">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Numero</th>
                                                    <th>Nombre</th>
                                                    <th>Owner</th>
                                                    <th>Active</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($residences as $idx => $item)
                                                    <tr row="{{ $item->id }}">
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td class="number">{{ $item->number }}</td>
                                                        <td class="name">{{ $item->name }}</td>
                                                        <td class="owner">
                                                            @foreach ($item->owner as $owner)
                                                                {{ $owner->name }}
                                                                @if (!$loop->last)
                                                                    /
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                        <td class="status"> <a
                                                                class="active bx {{ $item->active == 'Y' ? 'bx-check text-success' : 'bx-x text-danger' }} bx-sm me-2"></a>
                                                        </td>
                                                        <td>
                                                            @canany(['residences.edit'])
                                                                <button type="button"
                                                                    class="data-residence  btn btn-primary btn-sm"
                                                                    data-bs-toggle="modal" data-bs-target="#dataResidence"
                                                                    id="{{ $item->id }}">
                                                                    Editar</button>
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
        <!--/ Lista residences -->

        <!-- Modal residences-->
        <div class="modal fade" id="dataResidence" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-lg ">
                <form class="modal-content" id="formresidences" onsubmit="return false">
                    <div class="modal-header">
                        <input type="hidden" id="residenceid" />
                        <h5 class="modal-title" id="dataResidenceTitle">Añadir Residencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-0">
                                <label for="number" class="form-label">Numero</label>
                                <input type="number" id="number" name="number" pattern="[0-9]*" minlength="4"
                                    maxlength="4" value="" class="form-control" placeholder="" required />
                            </div>
                            <div class="col mb-0">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" id="name" name="name" class="form-control" value=""
                                    required />
                            </div>
                        </div>
                        <div class="row g-2 ">
                            <div class="col mb-1">
                                <label for="owner" class="form-label">Owner</label>
                                <select id="owner" name="owner[]" class="selectpicker w-100" data-style="btn-default"
                                multiple data-live-search="true" required>
                                @foreach ($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="col mb-1">
                                <label for="active" class="form-label">Activo</label>
                                <select id="active" name="active" class="form-select" required>
                                    <option value="">Default select</option>
                                    <option value="Y" selected>Si</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btnupdate" class="update btn btn-primary">Guardar</button>
                        <button type="submit" id="btnnew" class="new btn btn-primary">Crear</button>
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
        <!-- SELECT -->
        <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
        <!-- Page JS -->
        <script src="{{ asset('assets/jsv3/residences.js') }}"></script>
    @endsection
