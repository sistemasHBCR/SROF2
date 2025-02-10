@extends('layout.main')

@section('title')
    Parametros Utilities
@endsection

@section('css')
    <!-- Vendors CSS PICKER-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <!-- Vendors CSS Datatable -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <!-- Toast CSS-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
    <!-- Complemento refresh cards CSS-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/spinkit/spinkit.css') }}" />
    <!-- Vendors CSS SWEET ALERT-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <!-- Vendors CSS Select2-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <style>
        .tariff-message {
            font-weight: bold;
        }

        .residences-list {
            list-style-type: none;
            padding-left: 20px;
        }

        .residences-list li {
            margin: 5px 0;
        }
    </style>
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="py-3 breadcrumb-wrapper mb-4">
                <span class="text-muted fw-light"></span> Parametros Utilities
            </h4>
            <!-- Botón alineado a la derecha -->
            @canany(['parameters.transfer'])
                <button class="btn btn-white" id="duplicate" style="display: none;"><i class='bx bxs-duplicate me-1'></i>
                    Duplicar</button>
            @endcanany
        </div>

        <div class="row g-4">
            <!-- Navigation -->
            <div class="col-12 col-lg-2">
                <div class="d-flex justify-content-between flex-column mb-1 mb-md-0">
                    <form class="d-flex mb-2" method="GET" action="{{ route('parameters.index') }}">
                        <div class="input-group date me-1" id="yearPicker" data-target-input="nearest" style="width:83px">
                            <input id="yearInput" name="year" type="text"
                                class="form-control form-control-sm datetimepicker-input" data-target="#yearPicker"
                                value="{{ $dateNow->format('Y') }}" readonly>
                            <div class="input-group-append" data-target="#yearPicker" data-toggle="datepicker">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-filter" style="margin-right: 5px;"></i> <span
                                class="d-none d-md-inline">Filtrar</span>
                        </button>
                    </form>
                    <ul class="nav nav-align-left nav-pills flex-column me-1">


                        @foreach (collect($months)->skip(1) as $i => $month)
                            @php

                                $period = $periods[$i] ?? null;

                                $idperiod = !empty($period) ? $period['id'] : '0';

                                $idstatus = !empty($period) ? $period['status']['id'] : $status1->id;

                                $status = !empty($period) ? $period['status']['class'] : $status1->class;
                                $nmstatus = !empty($period) ? $period['status']['name'] : $status1->name;

                                $icon =
                                    $idstatus == '1'
                                        ? 'bx bx-window-close'
                                        : ($idstatus == '2'
                                            ? 'bx bx-window-open'
                                            : 'bx bxs-calendar-check');

                                $parametermessage = isset($period['parametersutilities'])
                                    ? ($period['parametersutilities'] == 0
                                        ? 'Parametros pendientes'
                                        : 'Parametros registrados')
                                    : '';

                            @endphp
                            <li class="nav-item mb-2">
                                <div data-period="{{ $idperiod }}" data-status="{{ $idstatus }}"
                                    data-month="{{ $month['title'] }}"
                                    data-year="{{ date('Y', strtotime($months[0]['start_date'])) }}"
                                    class="@if ($idstatus != 1) cardsperiods cursor-pointer card-reload @endif card card-border-shadow-{{ $status }} h-100 user-select-none"
                                    style="height: 150px;">
                                    <div class="card-body p-2">
                                        <div class="d-flex align-items-center mb-1 pb-1">
                                            <div class="avatar me-2">
                                                <span class="avatar-initial rounded bg-label-{{ $status }}"><i
                                                        class="{{ $icon }}"></i></span>
                                            </div>
                                            <div class="mb-sm-0 mb-4">
                                                <h6 class="mb-1">{{ $month['title'] }}</h6>
                                                <small class="mb-1">{{ $nmstatus }}</small>
                                            </div>
                                        </div>
                                        <small class="parameterstatus">{!! $parametermessage !!}</small>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <!-- /Navigation -->

            <!-- Options -->
            <div class="col-12 col-lg-10 pt-4 pt-lg-0">
                <div class="content-parameters  tab-content p-0">
                    <div class="card-alert"></div>
                    <!-- Store Details Tab -->
                    <div class="tab-pane fade show active" id="parameters" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="content-withoutdata  col-lg-12 h-100">
                                                <div class="alert d-flex align-items-center bg-label-info mb-0"
                                                    role="alert">
                                                    <i class="bx bx-info-circle bx-xs me-2"></i>Selecciona un periodo
                                                    disponible para visualizar sus metricas de consumo en el año.
                                                </div>
                                            </div>
                                            <div class="content-dataperiod col-lg-12 mx-auto sr-only">
                                                <!-- 1. General -->
                                                @canany(['parameters.tc'])
                                                    <h5 class="mb-4">1. Parametros generales</h5>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label><small class="text-light fw-medium">Tipo de cambio /
                                                                    <code>codigo: tc</code></small></label>
                                                            <div class="input-group input-group-sm mb-2">
                                                                <span class="input-group-text"><i
                                                                        class='bx bx-money-withdraw'></i></span>
                                                                <input type="text" id="tc" name="tc"
                                                                    class="form-control mask-money"
                                                                    placeholder="Ingrese el tipo de cambio" required disabled>
                                                                @canany(['parameters.tc.edit'])
                                                                    <button class="btn btn-outline-secondary edit-tc-btn"
                                                                        type="button"><i class='bx bx-edit'></i></button>
                                                                @endcanany
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label><small class="text-light fw-medium">TAX (%) /
                                                                    <code>codigo: tax</code></small></label>
                                                            <div class="input-group input-group-sm mb-2">
                                                                <span class="input-group-text"><i
                                                                        class='bx bxs-discount'></i></span>
                                                                <input type="text" id="tax" name="tax"
                                                                    class="form-control mask-money" value=""
                                                                    placeholder="Ingrese el TAX: valor sera tomado como porcentaje "
                                                                    required disabled>
                                                                @canany(['parameters.tc.edit'])
                                                                    <button class="btn btn-outline-secondary edit-tax-btn"
                                                                        type="button"><i class='bx bx-edit'></i></button>
                                                                @endcanany
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                @endcanany
                                                <!-- 2. Utilities -->
                                                @canany(['parameters.costs'])
                                                    <h5 class="my-4">2. Alta de tarifas por consumo</h5>
                                                    <div id="CardsServices">
                                                    </div>
                                                    <hr>
                                                @endcanany

                                                @canany(['parameters.residences'])
                                                    <!-- 3. Administrar reglas de calculo -->
                                                    <h5 class="my-4">3. Tarifas por residencia</h5>
                                                    <div class="alert d-flex align-items-center bg-label-secondary mb-2"
                                                        role="alert">
                                                        <ul id="residencesTariffInfo">

                                                        </ul>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="table-responsive">
                                                                <div class="card-datatable table-responsive pt-0">
                                                                    <table id="tb-residence" class="table table-sm ">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Residencia</th>
                                                                                <th>Servicio</th>
                                                                                <th>Estado</th>
                                                                                <th>Tarifa</th>
                                                                                <th>Si consumo es menor a</th>
                                                                                <th></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endcanany
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Options-->
        </div>
    </div>
    <!-- Modal residences-->
    <div class="modal fade" id="dataResidenceModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formresidences" onsubmit="return false">
                <div class="modal-header">
                    <input type="hidden" id="residenceid" />
                    <h5 class="modal-title" id="dataResidenceTitle">Parametros para residencia</h5>
                    <button type="button" id="btncloseMdlRes" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12 mb-3">
                            <h6 class="fw-normal">1. Tipo de tarifa</h6>
                            <hr class="mt-0">
                            <select class="form-select" id="type_rate" @cannot('parameters.residences.edit') disabled @endcannot>
                                @foreach ($rates as $idx => $rate)
                                    <option value="{{ $rate->id }}">{{ $rate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <h6 class="fw-normal">2. Tarifa del servicio en la residencia</h6>
                            <hr class="mt-0">
                            <div class="table-responsive text-nowrap">
                                <table id="listcosts" class="table table-sm border-top">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Código</th>
                                            <th>Volumen</th>
                                            <th>Fórmula</th>
                                            <th>Costo</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                    </tbody>
                                </table>
                            </div>
                            <div class="input-group contentfixedrate_value">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control input_value mask-money"
                                    placeholder="Ingrese costo" id="fixedrate_value" @cannot('parameters.residences.edit') disabled @endcannot>
                                <span class="input-group-text">USD</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <h6 class="fw-normal txt_condition_value">3. Tarifa condicional</h6>
                        <hr class="mt-0 txt_condition_value">
                        <div class="col-6 mb-2">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="enable_condition"
                                    checked @cannot('parameters.residences.edit') disabled @endcannot>
                                <label for="condition_consumption" class="form-label txt_condition_consumption">SI CONSUMO
                                    ES IGUAL Y MENOR A</label>
                            </div>
                            <input type="text" id="condition_consumption" value=""
                                class="form-control mask-money" placeholder="" @cannot('parameters.residences.edit') disabled @endcannot/>
                        </div>
                        <div class="col-6 mt-4">
                            <label for="condition_value" class="form-label txt_condition_value">TARIFA FIJA DE ($)
                                USD</label>
                            <input type="text" id="condition_value" class="form-control mask-money" value="" @cannot('parameters.residences.edit') disabled @endcannot/>
                        </div>
                    </div>
                    <div class="row g-2 content-listresidences">
                        <h6 class="fw-normal">4. Aplicar tarifa en:</h6>
                        <hr class="mt-0">
                        <div class="col-12 mb-1">
                            <label for="select_listresidences" class="form-label">LISTA RESIDENCIAS</label>
                            <select id="select_listresidences" class="selectpicker w-100" data-style="btn-default"
                                multiple data-actions-box="true" data-live-search="true">
                                @foreach ($residences as $residence)
                                    <option id="{{ $residence->id }}" data-service="{{ $residence->servicename }}">
                                        {{ $residence->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    @canany(['parameters.residences.edit'])
                    <button type="submit" id="btnsavegeneralres" class="update btn btn-primary">Guardar</button>
                    <button type="submit" id="btnsaveinresidence" class="update btn btn-primary">Guardar</button>
                    @endcanany
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para añadir Service Card -->
    <div class="modal fade" id="addCostServiceModal" tabindex="-1" aria-labelledby="addCostServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel"></h5>
                </div>
                <div class="modal-body">
                    <form id="addServiceForm">
                        <div class="mb-3">
                            <label for="serviceVolume" class="form-label">Medida/Volumen</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="newvolum"><i class="bx bxs-component"></i></span>
                                <input type="text" class="form-control new-calculate-volum mask-money" step="0.01"
                                    min="1" placeholder="Parametro del servicio" aria-label="Volumen"
                                    aria-describedby="newvolum" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="serviceCost" class="form-label">Costo</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="newcost"><i class="bx bx-dollar-circle"></i></span>
                                <input type="text" class="form-control new-calculate-cost"
                                    placeholder="Calculo de costo" aria-label="Costo" aria-describedby="newcost">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-center">
                                <h5 class="result-cost card-title mb-0 me-2">$0.00</h5>
                                <small class="text-muted">Precio x consumo</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-sm" id="saveServiceBtn">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="duplicateModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="duplicateModalTitle">Duplicación de Parámetros</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <small class="mb-3">A continuación, se enlistan los parámetros que se permiten duplicar, considerando
                        parámetros previamente registrados en periodos cerrados y utilizando una plantilla basada en el
                        consumo.</small>
                    <br><br>
                    <div class="row mb-3">
                        <div class="input-group">
                            <select id="parameters_listmonths" class="form-control">
                                <option value="">Seleccionar</option>
                                @foreach ($monthsWithParametersGrouped as $year => $months)
                                    <optgroup label="{{ $year }}">
                                        @foreach ($months as $period)
                                            <option value="{{ $period['id'] }}" data-year="{{ $year }}">
                                                {{ ucfirst(\Carbon\Carbon::parse($period['start'])->translatedFormat('F')) }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md mb-md-0 mb-2">
                            <div class="form-check custom-option custom-option-icon position-relative">
                                <label class="form-check-label custom-option-content" for="customRadioDelivery1">
                                    <span class="custom-option-body">
                                        <span class="custom-option-title" id="month_origenperiod"></span>
                                        <small id="year_origenoeriod"></small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md mb-md-0 mb-2">
                            <div class="form-check custom-option custom-option-icon position-relative">
                                <label class="form-check-label custom-option-content" for="customRadioDelivery1">
                                    <span class="custom-option-body">
                                        <span class="custom-option-title">></span>
                                        <small>Transferir</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md mb-md-0 mb-2">
                            <div class="form-check custom-option custom-option-icon position-relative">
                                <label class="form-check-label custom-option-content" for="customRadioDelivery2">
                                    <span class="custom-option-body">
                                        <span class="custom-option-title" id="month_transperiod"></span>
                                        <small id="year_transperiod"></small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label class="switch">
                                <input type="checkbox" class="switch-input" id="checkduplicateParameters">
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">
                                        <i class="bx bx-check"></i>
                                    </span>
                                    <span class="switch-off">
                                        <i class="bx bx-x"></i>
                                    </span>
                                </span>
                                <span class="switch-label">Duplicar parámetros en el mes seleccionado y pasar la
                                    información al periodo del mes actual</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-outline-primary" value="Importar" type="button" id="saveduplicatepar"
                        disabled><span class="icon">Aplicar</span></button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        var spartc_utilities = "{{ route('spartc_utilities') }}";
        var sparcost_utilities = "{{ route('sparcost_utilities') }}";
        var nparcost_utilities = "{{ route('new_parameters_costs') }}";
        var find_cost_residences = "{{ route('find_cost_residences') }}";
        var find_costbycode = "{{ route('find_costbycode') }}";
        var dparcost_utilities = "{{ route('delete_parameters_costs') }}";
        var sparres_utilities = "{{ route('sparres_utilities') }}";
        var sparsomeres_utilities = "{{ route('sparsomeres_utilities') }}";
        var load_costs_inperiod = "{{ route('load_costs_inperiod') }}";
        var loadcosts = "{{ route('loadcosts') }}";
        var load_costs_inservice = "{{ route('load_costs_inservice') }}";
        var load_databyresidence = "{{ route('load_databyresidence') }}";
        var load_resultcosts = "{{ route('load_resultcosts') }}";
        var duplicateparameters = "{{ route('duplicateparameters') }}";
        var hasPermission_costs = @json(auth()->user()->can('parameters.costs'));
        var hasPermission_consumptionpriceedit = @json(auth()->user()->can('parameters.consumptionprice.edit'));
        var hasPermission_consumptionvolumeedit = @json(auth()->user()->can('parameters.consumptionvolume.edit'));
    </script>

    <!-- JS Select2 Multiple -->
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <!-- JS PICKER  -->
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <!-- Vendors JS Datatable -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <!-- Toast JS-->
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
    <!-- JS SWEET ALERT -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <!-- JS mask -->
    <script src=https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.6/jquery.inputmask.bundle.min.js></script>
    <script src=https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js></script>
    <!--Complemento Refresh card JS -->
    <script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/parameters.js') }}"></script>
@endsection
