//***WIZARD MESES ARRASTRABLE***/
// --------------------------------------------------------------------
let isDragging = false;
let startX = 0;
let scrollLeft = 0;

const header = document.getElementById('header');

header.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.pageX - header.offsetLeft;
    scrollLeft = header.scrollLeft;
});

header.addEventListener('mouseup', () => {
    isDragging = false;
});

header.addEventListener('mouseleave', () => {
    isDragging = false;
});

header.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - header.offsetLeft;
    const walk = (x - startX) * 2; // Se puede ajustar la velocidad del desplazamiento
    header.scrollLeft = scrollLeft - walk;
});

$(document).ready(function () {
    //***PICKEAR ONLY YEAR***/
    // --------------------------------------------------------------------
    var dateNow = $('#dateNow').val();
    var minYear = 2021;
    var yearNow = moment(dateNow, 'YYYY-MM-DD').year(); //2022
    var maxyear = moment().year();


    $('#yearPicker').datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true,
        startDate: new Date(minYear, 0, 1), // Define el primer día del año mínimo
        endDate: new Date(maxyear, 11, 31),  // Define el último día del año actual
        defaultViewDate: { year: yearNow }
    });


    //***PICKEAR CLASE/BOTON IR PERIODO***/
    // --------------------------------------------------------------------
    $('.period_status').click(function (event) {
        event.preventDefault();
        //obtener elemento span
        var spanElement = $(this).prev('span');
        // Obtiene el valor de Datenow del atributo del elemento span
        var period_start = $(this).attr("period_start").split("/").reverse().join("-");;
        var period_end = $(this).attr("period_end").split("/").reverse().join("-");;
        // Obtiene el valor de status del atributo status del elemento span
        var status_id = $(this).prev(".badge").attr("status");
        // Obtener la fecha actual en el formato YYYY-MM-DD
        var currentDate = new Date().toISOString().slice(0, 10);
        // Obtener el token CSRF de la etiqueta meta
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        //si periodo esta sin capturar
        if (status_id === "1") {
            if (currentDate <= period_start) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede abrir enlace',
                    text: 'Este mes no ha iniciado periodo de captura.',
                    confirmButtonText: 'Aceptar'
                });
                return;
                // Aquí puedes mostrar el mensaje o realizar alguna acción adicional
            } else {
                Swal.fire({
                    icon: 'question',
                    title: '¿Desea poner este mes en curso?',
                    text: 'Se requiere cambiar manualmente el estado del periodo para abrirlo',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Seleccione Status',
                            html:
                                '<div style="display:flex; align-items:center; justify-content:center;">' +
                                '<select id="period_status" class="swal2-select form-control" style="width:60px">' +
                                '<option value="">Seleccionar</option>' +
                                '<option value="' + status2.id + '">' + status2.name + '</option>' +
                                '</select>' +
                                '</div>',
                            showCancelButton: true,
                            confirmButtonText: 'Guardar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Verificar si se ha seleccionado un status
                                var selectedStatus = document.getElementById('period_status').value;
                                if (!selectedStatus) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Por favor, seleccione un status antes de guardar.'
                                    });
                                    return; // Detener la ejecución si no se ha seleccionado un status
                                }

                                //iniciar bloqueo UI
                                $.blockUI({
                                    message: '<div class="spinner-border text-white" role="status"></div>',
                                    css: {
                                        backgroundColor: 'transparent',
                                        border: 'none',
                                        padding: '15px',
                                        fontSize: '20px',
                                        'z-index': 9999,
                                    },
                                    overlayCSS: {
                                        opacity: 0.5,
                                    }
                                });

                                // Crear un objeto con los datos del período
                                var periodData = {
                                    _token: csrfToken,
                                    start: period_start,
                                    end: period_end,
                                };
                                // Enviar los datos al servidor utilizando Ajax
                                $.ajax({
                                    type: 'POST',
                                    url: createperiod_utilities,
                                    data: periodData,
                                    dataType: 'json',
                                    success: function (response) {
                                        $.unblockUI();
                                        Swal.fire({
                                            icon: 'success',
                                            title: '¡Éxito!',
                                            text: response.message
                                        });
                                        // Cambiar el atributo 'status'
                                        spanElement.attr('status', status2.id).text(status2.name);
                                        // Remover todas las clases de color de fondo del span
                                        spanElement.removeClass('bg-label-secondary bg-label-primary bg-label-danger');
                                        // Agregar la clase CSS basada en la clase de status2
                                        spanElement.addClass('bg-label-' + status2.class);

                                    },
                                    error: function (xhr, status, error) {
                                        $.unblockUI();
                                        Swal.fire({
                                            icon: 'error',
                                            title: '¡Error!',
                                            text: 'Hubo un error al crear el período.'
                                        });
                                    }
                                });
                            } else {
                                console.log("El usuario canceló la operación de guardar.");
                            }
                        });
                    } else {
                        console.log("El usuario canceló iniciar el período.");
                    }
                });
                return;
            }
        }
        window.location.href = $(this).attr('href');
    });
});