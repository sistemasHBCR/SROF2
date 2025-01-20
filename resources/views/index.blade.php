@extends('layout.main')

@section('title')
    Periodos mensuales de captura
@endsection

@section('css')
    <!-- Complemento refresh cards CSS-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/spinkit/spinkit.css') }}" />
    <!-- Vendors CSS SWEET ALERT-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <!-- Vendors CSS PICKER-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <!-- Vendors CSS WIZARD-->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <!-- CSS index-->
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}" />
@endsection


@section('contenido')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light">Periodos / </span> Utilities
        </h4>
        <!-- Default -->


        <!-- Month Wizard -->
        <div class="col-12 mb-4">
            <div class="bs-stepper wizard-numbered mt-2">
                <br>
                <div class="col-12 mb-4 ml-2">
                    <div class="card" style="border: none; box-shadow: none;">
                        <form method="GET" action="{{ route('utilities-periods') }}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 col-12">
                                        <div class="input-group date" id="yearPicker" data-target-input="nearest">
                                            <input id="yearInput" name="year" type="text"
                                                class="form-control form-control-sm datetimepicker-input"
                                                data-target="#yearPicker" value="{{ $dateNow->format('Y') }}" readonly>
                                            <div class="input-group-append" data-target="#yearPicker"
                                                data-toggle="datepicker">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-12">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="fa-solid fa-filter" style="margin-right: 5px;"></i> <span
                                                class="d-none d-md-inline">Filtrar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bs-stepper-header" id="header">
                    @foreach ($months as $month)
                        <div class="step" data-target="#{{ $month['target'] }}">
                            <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle">{{ $month['number'] }}</span>
                                <span class="bs-stepper-label">
                                    <span class="bs-stepper-title">{{ $month['title'] }}</span>
                                </span>
                            </button>
                        </div>
                        <div class="line"></div>
                    @endforeach
                </div>
                <div class="bs-stepper-content">
                    <form onSubmit="return false">
                        <input type="hidden" id="dateNow" value="{{ $dateNow->format('Y-m-d') }}">
                        @foreach ($months as $i => $month)
                            <div id="{{ $month['target'] }}" class="content">
                                <div class="content-header mb-3">
                                    <h6 class="mb-0">Periodo {{ $month['title'] }}</h6>
                                    <small>{{ $month['start_date'] }} - {{ $month['end_date'] }}</small>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="demo-inline-spacing">
                                            @if (!empty($periods[$i]))
                                                @foreach ($periods[$i] as $period)
                                                    <span class="badge bg-label-{{ $period['status']['class'] }}"
                                                        status="{{ $period['status']['id'] }}">{{ $period['status']['name'] }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge bg-label-{{ $status1->class }}"
                                                    status="{{ $status1->id }}">{{ $status1->name }}</span>
                                            @endif
                                            @canany(['utilities.createperiod', 'utilities.viewperiod'])
                                                <a href="{{ route('viewperiod', ['start_date' => $month['start_date'], 'end_date' => $month['end_date']]) }}"
                                                    class="text-dark period_status" period_start="{{ $month['start_date'] }}"
                                                    period_end="{{ $month['end_date'] }}">
                                                    <i class="fa-solid fa-right-from-bracket"></i>
                                                </a>
                                            @endcanany
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </form>
                </div>
            </div>
        </div>
        <!-- /Month Wizard -->
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        var status1 = {!! json_encode($status1) !!};
        var status2 = {!! json_encode($status2) !!};
        var status3 = {!! json_encode($status3) !!};
        var createperiod_utilities = "{{ route('createperiod.utilities') }}";
    </script>
    <!-- Ui block-->
    <script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>
    <!-- JS WIZARD-->
    <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <script src="{{ asset('assets/jsv3/form-wizard-numbered.js') }}"></script>
    <!-- JS SWEET ALERT -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <!-- JS PICKER  -->
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/jsv3/index.js') }}"></script>
@endsection
