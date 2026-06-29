import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as authService from '../../services/authService';
import './RegisterProfesional.css';

const ESPECIALIDADES = [
  'Barbero',
  'Estilista',
  'Colorista',
  'Manicurista',
  'Maquillador',
  'Otro',
];

/**
 * Valida el formato de un correo electrónico.
 * @param {string} correo
 * @returns {boolean}
 */
function esCorreoValido(correo) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
}

/**
 * Formulario de registro para profesionales.
 */
export default function RegisterProfesional() {
  const navigate = useNavigate();

  const [nombres, setNombres] = useState('');
  const [apellidos, setApellidos] = useState('');
  const [cedula, setCedula] = useState('');
  const [correo, setCorreo] = useState('');
  const [password, setPassword] = useState('');
  const [confirmarPassword, setConfirmarPassword] = useState('');
  const [especialidad, setEspecialidad] = useState('');
  const [especialidadOtra, setEspecialidadOtra] = useState('');
  const [aniosExperiencia, setAniosExperiencia] = useState('');
  const [bio, setBio] = useState('');
  const [ubicacion, setUbicacion] = useState('');
  const [error, setError] = useState('');
  const [exito, setExito] = useState('');
  const [loading, setLoading] = useState(false);

  /**
   * Valida los campos del formulario antes de enviar.
   * @returns {string|null} Mensaje de error o null si es válido
   */
  function validarFormulario() {
    const especialidadFinal =
      especialidad === 'Otro' ? especialidadOtra.trim() : especialidad;

    if (
      !nombres.trim() ||
      !apellidos.trim() ||
      !cedula.trim() ||
      !correo.trim() ||
      !password ||
      !confirmarPassword ||
      !especialidadFinal ||
      !aniosExperiencia ||
      !ubicacion.trim()
    ) {
      return 'Todos los campos obligatorios deben estar completos.';
    }
    if (!esCorreoValido(correo)) {
      return 'El correo electrónico no tiene un formato válido.';
    }
    if (password.length < 4) {
      return 'La contraseña debe tener al menos 4 caracteres.';
    }
    if (password !== confirmarPassword) {
      return 'Las contraseñas no coinciden.';
    }
    if (Number(aniosExperiencia) < 0) {
      return 'Los años de experiencia no pueden ser negativos.';
    }
    return null;
  }

  /**
   * Maneja el envío del formulario de registro.
   * @param {import('react').FormEvent} e
   */
  async function handleSubmit(e) {
    e.preventDefault();
    setError('');
    setExito('');

    const errorValidacion = validarFormulario();
    if (errorValidacion) {
      setError(errorValidacion);
      return;
    }

    setLoading(true);

    const especialidadFinal =
      especialidad === 'Otro' ? especialidadOtra.trim() : especialidad;

    const datos = {
      nombres: nombres.trim(),
      apellidos: apellidos.trim(),
      cedula: cedula.trim(),
      correo: correo.trim(),
      password,
      especialidad: especialidadFinal,
      aniosExperiencia: Number(aniosExperiencia),
      bio: bio.trim(),
      ubicacion: ubicacion.trim(),
    };

    const resultado = await authService.registerProfesional(datos);

    if (resultado.ok) {
      setExito('¡Registro exitoso! Redirigiendo al inicio de sesión…');
      setTimeout(() => navigate('/'), 1500);
    } else {
      setError(resultado.error || 'Error al registrar. Intenta de nuevo.');
    }

    setLoading(false);
  }

  return (
    <div className="registro-page">
      <div className="registro-header">
        <Link to="/" className="registro-volver" aria-label="Volver">
          ←
        </Link>
        <div>
          <h2>Registro de Profesional</h2>
          <p>Únete como estilista o barbero</p>
        </div>
      </div>

      <form className="registro-form" onSubmit={handleSubmit}>
        <div className="registro-fila">
          <div className="registro-campo">
            <label htmlFor="nombres">Nombres</label>
            <input
              id="nombres"
              type="text"
              placeholder="María"
              value={nombres}
              onChange={(e) => setNombres(e.target.value)}
            />
          </div>
          <div className="registro-campo">
            <label htmlFor="apellidos">Apellidos</label>
            <input
              id="apellidos"
              type="text"
              placeholder="García"
              value={apellidos}
              onChange={(e) => setApellidos(e.target.value)}
            />
          </div>
        </div>

        <div className="registro-campo">
          <label htmlFor="cedula">Cédula</label>
          <input
            id="cedula"
            type="text"
            placeholder="1234567890"
            value={cedula}
            onChange={(e) => setCedula(e.target.value)}
          />
        </div>

        <div className="registro-campo">
          <label htmlFor="correo">Correo electrónico</label>
          <input
            id="correo"
            type="email"
            placeholder="tu@correo.com"
            value={correo}
            onChange={(e) => setCorreo(e.target.value)}
            autoComplete="email"
          />
        </div>

        <div className="registro-fila">
          <div className="registro-campo">
            <label htmlFor="password">Contraseña</label>
            <input
              id="password"
              type="password"
              placeholder="Mín. 4 caracteres"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="new-password"
            />
          </div>
          <div className="registro-campo">
            <label htmlFor="confirmarPassword">Confirmar contraseña</label>
            <input
              id="confirmarPassword"
              type="password"
              placeholder="Repite la contraseña"
              value={confirmarPassword}
              onChange={(e) => setConfirmarPassword(e.target.value)}
              autoComplete="new-password"
            />
          </div>
        </div>

        <div className="registro-campo">
          <label htmlFor="especialidad">Especialidad</label>
          <select
            id="especialidad"
            value={especialidad}
            onChange={(e) => setEspecialidad(e.target.value)}
          >
            <option value="">Selecciona una especialidad</option>
            {ESPECIALIDADES.map((esp) => (
              <option key={esp} value={esp}>
                {esp}
              </option>
            ))}
          </select>
        </div>

        {especialidad === 'Otro' && (
          <div className="registro-campo">
            <label htmlFor="especialidadOtra">Especifica tu especialidad</label>
            <input
              id="especialidadOtra"
              type="text"
              placeholder="Ej. Peluquero canino"
              value={especialidadOtra}
              onChange={(e) => setEspecialidadOtra(e.target.value)}
            />
          </div>
        )}

        <div className="registro-campo">
          <label htmlFor="aniosExperiencia">Años de experiencia</label>
          <input
            id="aniosExperiencia"
            type="number"
            min="0"
            placeholder="3"
            value={aniosExperiencia}
            onChange={(e) => setAniosExperiencia(e.target.value)}
          />
        </div>

        <div className="registro-campo">
          <label htmlFor="bio">Bio / Descripción corta (opcional)</label>
          <textarea
            id="bio"
            placeholder="Cuéntanos sobre tu experiencia y estilo…"
            value={bio}
            onChange={(e) => setBio(e.target.value)}
          />
        </div>

        <div className="registro-campo">
          <label htmlFor="ubicacion">Ubicación del salón</label>
          <input
            id="ubicacion"
            type="text"
            placeholder="Av. Principal 456, Ciudad"
            value={ubicacion}
            onChange={(e) => setUbicacion(e.target.value)}
          />
        </div>

        {error && <div className="registro-error">{error}</div>}
        {exito && <div className="registro-exito">{exito}</div>}

        <button type="submit" className="registro-btn" disabled={loading}>
          {loading ? 'Registrando…' : 'Crear cuenta profesional'}
        </button>
      </form>

      <p className="registro-enlace">
        ¿Ya tienes cuenta? <Link to="/">Inicia sesión</Link>
      </p>
    </div>
  );
}
