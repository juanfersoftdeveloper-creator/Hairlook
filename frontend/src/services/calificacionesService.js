/**
 * Servicio de calificaciones — conectado al backend PHP.
 */

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost/Hairlook/backend/public';

/**
 * Envía una calificación para un profesional
 * @param {object} datos - { id_cita, id_usuario, id_profesional, puntuacion, comentario }
 * @returns {Promise<{ok: boolean, data?: object, error?: string}>}
 */
export async function enviarCalificacion(datos) {
  try {
    const res = await fetch(`${API_BASE}/c_calificar.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos),
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: json.data };
    }

    return { ok: false, error: json.error || 'Error al calificar' };
  } catch (error) {
    console.error('Error enviarCalificacion:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}

/**
 * Obtiene las calificaciones de un profesional
 * @param {number} id_profesional - ID del profesional
 * @returns {Promise<{ok: boolean, data?: Array, error?: string}>}
 */
export async function traerCalificacionesProfesional(id_profesional) {
  try {
    const res = await fetch(`${API_BASE}/c_calificaciones_profesional.php?id_profesional=${id_profesional}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: json.data };
    }

    return { ok: false, error: json.error || 'Error al obtener calificaciones' };
  } catch (error) {
    console.error('Error traerCalificacionesProfesional:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}