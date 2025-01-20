'use strict';

var dt_residence; // Variable global para la instancia de DataTable
$(document).ready(function () {
    // Inicializar DataTable
    dt_residence = $('#tb-residence').DataTable({
        paging: true,
        ordering: true,
        searching: true,
        lengthChange: true,
        pageLength: 25,
        dom:
            '<"row mx-1"' +
            '<"col-12 col-md-6 d-flex align-items-center justify-content-center justify-content-md-start gap-3"l<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start mt-md-0 mt-3"B>>' +
            '<"col-12 col-md-6 d-flex align-items-center justify-content-end flex-column flex-md-row pe-3 gap-md-3"f<"invoice_status mb-3 mb-md-0">>' +
            '>t' +
            '<"row mx-2"' +
            '<"col-sm-12 col-md-6"i>' +
            '<"col-sm-12 col-md-6"p>' +
            '>',
        buttons: [
            {
                text: '+<i class="bx bx-home-alt me-md-1 sm"></i><span class="d-md-inline-block d-none">Residencia</span>',
                className: 'newresidence btn btn-secondary btn-sm',
                action: function (e, dt, button, config) {
                    $('#dataResidence').modal('show');
                }
            }
        ],
        language: {
            paginate: {
                next: '<i class="fas fa-chevron-right"></i>',
                previous: '<i class="fas fa-chevron-left"></i>'
            }
        },
        columnDefs: [{
            targets: [5],
            orderable: false
        }]
    });


    // Función para limpiar el modal
    /**
    *  Modal CRUD Residencia
    */

    //abrir form - crear
    $(document).on('click', 'button.newresidence', function () {
        $('#btnupdate').hide();
        $('#btnnew').show();
    });

    //abrir form - leer
    $(document).on('click', 'button.data-residence', function () {
        var id = $(this).attr('id');
        $('#btnupdate').show();
        $('#btnnew').hide();
        $.ajax({
            url: './residences/' + id + '/edit',
            type: 'GET',
            data: { id: id },
            success: function (response) {
                const ownerIds = response.residence[0].owner.map(owner => owner.pivot.owner_id);
                console.log(ownerIds);
                // Mostrar los datos en el formulario
                $('#dataResidenceTitle').text("Residencia " + response.residence.number);
                $('#residenceid').val(response.residence[0].id);
                $('#number').val(response.residence[0].number);
                $('#name').val(response.residence[0].name);
                $('#owner').val(ownerIds);
                $('#owner').selectpicker('refresh');
                $('#active').val(response.residence[0].active).trigger('change');
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    });

    //limpiar modal
    function limpiar() {
        $('#dataResidenceTitle').text("Añadir Residencia");
        $('#residenceid').val("");
        $('#number').val("");
        $('#name').val("");
        $('#owner').selectpicker('deselectAll');
      //  $('#active').val("").trigger('change');
        $('#btnupdate').hide();
        $('#btnnew').hide();
    }

    //cerrar form
    $('button.btn-close').click(function () {
        limpiar();
    });


    // Captura el evento submit del formulario
    $('#formresidences').on('submit', function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            event.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        if ($('#btnupdate').is(':visible')) {
            // Actualizar registro existente
            Swal.fire({
                title: 'Guardar registro',
                text: '¿Estás seguro de guardar cambios?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                allowEnterKey: false,
                backdrop: 'static',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = $('#residenceid').val();
                    var number = $('#number').val().trim();
                    var name = $('#name').val().trim();
                    var owner = $('#owner').val();
                    var active = $('#active').val();

                    $.ajax({
                        url: './residences/' + id,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'PUT',
                        data: { id: id, number: number, name: name, owner: owner, active: active },
                        success: function (response) {
                            //cerrar modal
                            $('#dataResidence').modal('hide');
                            limpiar();
                            //toast message
                            toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                            //actualizar fila
                            var id = response.residence.id;
                            var statusIcon = "";
                            if (response.residence.active === 'Y') {
                                statusIcon = '<a class="active bx bx-check text-success bx-sm me-2"></a>';
                            } else {
                                statusIcon = '<a class="active bx bx-x text-danger bx-sm me-2"></a>';
                            }
                            var fila = $('tr[row="' + id + '"]');
                            fila.find('.number').text(response.residence.number);
                            fila.find('.name').text(response.residence.name);
                            fila.find('.owner').text(response.owner);
                            fila.find('.status').html(statusIcon);

                        },
                        error: function (xhr, status, error) {
                            toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                        }
                    });
                }
            });
        } else {
            // Crear nuevo registro
            Swal.fire({
                title: 'Crear registro',
                text: '¿Estás seguro de guardar cambios?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                allowEnterKey: false,
                backdrop: 'static',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var number = $('#number').val().trim();
                    var name = $('#name').val().trim();
                    var owner = $('#owner').val();
                    var active = $('#active').val();

                    $.ajax({
                        url: './residences',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'POST',
                        data: { number: number, name: name, owner: owner, active: active },
                        success: function (response) {

                            var numRows = dt_residence.rows().count() + 1;
                            var active = "";
                            if (response.residence.active === 'Y') {
                                active = '<a class="active bx bx-check text-success bx-sm me-2"></a>';
                            } else {
                                active = '<a class="active bx bx-x text-danger bx-sm me-2"></a>';
                            }

                            var newRow = '<tr row="' + response.residence.id + '">' +
                                '<td class="sorting_1">' + numRows + '</td>' +
                                '<td class="number">' + response.residence.number + '</td>' +
                                '<td class="name">' + response.residence.name + '</td>' +
                                '<td class="owner">' + response.owners + '</td>' +
                                '<td class="status">' + active + '</td>' +
                                '<td><button type="button" class="data-residence btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dataResidence" id="' + response.residence.id + '">Editar</button></td>' +
                                '</tr>';
                            dt_residence.row.add($(newRow)).draw();


                            $('#dataResidence').modal('hide');
                            limpiar();
                            toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                        },
                        error: function (xhr, status, error) {
                            toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                        }
                    });
                }
            });
        }
        this.classList.add('was-validated');
    });

    //autocompletar campo nombre
    $('#number').on('input', function () {
        const numberValue = $(this).val();
        if (numberValue.length === 4) {
            const formattedName = numberValue[0] + '-' + numberValue.slice(1);
            $('#name').val(formattedName);
        } else {
            $('#name').val(''); // Limpiar si no tiene 4 dígitos
        }
    });

});
