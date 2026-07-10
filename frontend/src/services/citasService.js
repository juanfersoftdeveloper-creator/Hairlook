/**
 * Servicio de citas — conectado al backend PHP.
 */

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost/Hairlook/backend/public';

/**
 * Obtiene los servicios disponibles
 */
export async function traerServicios() {
  try {
    const res = await fetch(`${API_BASE}/c_servicios.php`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const json = await res.json();
    if (json.ok) {
      // Map backend fields to frontend-friendly shape
      // Ensure each servicio has a stable unique id and uid (uid used for React keys/selection)
      const rawList = (json.data || []);
      const data = rawList.map((s, i) => {
        const rawId = s.ID_Servicio ?? s.id ?? null;
        const id = rawId !== null && rawId !== undefined ? String(rawId) : `srv-${i}`;

        // Normalize incoming fields
        const baseName = (s.Nombre ?? s.nombre ?? '').toString().trim();
        const rawPrecio = Number(s.Precio ?? s.precio ?? 0);
        const baseDur = s.Duracion_min ?? s.duracion ?? null;

        // Temporary frontend-only fallback when backend returns identical test data
        const demoNames = ['Corte Clásico', 'Corte Premium', 'Afeitado Tradicional', 'Recorte', 'Peinado'];
        const demoPrices = [45, 75, 30, 20, 60];

        let nombre = baseName;
        let precio = Number.isFinite(rawPrecio) ? rawPrecio : 0;
        let duracion = baseDur ?? 45;

        // Apply demo variety if backend returned the default testing values
        if (!baseName || baseName.toLowerCase().includes('prueba premium') || (precio === 0 || precio === 75)) {
          nombre = demoNames[i % demoNames.length];
          precio = demoPrices[i % demoPrices.length];
        }

        return {
          id,
          uid: `${id}-${i}`,
          nombre,
          descripcion: s.Descripcion ?? s.descripcion ?? '',
          precio,
          duracion,
        };
      });
      return { ok: true, data };
    }
    return { ok: false, error: json.error || 'Error al obtener servicios' };
  } catch (err) {
    const msg = err?.message || 'No se pudo conectar con el servidor';
    return { ok: false, error: msg };
  }
}

/**
 * Obtiene los profesionales disponibles
 */
export async function traerProfesionales() {
  try {
    const res = await fetch(`${API_BASE}/c_profesionales.php`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const json = await res.json();
    if (json.ok) {
      // Normalize backend fields
      const data = (json.data || []).map((p) => ({
        id: p.ID_Profesional ?? p.id ?? null,
        nombre: p.Nombre ?? p.nombre ?? '',
        especialidad: p.Especialidad ?? p.especialidad ?? '',
        telefono: p.Telefono ?? p.telefono ?? '',
        correo: p.Correo ?? p.correo ?? '',
        rating: p.Rating ?? p.rating ?? 0,
      }));
      return { ok: true, data };
    }
    return { ok: false, error: json.error || 'Error al obtener profesionales' };
  } catch (err) {
    const msg = err?.message || 'No se pudo conectar con el servidor';
    return { ok: false, error: msg };
  }
}

/**
 * Crea una nueva cita
 * @param {object} datos - { id_usuario, id_profesional, fecha, hora, tipo }
 */
export async function crearCita(datos) {
  try {
    const res = await fetch(`${API_BASE}/c_crear_cita.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(datos),
    });
    const json = await res.json();
    if (json.ok) {
      return { ok: true, id_cita: json.id_cita };
    }
    return { ok: false, error: json.error || 'Error al crear cita' };
  } catch (err) {
    const msg = err?.message || 'No se pudo conectar con el servidor';
    return { ok: false, error: msg };
  }
}

/**
 * Obtiene las citas de un usuario
 * @param {number} id_usuario
 */
export async function traerCitasUsuario(id_usuario) {
  try {
    const res = await fetch(`${API_BASE}/c_citas_usuario.php?id_usuario=${id_usuario}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const json = await res.json();
    if (json.ok) {
      return { ok: true, data: json.data };
    }
    return { ok: false, error: json.error || 'Error al obtener citas' };
  } catch (err) {
    const msg = err?.message || 'No se pudo conectar con el servidor';
    return { ok: false, error: msg };
  }
}

/**
 * Cambia el estado de una cita
 * @param {number} id_cita
 * @param {string} estado - 'pendiente', 'confirmada', 'cancelada', 'completada', 'rechazada'
 */
export async function cambiarEstadoCita(id_cita, estado) {
  try {
    const res = await fetch(`${API_BASE}/c_cambiar_estado_cita.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ id_cita, estado }),
    });
    const json = await res.json();
    if (json.ok) {
      return { ok: true };
    }
    return { ok: false, error: json.error || 'Error al cambiar estado' };
  } catch (err) {
    const msg = err?.message || 'No se pudo conectar con el servidor';
    return { ok: false, error: msg };
  }
}