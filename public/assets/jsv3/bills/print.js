function printDiv(divId) {
  // Guardar la posición actual de la ventana
  var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

  // Ocultar todos los divs
  var divsToHide = document.querySelectorAll('.tm_container');
  divsToHide.forEach(function(div) {
      div.style.display = 'none';
  });

  // Mostrar el div que se va a imprimir
  var divToPrint = document.getElementById(divId);
  divToPrint.style.display = 'block';

  // Imprimir el div deseado
  window.print();

  // Mostrar todos los divs nuevamente
  divsToHide.forEach(function(div) {
      div.style.display = 'block';
  });

  // Restaurar la posición de la ventana después de imprimir
  window.scrollTo(scrollLeft, scrollTop);
}
