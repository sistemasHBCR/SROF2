const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

// Variable para almacenar el mensaje
let message = '';

async function checkSession() {
    try {
        const response = await fetch(`${baseUrl}/check-session`, {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache'
            }
        });
        const data = await response.json();

        // Define el estado en función del valor de guest
        const status = data.guest ? 'inactive' : 'active';

        // Actualiza el mensaje basado en el estado
        if (status === 'active') {
            //message = 'The session is active.';
        } else {
            //message = 'The session is inactive.';
            location.reload();
        }
    } catch (error) {
        console.error('Error fetching session status:', error);
    }
}

// Ejecutar checkSession cada 60 segundos (60000 milisegundos)
setInterval(checkSession, 60000);

// Ejecutar checkSession inmediatamente al cargar la página
checkSession();