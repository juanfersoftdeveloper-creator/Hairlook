/**
 * Servicio de notificaciones — conectado al backend PHP.
 */

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost/Hairlook/backend/public';

/**
 * Obtiene las notificaciones de un profesional
 * @param {number} id_profesional - ID del profesional
 * @returns {Promise<{ok: boolean, data?: Array, error?: string}>}
 */
export async function traerNotificaciones(id_profesional) {
  try {
    const res = await fetch(`${API_BASE}/c_notificaciones.php?id_profesional=${id_profesional}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: json.data };
    }

    return { ok: false, error: json.error || 'Error al obtener notificaciones' };
  } catch (error) {
    console.error('Error traerNotificaciones:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}

/**
 * Marca una notificación como leída
 * @param {number} id_notificacion - ID de la notificación
 * @returns {Promise<{ok: boolean, error?: string}>}
 */
export async function marcarNotificacionLeida(id_notificacion) {
  try {
    const res = await fetch(`${API_BASE}/c_marcar_notificacion.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_notificacion }),
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true };
    }

    return { ok: false, error: json.error || 'Error al marcar notificación' };
  } catch (error) {
    console.error('Error marcarNotificacionLeida:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}
