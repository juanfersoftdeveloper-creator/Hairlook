/**
 * Servicio de autenticación — conectado al backend PHP.
 */

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost/Hairlook/backend/public';

/**
 * Inicia sesión con correo, contraseña y tipo de usuario.
 * @param {string} correo - Correo electrónico del usuario
 * @param {string} password - Contraseña
 * @param {'cliente'|'profesional'} tipo - Tipo de usuario
 * @returns {Promise<{ok: boolean, data?: object, error?: string}>}
 */
export async function login(correo, password, tipo) {
  try {
    const res = await fetch(`${API_BASE}/c_login.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ correo, password, tipo }),
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: json.data };
    }

    return { ok: false, error: json.error || 'Error al iniciar sesión' };
  } catch (error) {
    console.error('Error login:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}

/**
 * Registra un nuevo cliente.
 * @param {object} datos - Datos del formulario de registro
 * @returns {Promise<{ok: boolean, data?: object, error?: string}>}
 */
export async function registerCliente(datos) {
  try {
    const res = await fetch(`${API_BASE}/c_registro_usuario.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos),
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: datos };
    }

    return { ok: false, error: json.error || 'Error al registrar' };
  } catch {
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}

/**
 * Registra un nuevo profesional.
 * @param {object} datos - Datos del formulario de registro
 * @returns {Promise<{ok: boolean, data?: object, error?: string}>}
 */
export async function registerProfesional(datos) {
  try {
    const res = await fetch(`${API_BASE}/c_registro_profesional.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos),
    });

    const json = await res.json();

    if (json.ok) {
      return { ok: true, data: datos };
    }

    return { ok: false, error: json.error || 'Error al registrar' };
  } catch (error) {
    console.error('Error registerProfesional:', error);
    return { ok: false, error: 'No se pudo conectar con el servidor' };
  }
}
