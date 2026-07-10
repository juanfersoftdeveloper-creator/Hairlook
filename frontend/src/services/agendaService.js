/**
 * Servicio de agenda — conectado al backend PHP.
 */

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost/Hairlook/backend/public';

/**
 * Obtiene la agenda de un profesional
 * @param {number} id_profesional - ID del profesional
 * @param {boolean} soloPendientes - Si true, solo citas pendientes
 * @returns {Promise<{ok: boolean, data?: Array, error?: string}>}
 */
export async function traerAgendaProfesional(id_profesional, soloPendientes = true) {
  try {
    const res = await fetch(`${API_BASE}/c_agenda_profesional.php?id_profesional=${id_profesional}&solo_pendientes=${soloPendientes}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: json.data };
    }

    return { ok: false, error: json.error || 'Error al obtener agenda' };
  } catch (error) {
    console.error('Error traerAgendaProfesional:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}

/**
 * Obtiene la disponibilidad de un profesional
 * @param {number} id_profesional - ID del profesional
 * @param {string} diaSemana - Día de la semana (opcional)
 * @returns {Promise<{ok: boolean, data?: Array, error?: string}>}
 */
export async function traerDisponibilidad(id_profesional, diaSemana = null) {
  try {
    let url = `${API_BASE}/c_disponibilidad.php?id_profesional=${id_profesional}`;
    if (diaSemana) {
      url += `&dia_semana=${diaSemana}`;
    }

    const res = await fetch(url, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: json.data };
    }

    return { ok: false, error: json.error || 'Error al obtener disponibilidad' };
  } catch (error) {
    console.error('Error traerDisponibilidad:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}

/**
 * Guarda la disponibilidad de un profesional
 * @param {number} id_profesional - ID del profesional
 * @param {Array} dias - Array de {dia_semana, hora_inicial, hora_fin}
 * @returns {Promise<{ok: boolean, error?: string}>}
 */
export async function guardarDisponibilidad(id_profesional, dias) {
  try {
    const res = await fetch(`${API_BASE}/c_disponibilidad.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id_profesional,
        disponibilidad: dias,
      }),
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true };
    }

    return { ok: false, error: json.error || 'Error al guardar disponibilidad' };
  } catch (error) {
    console.error('Error guardarDisponibilidad:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}
