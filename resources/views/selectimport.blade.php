@extends('layout.main')

@section('title')
    Importar excel
@endsection

@section('css')
    <!-- Toast CSS-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
    <!-- Vendors CSS Datatable -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-keytable/keytable.css') }}" />
    <style>
        /***DATATABLE***/

        /*Ver escala de tablas mas pequeñas*/
        #listsheets table td,
        #listsheets table th {
            font-size: 0.6em;
        }

        /*Agregar espacio entre el input busqueda y el select año */
        #tb-imports_filter input {
            margin-right: 0.5rem;
            /* Similar al me-2 en Bootstrap */
        }

        /* Centra verticalmente los elementos */
        .dt-buttons {
            display: flex;
            align-items: center;
            /* Centra verticalmente los elementos */
        }

        /***TAB MODAL***/
        .tab-pane {
            min-width: 100%;
            max-width: 100%;
        }

        .tab-content>.tab-pane {
            min-width: 600px;
            /* Ajusta este valor según tus necesidades */
        }
    </style>
@endsection

@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Subir consumos /</span>
            <span id="start">{{ \Carbon\Carbon::parse($start_date)->isoFormat('DD/MM/YYYY') }}</span> -
            <span id="end">{{ \Carbon\Carbon::parse($end_date)->isoFormat('DD/MM/YYYY') }}</span>
            <input id="period" value="{{$period->first()->id}}" hidden>
        </h4>
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-sm-6 col-lg-4">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-1">
                                        {{ ucfirst(\Carbon\Carbon::parse($start_date)->locale('es_ES')->isoFormat('MMMM YYYY')) }}
                                    </h3>
                                    <a href="{{ route('download_utilitiestmp1') }}" class="mb-0">Descargar plantilla</a>
                                </div>
                                <div class="avatar me-sm-4">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="bx bx-calendar-event bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none me-4">
                        </div>
                        <div class="col-sm-6 col-lg-8">
                            <h5 class="card-header">Plantilla Utilities (.xlsx, .xls)</h5>
                            <div class="card-body demo-vertical-spacing demo-only-element">
                                <form id="ReadSheetForm" method="POST" action="{{ route('readsheets') }}"
                                    enctype="multipart/form-data">
                                    @method('POST')
                                    @csrf
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="documento" id="documento"
                                            accept=".xlsx, .xls" aria-describedby="inputGroupFileAddon04"
                                            aria-label="Upload" placeholder="Sin archivos">
                                        <button class="btn btn-outline-primary" value="Importar" type="button"
                                            id="btnread"><span class="icon">Leer</span></button>
                                    </div>
                                    <small class="text-muted">*Considera cerrar primero el archivo que se quiere leer para
                                        evitar subir datos sin guardar..</small>
                                </form>
                            </div>

                            <hr class="d-none d-sm-block d-lg-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade modal-static" id="listsheets" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Hojas encontradas en el archivo</h5>
                        <button type="button" id="close-importmodal" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-12">
                                <h6 class="text-muted"></h6>
                                <div class="shadow-none text-center mb-3">
                                    <div class="card-header border-bottom">
                                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                            <li class="nav-item">
                                                <button type="button" id="tab-sheets" class="nav-link active" disabled>
                                                    Hojas encontradas
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" id="tab-template" class="nav-link" disabled>
                                                    Template
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" id="tab-SheetValidate" class="nav-link" disabled>
                                                    Validación de datos
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" id="tab-importdata" class="nav-link" disabled>
                                                    Importar datos
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="sheets" role="tabpanel">
                                            <h4 class="card-title">Hojas encontradas</h4>
                                            <p class="card-text sr-only">Hay más de una sola hoja en el archivo. Seleccione
                                                una para el siguiente paso</p>
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <hr class="m-0">
                                                    <!-- Inline Checkboxes -->
                                                    <div class="row">
                                                        <div class="col-md-12 p-4"
                                                            style="max-height: 300px; overflow-y: auto;">
                                                            <small class="text-light fw-medium d-block"
                                                                id="title-listsheets"></small>
                                                            <!-- Aquí lista -->
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-12 d-flex justify-content-end">
                                                            <button id="btn1-next" type="button" class="btn btn-primary"
                                                                disabled>
                                                                <span
                                                                    class="d-sm-inline-block d-none me-sm-1">Siguiente</span>
                                                                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="template" role="tabpanel">
                                            <h4 class="card-title">Definir plantilla</h4>
                                            <p class="card-text">Selecciona que tipo de plantilla es sobre la cual
                                                se haran las validaciónes.</p>
                                            <div class="row">
                                                <div class="col-md mb-md-0 mb-2">
                                                    <div class="form-check custom-option custom-option-basic checked">
                                                        <label class="form-check-label custom-option-content"
                                                            for="checktemplateconsumption">
                                                            <input name="checktemplate" class="form-check-input"
                                                                type="radio" value="1" id="checktemplateconsumption"
                                                                checked="">
                                                            <span class="custom-option-header">
                                                                <span class="h6 mb-0">Plantilla de Consumos</span>
                                                                <span>1</span>
                                                            </span>
                                                            <span class="custom-option-body">
                                                                <small style="text-align: left;">Se validaran unicamente las 6 primeras columnas de la
                                                                    plantilla utilities.</small>
                                                                <br>
                                                                <code>Los totales de este periodo seran calculados en base
                                                                    a los parametros registrados.</code>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md">
                                                    <div class="form-check custom-option custom-option-basic">
                                                        <label class="form-check-label custom-option-content"
                                                            for="checktemplatefull">
                                                            <input name="checktemplate" class="form-check-input"
                                                                type="radio" value="2" id="checktemplatefull">
                                                            <span class="custom-option-header">
                                                                <span class="h6 mb-0">Plantilla de Costos Totales</span>
                                                                <span>2</span>
                                                            </span>
                                                            <span class="custom-option-body">
                                                                <small style="text-align: left;">Se validara completamente todas las columnas de la
                                                                    plantilla.</small>
                                                                <br>
                                                                <code>Los totales de este periodo pueden ser tomados
                                                                    directamente del archivo.</code>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-12 d-flex justify-content-end">
                                                    <button id="btn2-back" type="button" class="btn btn-secondary me-2">
                                                        <span class="d-sm-inline-block d-none me-sm-1">Anterior</span>
                                                        <i class="bx bx-chevron-left bx-sm me-sm-n2"></i>
                                                    </button>
                                                    <button id="btn2-next" type="button" class="btn btn-primary">
                                                        <span class="d-sm-inline-block d-none me-sm-1">Siguiente</span>
                                                        <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="SheetValidate" role="tabpanel">
                                            <h4 class="card-title">Validación de datos</h4>
                                            <p class="card-text">Espere mientras se comprueba la estructura de la plantilla
                                            </p>
                                            <ul class="list-unstyled text-start" id="listvalidations">
                                                <li class="d-flex mb-3" id="validation1">
                                                    <span class="icon me-2"><i class="bx bx-circle bx-sm"></i></span>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="title mb-0 lh-1">Validación de existencia de hoja
                                                            </h6>
                                                            <small class="text-muted">Verifica si la hoja con el nombre
                                                                especificado existe en el archivo de
                                                                Excel.</small>
                                                            <br>
                                                            <code class="error"></code>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="d-flex mb-3" id="validation2">
                                                    <span class="icon me-2"><i class="bx bx-circle bx-sm"></i></span>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="title mb-0 lh-1">Validación de encabezados</h6>
                                                            <small class="text-muted">Asegura que las columnas requeridas
                                                                estén presentes en la primera fila del
                                                                archivo de Excel.</small>
                                                            <br>
                                                            <code class="error"></code>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="d-flex mb-3" id="validation3">
                                                    <span class="icon me-2"><i class="bx bx-circle bx-sm"></i></span>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="title mb-0 lh-1">Validación de orden de columnas
                                                            </h6>
                                                            <small class="text-muted">Verifica que las columnas en el
                                                                archivo de Excel estén en el orden
                                                                correcto.</small>
                                                            <br>
                                                            <code class="error"></code>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="d-flex mb-3" id="validation4">
                                                    <span class="icon me-2"><i class="bx bx-circle bx-sm"></i></span>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="title mb-0 lh-1">Validación de conteo de filas</h6>
                                                            <small class="text-muted">Comprueba que el número total de
                                                                filas en el archivo de Excel coincida con
                                                                el número esperado de residencias.</small>
                                                            <br>
                                                            <code class="error"></code>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="d-flex mb-3" id="validation5">
                                                    <span class="icon me-2"><i class="bx bx-circle bx-sm"></i></span>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="title mb-0 lh-1">Validación de residencias</h6>
                                                            <small class="text-muted">Compara las residencias importadas
                                                                del archivo de Excel con las
                                                                residencias existentes en la base de datos para asegurar que
                                                                no falten ni sobren
                                                                residencias.</small>
                                                            <br>
                                                            <code class="error"></code>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="d-flex mb-3" id="validation6">
                                                    <span class="icon me-2"><i class="bx bx-circle bx-sm"></i></span>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="title mb-0 lh-1">Validación del llenado de datos
                                                            </h6>
                                                            <small class="text-muted">Se revisarán todas y cada una de las
                                                                celdas para comprobar que esten en el tipo de datos
                                                                correcto.</small>
                                                            <br>
                                                            <code class="error"></code>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="row mt-3">
                                                <div class="col-md-12 d-flex justify-content-end">
                                                    <button id="btn3-back" type="button" class="btn btn-secondary me-2"
                                                        disabled>
                                                        <span class="d-sm-inline-block d-none me-sm-1">Anterior</span>
                                                        <i class="bx bx-chevron-left bx-sm me-sm-n2"></i>
                                                    </button>
                                                    <button id="btn3-next" type="button" class="btn btn-primary"
                                                        disabled>
                                                        <span class="d-sm-inline-block d-none me-sm-1">Siguiente</span>
                                                        <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="importdata" role="tabpanel">
                                            <h4 class="card-title">Importar datos</h4>
                                            <p class="card-text">Compruebe que los datos de la tabla sean los mismos al que
                                                desea importar segun la hoja excel seleccionada</p>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="table-responsive">
                                                        <table
                                                            class="table table-hover table-sm  table-bordered display nowrap"
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
                                            <div class="row mt-3">
                                                <div class="col-md-12 d-flex justify-content-end">
                                                    <button id="btn4-back" type="button" class="btn btn-secondary me-2"
                                                        disabled>
                                                        <span class="d-sm-inline-block d-none me-sm-1">Anterior</span>
                                                        <i class="bx bx-chevron-left bx-sm me-sm-n2"></i>
                                                    </button>
                                                    <button id="btn-import" type="button" class="btn btn-primary"
                                                        disabled>
                                                        <span class="d-sm-inline-block d-none me-sm-1">Importar</span>
                                                        <i class="bx bx-upload bx-sm me-sm-n2"></i>
                                                    </button>
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
    </div>
@endsection


@section('script')
    <script type="text/javascript">
        var readsheets = "{{ route('readsheets') }}";
        var processSheet = "{{ route('process_sheet') }}";
        var importExcelRoute = "{{ route('importexcel') }}";
    </script>
    <!-- Vendors JS Datatable -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-keytable/keytable.min.js') }}"></script>
    <!-- Toast JS-->
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/import.js') }}"></script>
@endsection
