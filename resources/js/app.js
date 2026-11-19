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
        for (const campo of ['nombre', 'imagen_url', 'spotify_id']) {
            document.getElementById(campo).value = artista[campo] ?? '';
        }
        // Spotify dejó de enviar géneros a las apps en modo desarrollo: si no llegan, se conserva lo escrito.
        if (artista.generos) {
            document.getElementById('generos').value = artista.generos;
        }
        this.resultados = [];
        this.buscado = false;
    },
}));

// Botón de favorito: consume la API REST (PUT agrega, DELETE quita) sin recargar.
// Si la petición falla, se envía el formulario normal, que hace lo mismo por la ruta web.
Alpine.data('botonFavorito', (marcado, urlApi) => ({
    marcado,
    enviando: false,

    async alternar(formulario) {
        if (this.enviando) return;
        this.enviando = true;
        try {
            await window.axios({ method: this.marcado ? 'delete' : 'put', url: urlApi });
            this.marcado = !this.marcado;
        } catch (e) {
            formulario.submit();
        } finally {
            this.enviando = false;
        }
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
