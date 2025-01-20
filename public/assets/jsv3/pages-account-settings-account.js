/**
 * Account Settings - Account
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const formAccSettings = document.querySelector('#formAccountSettings'),
      deactivateAcc = document.querySelector('#formAccountDeactivation'),
      deactivateButton = deactivateAcc.querySelector('.deactivate-account');

    // Form validation for Add new record
    if (formAccSettings) {
      const fv = FormValidation.formValidation(formAccSettings, {
        fields: {
          firstName: {
            validators: {
              notEmpty: {
                message: 'Please enter first name'
              }
            }
          },
          lastName: {
            validators: {
              notEmpty: {
                message: 'Please enter last name'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.col-md-6'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          // Submit the form when all fields are valid
          // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });
    }

    if (deactivateAcc) {
      const fv = FormValidation.formValidation(deactivateAcc, {
        fields: {
          accountActivation: {
            validators: {
              notEmpty: {
                message: 'Por favor marca el checkbox'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: ''
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          fieldStatus: new FormValidation.plugins.FieldStatus({
            onStatusChanged: function (areFieldsValid) {
              areFieldsValid
                ? // Enable the submit button
                // so user has a chance to submit the form again
                deactivateButton.removeAttribute('disabled')
                : // Disable the submit button
                deactivateButton.setAttribute('disabled', 'disabled');
            }
          }),
          // Submit the form when all fields are valid
          // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });
    }

    // Deactivate account alert
    const accountActivation = document.querySelector('#accountActivation');

    // Alert With Functional Confirm Button
    if (deactivateButton) {
      deactivateButton.onclick = function () {
        if (accountActivation.checked == true) {
          Swal.fire({
            text: 'Un paso más',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si',
            customClass: {
              confirmButton: 'btn btn-primary me-2',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          }).then(function (result) {
            if (result.value) {
              // Llamada AJAX para desactivar el usuario

              var csrfToken = $('meta[name="csrf-token"]').attr('content');
              $.ajax({
                url: accountDesactivation,
                type: 'POST',
                data: { _token: csrfToken },
                success: function (response) {
                  
                  setTimeout(function () {
                    location.reload(true);
                  }, 2000);

                  Swal.fire({
                    icon: 'success',
                    title: 'Desactivado!',
                    text: 'Su usuario queda inactivo',
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  });
                },
                error: function (xhr, status, error) {
                  Swal.fire({
                    title: 'Cancelado',
                    text: 'Problemas al iniciar esta solicitud con el servidor',
                    icon: 'error',
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  });
                }
              });
    
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              Swal.fire({
                title: 'Cancelado',
                text: 'Desactivación Cancelada!',
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          });
        }
      };
    }

    // CleaveJS validation

    const phoneNumber = document.querySelector('#phoneNumber'),
      zipCode = document.querySelector('#zipCode');
    // Phone Mask
    if (phoneNumber) {
      new Cleave(phoneNumber, {
        phone: true,
        phoneRegionCode: 'US'
      });
    }

    // Pincode
    if (zipCode) {
      new Cleave(zipCode, {
        delimiter: '',
        numeral: true
      });
    }

    // Update/reset user image of account page
    let accountUserImage = document.getElementById('uploadedAvatar');
    const fileInput = document.querySelector('.account-file-input'),
      resetFileInput = document.querySelector('.account-image-reset');

    if (accountUserImage) {
      const resetImage = accountUserImage.src;
      fileInput.onchange = () => {
        if (fileInput.files[0]) {
          accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
        }
      };
      resetFileInput.onclick = () => {
        fileInput.value = '';
        accountUserImage.src = resetImage;
      };
    }
  })();
});

// Select2 (jquery)
$(function () {
  var select2 = $('.select2');
  // For all Select2
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>');
      $this.select2({
        dropdownParent: $this.parent()
      });
    });
  }
});
