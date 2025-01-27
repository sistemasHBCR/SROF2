@extends('layout.main')

@section('title')
    Periodo Mensual
@endsection
<!-- Vendors CSS Datatable -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
<!--Css datatable component-->
<link rel="stylesheet" href="{{ asset('assets/css/_tables.scss') }}" />
<!-- Vendors CSS MODAL TAB-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
<!-- Vendors CSS TOAST-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />
<!-- Vendors CSS SWIPER-->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/ui-carousel.css') }}" />
<style>
    /* Asignar el icono como cursor */
    #tb-data td {
        position: relative;
        cursor: pointer;
    }

    /* Crear el cursor de lápiz usando Font Awesome */
    .editable:hover::after {
        content: "\f303";
        /* Código Unicode de Font Awesome para el lápiz */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: -10px;
        right: 5%;
        font-size: 16px;
        pointer-events: none;
    }

    .non-editable:hover::after {
        content: "\f023";
        /* Código Unicode de Font Awesome para el candado */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: -10px;
        right: 5%;
        font-size: 16px;
        pointer-events: none;
    }
</style>
@section('css')
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light">Periodo Mensual / </span> {{ $start_date->format('d/m/Y') }} -
            {{ $end_date->format('d/m/Y') }}
        </h4>
        @if ($errors->has('error'))
            <div class="alert alert-solid-secondary alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-xs bx-detail me-2"></i>
                {{ $errors->first('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="row mt-4">
            <!-- Navigation -->
            <div class="col-lg-12 col-md-12 col-12 mb-md-0 mb-3">
                <div class="d-flex justify-content-between mb-2 mb-md-0">
                    <ul class="nav nav-align-left nav-pills flex-row ms-4 mb-2"> <!-- Aquí añadimos ms-3 -->
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#payment">
                                <i class="fa-solid fa-address-book faq-nav-icon me-1"></i>
                                <span class="align-middle">Captura de operador</span>
                            </button>
                        </li>
                        @if ($utilities !== null && count($utilities) > 0)
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#delivery">
                                    <i class="fa-solid fa-database faq-nav-icon me-1"></i>
                                    <span class="align-middle">Datos asignados</span>
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            <!-- /Navigation -->

            <!-- Wizard -->
            <div class="col-lg-12 col-md-12 col-12">
                <div class="tab-content py-0">
                    <div class="tab-pane fade show active" id="payment" role="tabpanel">
                        <!-- Lista residencias   -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                            </div> <!--elementos a la izquierda -->
                                            <div class="col-md-6 mb-2"> <!--elementos a la derecha -->
                                                @if ($period->first()->status_id == 2)
                                                    <form action="{{ route('selectimport') }}" method="GET">
                                                        <input type="hidden" name="start_date"
                                                            value="{{ $start_date }}">
                                                        <input type="hidden" name="end_date" value="{{ $end_date }}">
                                                        <button type="submit" class="btn btn-outline-secondary float-end">
                                                            <span class="fa-solid fa-file-import me-1"></span>Importar
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($period->first()->status_id == 3)
                                                    <form action="{{ route('bills.utilities') }}" method="GET">
                                                        <input type="hidden" name="start_date"
                                                            value="{{ $start_date }}">
                                                        <input type="hidden" name="end_date" value="{{ $end_date }}">
                                                        <button type="submit" class="btn btn-outline-info float-end">
                                                            <span class="fa-solid fa-file-invoice me-1"></span>Recibos
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <hr>
                                            <h5 class="card-header text-center">Captura del operador</h5>
                                            <div class="col-12">
                                                <a class="bx bx-check text-success bx-sm me-2"></a>Capturado&nbsp;&nbsp|
                                                <a class="bx bx-x text-danger bx-sm me-2"></a>Sin capturar
                                            </div>
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table id="status" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Residencia</th>
                                                                <th>Luz</th>
                                                                <th>Agua</th>
                                                                <th>Gas</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($residences as $residence)
                                                                <tr row="{{ $residence->id }}">
                                                                    <td>{{ $residence->name }}</td>
                                                                    <td><a class="bx bx-check text-success bx-sm me-2"></a>
                                                                    </td>
                                                                    <td><a class="bx bx-check text-success bx-sm me-2"></a>
                                                                    </td>
                                                                    <td><a class="bx bx-x text-danger bx-sm me-2"></a></td>
                                                                    <td class="text-center"><button type="button"
                                                                            class="btn btn-primary btn-sm"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#Modal-Utilities"
                                                                            id="{{ $residence->id }}">Ver</button>
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

                        <!--/ Lista residencias -->

                        <!-- Modal -->
                        <!-- Create Utilities Modal -->
                        <div class="modal fade" id="Modal-Utilities" tabindex="-1" aria-hidden="true"
                            data-bs-backdrop="static">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-simple modal-upgrade-plan"
                                style="max-width: 1000px;">
                                <div class="modal-content p-3 p-md-5">
                                    <div class="modal-body p-2">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                        <div class="text-center">
                                            <h3 class="mb-2">Residencia 1-014</h3>
                                        </div>
                                        <!-- Wizard Captura-->

                                        <!-- BOTON LUZ-->
                                        <div id="wizard-create-app" class="bs-stepper vertical mt-2 shadow-none">
                                            <div class="bs-stepper-header border-0 p-1">
                                                <div class="step" data-target="#luz">
                                                    <button type="button" class="step-trigger">
                                                        <span class="bs-stepper-circle"><i
                                                                class="fa-solid fa-lightbulb"></i></span>
                                                        <span class="bs-stepper-label">
                                                            <span class="bs-stepper-title text-uppercase">LUZ</span>
                                                            <span class="bs-stepper-subtitle">KW</span>
                                                        </span>
                                                    </button>
                                                </div>
                                                <!-- BOTON AGUA-->
                                                <div class="line"></div>
                                                <div class="step" data-target="#agua">
                                                    <button type="button" class="step-trigger">
                                                        <span class="bs-stepper-circle"><i
                                                                class="fa-solid fa-droplet"></i></span>
                                                        <span class="bs-stepper-label">
                                                            <span class="bs-stepper-title text-uppercase">Agua</span>
                                                            <span class="bs-stepper-subtitle">M3</span>
                                                        </span>
                                                    </button>
                                                </div>
                                                <!-- BOTON GAS-->
                                                <div class="line"></div>
                                                <div class="step" data-target="#gas">
                                                    <button type="button" class="step-trigger">
                                                        <span class="bs-stepper-circle"><i
                                                                class="fa-solid fa-fire-flame-simple"></i></span>
                                                        <span class="bs-stepper-label">
                                                            <span class="bs-stepper-title text-uppercase">Gas</span>
                                                            <span class="bs-stepper-subtitle">M3</span>
                                                        </span>
                                                    </button>
                                                </div>

                                            </div>
                                            <div class="bs-stepper-content p-1">
                                                <form onSubmit="return false">
                                                    <!-- Luz -->
                                                    <div id="luz" class="content pt-3 pt-lg-0">
                                                        <div class="mb-3">
                                                            <input type="text" class="form-control form-control-lg"
                                                                id="captureluz" placeholder="Consumo" />
                                                        </div>
                                                        <h5 class="text-center">CAPTURA</h5>
                                                        <div class="card-group mb-5">
                                                            <div class="col-12 col-xl-12 col-md-12">
                                                                <div class="card h-100">
                                                                    <div class="card-body">
                                                                        <!-- Bootstrap crossfade carousel -->
                                                                        <div class="col-md">
                                                                            <h5 class="my-4">Bootstrap crossfade carousel
                                                                                (dark)</h5>

                                                                            <div id="carouselExampleDark"
                                                                                class="carousel carousel-dark slide carousel-fade"
                                                                                data-bs-ride="carousel">
                                                                                <div class="carousel-indicators">
                                                                                    <button type="button"
                                                                                        data-bs-target="#carouselExampleDark"
                                                                                        data-bs-slide-to="0"
                                                                                        class="active" aria-current="true"
                                                                                        aria-label="Slide 1"></button>
                                                                                    <button type="button"
                                                                                        data-bs-target="#carouselExampleDark"
                                                                                        data-bs-slide-to="1"
                                                                                        aria-label="Slide 2"></button>
                                                                                    <button type="button"
                                                                                        data-bs-target="#carouselExampleDark"
                                                                                        data-bs-slide-to="2"
                                                                                        aria-label="Slide 3"></button>
                                                                                </div>
                                                                                <div class="carousel-inner">
                                                                                    <div class="carousel-item active">
                                                                                        <img class="d-block w-100"
                                                                                            src="{{ asset('assets/img/elements/18.jpg') }}"
                                                                                            alt="First slide" />
                                                                                        <div
                                                                                            class="carousel-caption d-none d-md-block">
                                                                                            <h3>First slide</h3>
                                                                                            <p>Eos mutat malis maluisset et,
                                                                                                agam ancillae
                                                                                                quo te, in vim congue
                                                                                                pertinacia.</p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="carousel-item">
                                                                                        <img class="d-block w-100"
                                                                                            src="{{ asset('assets/img/elements/13.jpg') }}"
                                                                                            alt="Second slide" />
                                                                                        <div
                                                                                            class="carousel-caption d-none d-md-block">
                                                                                            <h3>Second slide</h3>
                                                                                            <p>In numquam omittam sea.</p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="carousel-item">
                                                                                        <img class="d-block w-100"
                                                                                            src="{{ asset('assets/img/elements/2.jpg') }}"
                                                                                            alt="Third slide" />
                                                                                        <div
                                                                                            class="carousel-caption d-none d-md-block">
                                                                                            <h3>Third slide</h3>
                                                                                            <p>Lorem ipsum dolor sit amet,
                                                                                                virtute consequat
                                                                                                ea qui, minim graeco mel no.
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <a class="carousel-control-prev"
                                                                                    href="#carouselExampleDark"
                                                                                    role="button" data-bs-slide="prev">
                                                                                    <span
                                                                                        class="carousel-control-prev-icon"
                                                                                        aria-hidden="true"></span>
                                                                                    <span
                                                                                        class="visually-hidden">Previous</span>
                                                                                </a>
                                                                                <a class="carousel-control-next"
                                                                                    href="#carouselExampleDark"
                                                                                    role="button" data-bs-slide="next">
                                                                                    <span
                                                                                        class="carousel-control-next-icon"
                                                                                        aria-hidden="true"></span>
                                                                                    <span
                                                                                        class="visually-hidden">Next</span>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <br>
                                                                        <div class="row mb-3 g-3">
                                                                            <div class="col-6">
                                                                                <div class="d-flex">
                                                                                    <div class="avatar flex-shrink-0 me-2">
                                                                                        <span
                                                                                            class="avatar-initial rounded bg-label-primary"><i
                                                                                                class="bx bx-time-five bx-sm"></i></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="mb-0 text-nowrap">17 Nov
                                                                                            23 12:30 pm
                                                                                        </h6>
                                                                                        <small>Tomado el</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="d-flex">
                                                                                    <div class="avatar flex-shrink-0 me-2">
                                                                                        <span
                                                                                            class="avatar-initial rounded bg-label-primary"><i
                                                                                                class="bx bx-camera bx-sm"></i></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="mb-0 text-nowrap">Ulises
                                                                                            sosa</h6>
                                                                                        <small>Tomado por</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <h4 class="mb-2 pb-1">Comentarios</h4>
                                                                        <p class="small">
                                                                            Ninguno.
                                                                        </p>
                                                                        <a href="javascript:void(0);"
                                                                            class="btn btn-primary w-100">Comparar captura
                                                                            anterior</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-between mt-4">
                                                            <button class="btn btn-label-secondary btn-prev" disabled>
                                                                <i class="bx bx-left-arrow-alt bx-xs me-sm-1 me-0"></i>
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none">Previous</span>
                                                            </button>
                                                            <button class="btn btn-primary btn-next">
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span>
                                                                <i class="bx bx-right-arrow-alt bx-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Agua -->
                                                    <div id="agua" class="content pt-3 pt-lg-0">
                                                        <div class="mb-3">
                                                            <input type="text" class="form-control form-control-lg"
                                                                id="captureagua" placeholder="Consumo" />
                                                        </div>
                                                        <h5 class="text-center">CAPTURA</h5>
                                                        <div class="card-group mb-5">
                                                            <div class="col-12 col-xl-12 col-md-12">
                                                                <div class="card h-100">
                                                                    <div class="card-body">
                                                                        <div
                                                                            class="bg-label-secondary rounded-3 text-center mb-3 ">
                                                                            <img class="img-fluid w-60"
                                                                                src="{{ asset('assets/img/elements/1.jpg') }}">
                                                                        </div>
                                                                        <div class="row mb-3 g-3">
                                                                            <div class="col-6">
                                                                                <div class="d-flex">
                                                                                    <div class="avatar flex-shrink-0 me-2">
                                                                                        <span
                                                                                            class="avatar-initial rounded bg-label-primary"><i
                                                                                                class="bx bx-time-five bx-sm"></i></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="mb-0 text-nowrap">17 Nov
                                                                                            23 12:30 pm
                                                                                        </h6>
                                                                                        <small>Tomado el</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="d-flex">
                                                                                    <div class="avatar flex-shrink-0 me-2">
                                                                                        <span
                                                                                            class="avatar-initial rounded bg-label-primary"><i
                                                                                                class="bx bx-camera bx-sm"></i></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="mb-0 text-nowrap">Ulises
                                                                                            sosa</h6>
                                                                                        <small>Tomado por</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <h4 class="mb-2 pb-1">Comentarios</h4>
                                                                        <p class="small">
                                                                            Ninguno.
                                                                        </p>
                                                                        <a href="javascript:void(0);"
                                                                            class="btn btn-primary w-100">Comparar captura
                                                                            anterior</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-between mt-4">
                                                            <button class="btn btn-label-secondary btn-prev">
                                                                <i class="bx bx-left-arrow-alt bx-xs me-sm-1 me-0"></i>
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none">Previous</span>
                                                            </button>
                                                            <button class="btn btn-primary btn-next">
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span>
                                                                <i class="bx bx-right-arrow-alt bx-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Gas -->
                                                    <div id="gas" class="content pt-3 pt-lg-0">
                                                        <div class="mb-3">
                                                            <input type="text" class="form-control form-control-lg"
                                                                id="capturegas" placeholder="Consumo" />
                                                        </div>
                                                        <h5 class="text-center">CAPTURA</h5>
                                                        <div class="card-group mb-5">
                                                            <div class="col-12 col-xl-12 col-md-12">
                                                                <div class="card h-100">
                                                                    <div class="card-body">
                                                                        <div
                                                                            class="bg-label-secondary rounded-3 text-center mb-3 ">
                                                                            <img class="img-fluid w-60"
                                                                                src="{{ asset('assets/img/elements/1.jpg') }}">
                                                                        </div>
                                                                        <div class="row mb-3 g-3">
                                                                            <div class="col-6">
                                                                                <div class="d-flex">
                                                                                    <div class="avatar flex-shrink-0 me-2">
                                                                                        <span
                                                                                            class="avatar-initial rounded bg-label-primary"><i
                                                                                                class="bx bx-time-five bx-sm"></i></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="mb-0 text-nowrap">17 Nov
                                                                                            23 12:30 pm
                                                                                        </h6>
                                                                                        <small>Tomado el</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="d-flex">
                                                                                    <div class="avatar flex-shrink-0 me-2">
                                                                                        <span
                                                                                            class="avatar-initial rounded bg-label-primary"><i
                                                                                                class="bx bx-camera bx-sm"></i></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <h6 class="mb-0 text-nowrap">Ulises
                                                                                            sosa</h6>
                                                                                        <small>Tomado por</small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <h4 class="mb-2 pb-1">Comentarios</h4>
                                                                        <p class="small">
                                                                            Ninguno.
                                                                        </p>
                                                                        <a href="javascript:void(0);"
                                                                            class="btn btn-primary w-100">Comparar captura
                                                                            anterior</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-between mt-4">
                                                            <button class="btn btn-label-secondary btn-prev">
                                                                <i class="bx bx-left-arrow-alt bx-xs me-sm-1 me-0"></i>
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none">Previous</span>
                                                            </button>
                                                            <button class="btn btn-primary btn-next">
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span>
                                                                <i class="bx bx-right-arrow-alt bx-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>


                                                    <!-- billing -->
                                                    <div id="billing" class="content">
                                                        <div id="AppNewCCForm" class="row g-3 pt-3 pt-lg-0 mb-5"
                                                            onsubmit="return false">
                                                            <div class="col-12">
                                                                <div class="input-group input-group-merge">
                                                                    <input class="form-control app-credit-card-mask"
                                                                        type="text" placeholder="1356 3215 6548 7898"
                                                                        aria-describedby="modalAppAddCard" />
                                                                    <span class="input-group-text cursor-pointer p-1"
                                                                        id="modalAppAddCard"><span
                                                                            class="app-card-type"></span></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <input type="text" class="form-control"
                                                                    placeholder="John Doe" />
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <input type="text"
                                                                    class="form-control app-expiry-date-mask"
                                                                    placeholder="MM/YY" />
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <div class="input-group input-group-merge">
                                                                    <input type="text" id="modalAppAddCardCvv"
                                                                        class="form-control app-cvv-code-mask"
                                                                        maxlength="3" placeholder="654" />
                                                                    <span class="input-group-text cursor-pointer"
                                                                        id="modalAppAddCardCvv2"><i
                                                                            class="text-muted bx bx-help-circle"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="Card Verification Value"></i></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="switch">
                                                                    <input type="checkbox" class="switch-input" checked />
                                                                    <span class="switch-toggle-slider">
                                                                        <span class="switch-on"></span>
                                                                        <span class="switch-off"></span>
                                                                    </span>
                                                                    <span class="switch-label">Save card for future
                                                                        billing?</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-between mt-4">
                                                            <button class="btn btn-label-secondary btn-prev">
                                                                <i class="bx bx-left-arrow-alt bx-xs me-sm-1 me-0"></i>
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none">Previous</span>
                                                            </button>
                                                            <button class="btn btn-primary btn-next">
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none me-sm-1">Next</span>
                                                                <i class="bx bx-right-arrow-alt bx-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- submit -->
                                                    <div id="submit" class="content text-center pt-3 pt-lg-0 mb-2">
                                                        <p>Los campos vacios no se guardaran y quedaran registrados como
                                                            pendientes por
                                                            capturar</p>

                                                        <img src="{{ asset('assets/img/illustrations/utilities.png') }}"
                                                            width="300" class="img-fluid"
                                                            data-app-light-img="illustrations/utilities.png"
                                                            data-app-dark-img="illustrations/utilities.png" />
                                                        <div class="col-12 d-flex justify-content-between mt-4 pt-2">
                                                            <button class="btn btn-label-secondary btn-prev">
                                                                <i class="bx bx-left-arrow-alt bx-xs me-sm-1 me-0"></i>
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none">Previous</span>
                                                            </button>
                                                            <button class="btn btn-success btn-submit">
                                                                <span
                                                                    class="align-middle d-sm-inline-block d-none">Guardar</span>
                                                                <i class="bx bx-check bx-xs ms-sm-1 ms-0"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ Wizard Captura-->
                                </div>
                            </div>
                        </div>
                        <!--/ Create App Modal -->
                    </div>
                    <div class="tab-pane fade" id="delivery" role="tabpanel">
                        <div id="accordionDelivery" class="accordion accordion-header-primary">
                            <div class="card accordion-item active">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        aria-expanded="true" data-bs-target="#accordionDelivery-1"
                                        aria-controls="accordionDelivery-1">
                                        ¿Qué se muestran?
                                    </button>
                                </h2>

                                <div id="accordionDelivery-1" class="accordion-collapse collapse show">
                                    <div class="accordion-body">
                                        Dueños / Residencias / Servicios que estaban activados al momento de abrir el
                                        periodo. Asi mismo, los datos de consumos aprobadas en su momento.
                                    </div>
                                </div>
                            </div>

                            <div class="card accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#accordionDelivery-2" aria-controls="accordionDelivery-2">
                                        ¿Qué pasa si edito un registro?
                                    </button>
                                </h2>
                                <div id="accordionDelivery-2" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        Cualquier modificación realizada en este módulo solo afectará al periodo cerrado.
                                        Es decir, si editas un registro, seran sobre datos estaticos y el cambio solo se
                                        aplicará en el recibo de este periodo y no afectará a los datos originales,
                                        relaciones ni a otros periodos.
                                        <br><br>
                                        Si se desea que un cambio se aplique de manera permanente y para todos los periodos,
                                        será necesario hacerlo en el módulo correspondiente de administración general.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="card card-action mb-4">
                                    <div class="card-header">
                                        <div class="card-action-title">
                                            <h5>Datos Utilities</h5>
                                            <li class="">Doble click para editar celda en la tabla.</li>
                                            <li class="">Click en cancelar u otra celda para descartar edición
                                                anterior.</li>
                                            <li class="">Click en aplicar para guardar cambios.</li>
                                        </div>
                                        <div class="card-action-element">
                                            <ul class="list-inline mb-0">
                                                <li class="list-inline-item">
                                                    <a href="javascript:void(0);" class="card-expand"><i
                                                            class="tf-icons bx bx-fullscreen"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="card-body" id="dataUtilities">
                                        <div class="table-responsive">
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
                                                    @foreach ($utilities as $idx => $item)
                                                        <tr>
                                                            <td id="{{ $item->id }}">
                                                                <b>#{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</b></td>
                                                            <td id="{{ $item->id }}" column="residencia">
                                                                {{ $item->residencia }}</td>
                                                            <td id="{{ $item->id }}" column="room">
                                                                {{ $item->room }}</td>
                                                            <td id="{{ $item->id }}" column="owner">
                                                                {{ $item->owner }}</td>
                                                            <td id="{{ $item->id }}" column="ocupacion">
                                                                {{ $item->ocupacion }}</td>
                                                            <td id="{{ $item->id }}" column="kw">
                                                                {{ number_format($item->kw, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="agua">
                                                                {{ number_format($item->agua, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="gas">
                                                                {{ number_format($item->gas, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total_kw">
                                                                ${{ number_format($item->total_kw, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total_kwfee">
                                                                ${{ number_format($item->total_kwfee, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total_gas">
                                                                ${{ number_format($item->total_gas, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total_gasfee">
                                                                ${{ number_format($item->total_gasfee, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total_agua">
                                                                ${{ number_format($item->total_agua, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total_sewer">
                                                                ${{ number_format($item->total_sewer, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="subtotal">
                                                                ${{ number_format($item->subtotal, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="tax">
                                                                ${{ number_format($item->tax, 2) }}</td>
                                                            <td id="{{ $item->id }}" column="total">
                                                                ${{ number_format($item->total, 2) }}</td>
                                                        </tr>
                                                    @endforeach
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
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Wizard' -->
        </div>
    </div>
@endsection
@section('script')
    <script>
        var utilitiesupdate = "{{ route('utilities.update') }}";
    </script>
    <!-- Vendors JS Datatable -->
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <!-- Vendors JS Modal Tab-->
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <!-- Vendors JS Toast -->
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
    <script src="{{ asset('assets/jsv3/ui-toasts.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/viewperiod.js') }}"></script>
    <!-- JS SWIPER -->
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('assets/jsv3/ui-carousel.js') }}"></script>
@endsection
