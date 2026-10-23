/* Tema claro u oscuro. Se carga en el <head> para aplicar el tema antes de dibujar la página */
var tema = localStorage.getItem('tema') || 'dark';

function aplicarTema() {
  document.documentElement.setAttribute('data-theme', tema);
  document.documentElement.setAttribute('data-bs-theme', tema);
}

aplicarTema();

/* el botón de sol/luna tiene el atributo data-tema-toggle */
document.addEventListener('click', function (evento) {
  if (evento.target.closest('[data-tema-toggle]')) {
    tema = tema === 'dark' ? 'light' : 'dark';
    localStorage.setItem('tema', tema);
    aplicarTema();
  }
});
