import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Buscador del formulario de artistas: consulta Spotify y llena los campos con el resultado elegido.
Alpine.data('buscadorSpotify', (url) => ({
    q: '',
    resultados: [],
    cargando: false,
    buscado: false,
    error: '',

    async buscar() {
        if (this.q.trim().length < 2) return;
        this.cargando = true;
        this.error = '';
        try {
            const { data } = await window.axios.get(url, { params: { q: this.q.trim() } });
            this.resultados = data.artistas;
        } catch (e) {
            this.resultados = [];
            this.error = e.response?.data?.mensaje ?? 'No se pudo consultar Spotify.';
        } finally {
            this.cargando = false;
            this.buscado = true;
        }
    },

    usar(artista) {
        for (const campo of ['nombre', 'generos', 'imagen_url', 'spotify_id']) {
            document.getElementById(campo).value = artista[campo] ?? '';
        }
        this.resultados = [];
    },
}));

Alpine.start();

// Confirmación de borrado: el texto viaja en data-confirmar para no armar JavaScript con datos del usuario.
document.addEventListener('submit', (evento) => {
    const mensaje = evento.target.dataset.confirmar;
    if (mensaje && !window.confirm(mensaje)) {
        evento.preventDefault();
    }
});
