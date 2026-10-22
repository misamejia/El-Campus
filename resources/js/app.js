import './bootstrap';

const botonMenu = document.querySelector('[data-menu-boton]');
const menu = document.querySelector('[data-menu-contenido]');

if (botonMenu && menu) {
    botonMenu.addEventListener('click', () => {
        const oculto = menu.classList.toggle('hidden');
        botonMenu.setAttribute('aria-expanded', String(!oculto));
    });
}
