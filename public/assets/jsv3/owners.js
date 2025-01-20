'use strict';

var dt_owner; // Variable global para la instancia de DataTable

$(document).ready(function () {
    // Inicializar DataTable
    dt_owner = $('#tb-owner').DataTable({
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
                text: '+<i class="bx bx-user me-md-1 sm"></i><span class="d-md-inline-block d-none">Dueño</span>',
                className: 'newowner btn btn-secondary btn-sm',
                action: function (e, dt, button, config) {
                    $('#dataOwner').modal('show');
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
            targets: [4],
            orderable: false
        }]
    });


    // Función para limpiar el modal
    /**
    *  Modal CRUD Owner
    */

    //abrir form - crear
    $(document).on('click', 'button.newowner', function () {
        $('#owner_btnupdate').hide();
        $('#owner_btnnew').show();
    });

    //abrir form - leer
    $(document).on('click', 'button.data-owner', function () {
        var id = $(this).attr('id');
        $('#owner_btnupdate').show();
        $('#owner_btnnew').hide();
        $.ajax({
            url: './owners/' + id + '/edit',
            type: 'GET',
            data: { id: id },
            success: function (response) {
                // Mostrar los datos en el formulario
                $('#dataOwnerTitle').text("Dueño #" + response.owner.id);
                $('#ownerid').val(response.owner[0].id);
                $('#name').val(response.owner[0].name);
                $('#email').val(response.owner[0].email);
                $('#active').val(response.owner[0].active).trigger('change');
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    //limpiar modal
    function limpiar() {
        $('#dataownerTitle').text("Agregar dueño");
        $('#ownerid').val("");
        $('#name').val("");
        $('#email').val("");
        $('#active').val("").trigger('change');
        $('#owner_btnupdate').hide();
        $('#owner_btnnew').hide();
    }

    //cerrar form
    $('button.btn-close').click(function () {
        limpiar();
    });


    // Captura el evento submit del formulario
    $('#formowners').on('submit', function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            event.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        if ($('#owner_btnupdate').is(':visible')) {
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
                    var id = $('#ownerid').val();
                    var name = $('#name').val().trim();
                    var email = $('#email').val().trim();
                    var active = $('#active').val();

                    $.ajax({
                        url: './owners/' + id,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'PUT',
                        data: { id: id, email: email, name: name, active: active },
                        success: function (response) {
                            //cerrar modal
                            $('#dataOwner').modal('hide');
                            limpiar();
                            //toast message
                            toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                            //actualizar fila
                            var id = response.owner.id;
                            var statusIcon = "";
                            if (response.owner.active === 'Y') {
                                statusIcon = '<a class="active bx bx-check text-success bx-sm me-2"></a>';
                            } else {
                                statusIcon = '<a class="active bx bx-x text-danger bx-sm me-2"></a>';
                            }

                            var fila = $('tr[row="' + id + '"]');
                            fila.find('.name').text(response.owner.name);
                            fila.find('.email').text(response.owner.email);
                            fila.find('.status').html(statusIcon);

                        },
                        error: function (xhr, status, error) {
                            toastr.error(xhr.responseJSON.errors.name[0], { progressBar: true, showDuration: 1000, hideDuration: 1000 });
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
                    var name = $('#name').val().trim();
                    var email = $('#email').val().trim();
                    var active = $('#active').val();

                    $.ajax({
                        url: './owners',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        type: 'POST',
                        data: { name: name, email: email, active: active },
                        success: function (response) {

                            var numRows = dt_owner.rows().count() + 1;
                            var active = "";
                            if (response.owner.active === 'Y') {
                                active = '<a class="active bx bx-check text-success bx-sm me-2"></a>';
                            } else {
                                active = '<a class="active bx bx-x text-danger bx-sm me-2"></a>';
                            }
                            var newRow = '<tr row="' + response.owner.id + '">' +
                                '<td class="sorting_1">' + numRows + '</td>' +
                                '<td class="name">' + response.owner.name + '</td>' +
                                '<td class="email">' + (response.owner.email !== null ? response.owner.email : '') + '</td>' +
                                '<td class="status">' + active + '</td>' +
                                '<td><button type="button" class="data-owner btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dataOwner" id="' + response.owner.id + '">Editar</button></td>' +
                                '</tr>';

                            dt_owner.row.add($(newRow)).draw();

                            $('#dataOwner').modal('hide');
                            limpiar();
                            toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                        },
                        error: function (xhr, status, error) {
                            toastr.error(xhr.responseJSON.errors.name[0], { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                        }
                    });
                }
            });
        }
        this.classList.add('was-validated');
    });
});
