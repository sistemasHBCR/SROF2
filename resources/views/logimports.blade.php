@extends('layout.main')

@section('title')
    Registro de importaciones
@endsection
<!-- Complemento refresh cards CSS-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/spinkit/spinkit.css') }}" />
<!-- Vendors CSS Datatable -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
<!-- Vendors CSS TOAST-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
<!-- Vendors CSS SWEET ALERT-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<!-- Vendors CSS PICKER-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
<!--Css datatable component-->
<link rel="stylesheet" href="{{ asset('assets/css/_tables.scss') }}" />

@section('css')
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"></span> Registros de importaciones
        </h4>
        <!-- Lista importaciones   -->

        <input type="hidden" id="dateNow" value="{{ $dateNow->format('Y-m-d') }}">

        <div class="row" id="dataimports">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="tb-imports" class="table datatable datatable-table">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Subido por</th>
                                                <th>Plantilla</th>
                                                <th>Check Mantenimiento</th>
                                                <th>Check Finanzas</th>
                                                <th>Estado</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 12; $i >= 1; $i--)
                                                @php
                                                    $monthName = ucfirst(
                                                        \Carbon\Carbon::create(null, $i, 1)->locale('es')->monthName,
                                                    );
                                                    // Filtrar las importaciones que comienzan en el mes específico
                                                    $filteredImports = $imports->filter(function ($item) use ($i) {
                                                        return \Carbon\Carbon::parse($item->period->start)->month == $i;
                                                    });

                                                    // Obtener la versión más alta del mes
                                                    $maxVersion = $filteredImports->max('version');
                                                @endphp
                                                @if ($filteredImports->isNotEmpty())
                                                    <tr class="table-light">
                                                        <td><b>{{ $monthName }}</b></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    @foreach ($filteredImports as $item)
                                                        @php
                                                            // Determinar si la versión es la más alta o no
                                                            $versionClass =
                                                                $item->version == $maxVersion
                                                                    ? 'text-success'
                                                                    : 'text-danger';
                                                        @endphp
                                                        <tr row="{{ $item->id }}">
                                                            <td><b>{{ \Carbon\Carbon::parse($item->created_at)->locale('es')->isoFormat('DD MMM YYYY') }}</b>
                                                            </td>
                                                            <td>{{ $item->uploadedBy->name }}
                                                                {{ $item->uploadedBy->last_name }}</td>
                                                            <td>
                                                                {{ $item->period->templateutilities == 'onlyconsumption' ? 'Solo consumos' : 'Full template' }}.
                                                                <small class="{{ $versionClass }} fw-normal d-block">
                                                                    V{{ $item->version }}
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <label class="maintenanceApprovedBy">
                                                                    {{ $item->maintenanceApprovedBy ? $item->maintenanceApprovedBy->name : '' }}
                                                                    {{ $item->maintenanceApprovedBy ? $item->maintenanceApprovedBy->last_name : '' }}
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <label class="financeApprovedBy">
                                                                    {{ $item->financeApprovedBy ? $item->financeApprovedBy->name : '' }}
                                                                    {{ $item->financeApprovedBy ? $item->financeApprovedBy->last_name : '' }}
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="status badge rounded-pill  bg-label-{{ $item->status->class }} me-1">
                                                                    {{ $item->status->name }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button type="button" data-bs-toggle="tooltip" data-bs-offset="0,8" data-bs-placement="top"
                                                                    data-bs-custom-class="tooltip-primary" data-bs-original-title="Abrir"
                                                                    class="data-import btn btn-outline-primary btn-xs"
                                                                    template="{{$item->period->templateutilities}}"
                                                                    statusparameters="{{$item->period->parametersutilities}}"
                                                                    token="{{ $item->import_token }}">
                                                                    <i class='bx bx-window-open'></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endfor
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal tabla -->
        <div class="modal fade" id="dataUtilities" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <input id="idimport" hidden>
                        <h4 class="modal-title" id="text-data">Datos Excel</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card-headertable-responsive text-nowrap">
                            <div class="row">
                                <div class="col-12">
                                    <div id="info-check" class="alert" role="alert">
                                        <h6 class="alert-heading mb-1"></h6>
                                        <span></span>
                                    </div>
                                    <hr class="m-0" />
                                    <div class="row gy-4">
                                        <!-- Accordion  -->
                                        <div class="col-md">
                                            <div class="accordion mt-3 accordion-header-primary" id="accordionStyle1">
                                                <div class="accordion-item card">
                                                    <h2 class="accordion-header">
                                                        <button type="button" class="accordion-button collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#accordionStyle1-1"
                                                            aria-expanded="false">
                                                            Validación entre departamentos
                                                        </button>
                                                    </h2>

                                                    <div id="accordionStyle1-1" class="accordion-collapse collapse"
                                                        data-bs-parent="#accordionStyle1">
                                                        <div class="accordion-body">
                                                            <!-- Aprobación -->
                                                            <div class="row row-bordered g-0">
                                                                <!-- Mantenimiento -->
                                                                <div class="col-md p-4">
                                                                    <small
                                                                        class="text-light fw-medium d-block">Mantenimiento</small>
                                                                    <small class="text-light fw-medium d-block"
                                                                        id="usermaintenance"></small>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="maintenance" id="maintenance_canceled"
                                                                            value="canceled">
                                                                        <label class="form-check-label"
                                                                            for="maintenance_canceled">Cancelar
                                                                            revisión
                                                                            (Permitir volver a subir)</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="maintenance"
                                                                            id="maintenance_disapproved"
                                                                            value="disapproved">
                                                                        <label class="form-check-label"
                                                                            for="maintenance_disapproved">No aprobar
                                                                            importación</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="maintenance" id="maintenance_approve"
                                                                            value="approved">
                                                                        <label class="form-check-label"
                                                                            for="maintenance_approve">
                                                                            Aprobar importación
                                                                        </label>
                                                                    </div>
                                                                    <textarea class="form-control mt-2 mb-2" id="maintenance_description" rows="3"></textarea>
                                                                    <button type="button" id="updatechkMaintenance"
                                                                        class="btn btn-sm btn-primary">Actualizar</button>
                                                                </div>
                                                                <!-- Finanzas -->
                                                                <div class="col-md p-4">
                                                                    <small
                                                                        class="text-light fw-medium d-block">Finanzas</small>
                                                                    <small class="text-light fw-medium d-block"
                                                                        id="userfinance">Finanzas</small>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="finance" id="finance_disapproved"
                                                                            value="disapproved">
                                                                        <label class="form-check-label"
                                                                            for="finance_disapproved">No aprobar
                                                                            importación</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="finance" id="finance_approve"
                                                                            value="approved">
                                                                        <label class="form-check-label"
                                                                            for="finance_approve">Aprobar
                                                                            importación</label>
                                                                    </div>
                                                                    <textarea class="form-control mt-2 mb-2" id="finance_description" rows="3"></textarea>
                                                                    <button type="button" id="updatechkFinance"
                                                                        class="btn btn-sm btn-primary">Actualizar</button>
                                                                </div>
                                                            </div>
                                                            <hr class="m-0" />

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="accordion-item card mb-3">
                                                    <h2 class="accordion-header">
                                                        <button type="button" class="accordion-button collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#accordionStyle1-2"
                                                            aria-expanded="false">
                                                            Parametros de consumo
                                                        </button>
                                                    </h2>
                                                    <div id="accordionStyle1-2" class="accordion-collapse collapse"
                                                        data-bs-parent="#accordionStyle1">
                                                        <div class="accordion-body">
                                                            <div class="mt-4">
                                                                <div class="row">
                                                                    <div class="col-12 mb-3">
                                                                        <div class="d-flex align-items-end flex-wrap">
                                                                            <div class="me-1 flex-shrink-0">
                                                                                <label for="select-metrics"
                                                                                    class="form-label">Tipo de
                                                                                    medición</label>
                                                                                <select class="form-select"
                                                                                    id="select-metrics">
                                                                                    <option value="ninguno">Ninguno
                                                                                    </option>
                                                                                    <option value="metricas_manuales">
                                                                                        Metricas manuales</option>
                                                                                    <option value="metricas_historicas"
                                                                                        disabled>
                                                                                        Metricas historicas * res</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="me-1 flex-shrink-0">
                                                                                <label for="min"
                                                                                    class="form-label">Min</label>
                                                                                <div class="cuadro-metricas"
                                                                                    id="cuadro-metricas-min"
                                                                                    style="background-color: rgb(253, 238, 65);">
                                                                                </div>
                                                                            </div>
                                                                            <div class="me-1 flex-shrink-0">
                                                                                <label for="max"
                                                                                    class="form-label">Max</label>
                                                                                <div class="cuadro-metricas"
                                                                                    id="cuadro-metricas-max"
                                                                                    style="background-color: rgb(255, 98, 98);">
                                                                                </div>
                                                                            </div>
                                                                            <div class="me-1 flex-shrink-0">
                                                                                <button type="button"
                                                                                    id="btn-resetparameters"
                                                                                    class="btn btn-icon btn-secondary"><i
                                                                                        class='bx bx-reset'></i></button>
                                                                            </div>
                                                                            <div class="me-1 flex-shrink-0">
                                                                                <button type="button"
                                                                                    id="btn-refreshparameters"
                                                                                    class="btn btn-icon btn-primary"><i
                                                                                        class='bx bx-filter-alt'></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-light fw-medium"></small>
                                                                    <div class="col-6">
                                                                        <div class="d-flex align-items-end flex-wrap">
                                                                            <!-- Usar flex en lugar de col-md-1 para los campos y botones -->
                                                                            <div class="me-1 flex-shrink-0 hidden">
                                                                                <label for="select-res"
                                                                                    class="form-label">Residencia</label>
                                                                                <select class="form-select"
                                                                                    id="select-res">
                                                                                </select>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-6 col-12 mb-4">
                                                                                    <label for="KwMin"
                                                                                        class="form-label">KW MIN (Igual o
                                                                                        menor a:)</label>
                                                                                    <input type="number" step="0.01"
                                                                                        min="0"
                                                                                        original="{{ $metrics->kwmin }}"
                                                                                        id="KwMin"
                                                                                        value="{{ $metrics->kwmin }}"
                                                                                        class="form-control">
                                                                                </div>
                                                                                <div class="col-md-6 col-12 mb-4">
                                                                                    <label for="KwMax"
                                                                                        class="form-label">KW Max (Igual o
                                                                                        mayor a:)</label>
                                                                                    <input type="number" step="0.01"
                                                                                        min="0"
                                                                                        original="{{ $metrics->kwmax }}"
                                                                                        id="KwMax"
                                                                                        value="{{ $metrics->kwmax }}"
                                                                                        class="form-control">
                                                                                </div>
                                                                                <div class="col-md-6 col-12 mb-4">
                                                                                    <label for="AguaMin"
                                                                                        class="form-label">Agua Min (Igual
                                                                                        o menor a:)</label>
                                                                                    <input type="number" step="0.01"
                                                                                        min="0"
                                                                                        original="{{ $metrics->aguamin }}"
                                                                                        id="AguaMin"
                                                                                        value="{{ $metrics->aguamin }}"
                                                                                        class="form-control">
                                                                                </div>
                                                                                <div class="col-md-6 col-12 mb-4">
                                                                                    <label for="AguaMax"
                                                                                        class="form-label">Agua Max (Igual
                                                                                        o mayor a:)</label>
                                                                                    <input type="number" step="0.01"
                                                                                        min="0" id="AguaMax"
                                                                                        original="{{ $metrics->aguamax }}"
                                                                                        value="{{ $metrics->aguamax }}"
                                                                                        class="form-control">
                                                                                </div>
                                                                                <div class="col-md-6 col-12 mb-md-0 mb-4">
                                                                                    <label for="GasMin"
                                                                                        class="form-label">Gas Min (Igual o
                                                                                        menor a:)</label>
                                                                                    <input type="number" step="0.01"
                                                                                        min="0" id="GasMin"
                                                                                        original="{{ $metrics->gasmin }}"
                                                                                        value="{{ $metrics->gasmin }}"
                                                                                        class="form-control">
                                                                                </div>
                                                                                <div class="col-md-6 col-12">
                                                                                    <label for="GasMax"
                                                                                        class="form-label">GasMax </label>
                                                                                    <input type="number" step="0.01"
                                                                                        min="0" id="GasMax"
                                                                                        original="{{ $metrics->gasmax }}"
                                                                                        value="{{ $metrics->gasmax }}"
                                                                                        class="form-control">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--/ Accordion-->
                                    </div>
                                </div>
                                <div class="col-12">
                                    <table class="table table-hover table-sm  table-bordered display nowrap"
                                        id="tb-data" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th id="residencia">Residencia</th>
                                                <th id="room">Room</th>
                                                <th id="owner">Owner</th>
                                                <th id="ocupacion">Ocupación</th>
                                                <th id="kw">KW</th>
                                                <th id="agua">Agua</th>
                                                <th id="gas">Gas</th>
                                                <th id="total_kw">TTl Kw</th>
                                                <th id="total_kwfee">TTl Kw Fee </th>
                                                <th id="total_gas">TTL Gas</th>
                                                <th id="total_gasfee">TTL Gas Fee</th>
                                                <th id="total_agua">TTL Agua</th>
                                                <th id="total_sewer">TTL Sewer</th>
                                                <th id="subtotal">Sub total</th>
                                                <th id="tax">Tax</th>
                                                <th id="total">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>#</th>
                                                <th>Residencia</th>
                                                <th>Room</th>
                                                <th>Owner</th>
                                                <th>Ocupación</th>
                                                <th>KW</th>
                                                <th>Agua</th>
                                                <th>Gas</th>
                                                <th>TTl Kw</th>
                                                <th>TTl Kw Fee </th>
                                                <th>TTL Gas</th>
                                                <th>TTL Gas Fee</th>
                                                <th>TTL Agua</th>
                                                <th>TTL Sewer</th>
                                                <th>Sub total</th>
                                                <th>Tax</th>
                                                <th>Total</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal principal -->
        <div id="main-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tabla de Comentarios</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <!-- Tu tabla aquí -->
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal secundario, comentario -->
        <div id="comment-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Comentarios</h5>
                    </div>
                    <div class="modal-body" style="max-height: 500px; overflow-y: auto;">

                    </div>
                    <div class="modal-footer">
                        <textarea class="form-control" id="textarea-newcomment" placeholder="Añade nuevo comentario"></textarea>
                        <button type="button" class="btn btn-primary comment-add">Comentar</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection
@section('script')
    <script>
        var viewImportUrl = '{{ route('viewimport') }}';
        var checkimportUrl = '{{ route('checkimport') }}';
        var logimportsUrl = '{{ route('logimport') }}';
        var readcommentsIntableUrl = '{{ route('readcommentsIntable') }}';
        var savecommentIntableUrl = '{{ route('savecommentIntable') }}';
        var newcommentIntableUrl = '{{ route('newcommentIntable') }}';
        var destroycommentIntableUrl = '{{ route('destroycommentIntable') }}';
    </script>
    <!-- Ui popover-->
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/jsv3/ui-popover.js') }}"></script>
    <!-- Ui block-->
    <script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>
    <!-- AUTOSIZE -->
    <script src="{{ asset('assets/vendor/libs/autosize/autosize.js') }}"></script>
    <!-- Vendors JS Datatable -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <!-- JS PICKER  -->
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <!-- JS SWEET ALERT -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <!-- Toast JS-->
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/logimports.js') }}"></script>
@endsection
