import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import * as authService from '../../services/authService';
import './Login.css';

/** Ícono de tijeras para el logo Hairlook. */
function IconoTijeras() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="6" cy="6" r="3" />
      <circle cx="6" cy="18" r="3" />
      <line x1="20" y1="4" x2="8.12" y2="15.88" />
      <line x1="14.47" y1="14.48" x2="20" y2="20" />
      <line x1="8.12" y1="8.12" x2="12" y2="12" />
    </svg>
  );
}

/**
 * Pantalla de inicio de sesión con tabs Cliente / Profesional.
 */
export default function Login() {
  const [tabActivo, setTabActivo] = useState('cliente');
  const [correo, setCorreo] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const { login } = useAuth();
  const navigate = useNavigate();

  /**
   * Maneja el envío del formulario de login.
   * @param {import('react').FormEvent} e
   */
  async function handleSubmit(e) {
    e.preventDefault();
    setError('');
    setLoading(true);

    const resultado = await authService.login(correo, password, tabActivo);

    if (resultado.ok) {
      login(resultado.data, tabActivo);
      navigate('/home');
    } else {
      setError(resultado.error || 'Credenciales inválidas');
    }

    setLoading(false);
  }

  const rutaRegistro =
    tabActivo === 'cliente' ? '/registro-cliente' : '/registro-profesional';

  return (
    <div className="auth-page">
      <div className="auth-logo">
        <div className="auth-logo-icon">
          <IconoTijeras />
        </div>
        <h1>Hairlook</h1>
        <p>Agenda tu cita con estilo</p>
      </div>

      <div className="auth-tabs">
        <button
          type="button"
          className={`auth-tab${tabActivo === 'cliente' ? ' auth-tab--activo' : ''}`}
          onClick={() => { setTabActivo('cliente'); setError(''); }}
        >
          👤 Cliente
        </button>
        <button
          type="button"
          className={`auth-tab${tabActivo === 'profesional' ? ' auth-tab--activo' : ''}`}
          onClick={() => { setTabActivo('profesional'); setError(''); }}
        >
          ✂️ Profesional
        </button>
      </div>

      <form className="auth-form" onSubmit={handleSubmit}>
        <div className="auth-campo">
          <label htmlFor="correo">Correo electrónico</label>
          <input
            id="correo"
            type="email"
            placeholder="tu@correo.com"
            value={correo}
            onChange={(e) => setCorreo(e.target.value)}
            required
            autoComplete="email"
          />
        </div>

        <div className="auth-campo">
          <label htmlFor="password">Contraseña</label>
          <input
            id="password"
            type="password"
            placeholder="••••••••"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            autoComplete="current-password"
          />
        </div>

        <p className="auth-olvido">¿Olvidó su contraseña?</p>

        {error && <div className="auth-error">{error}</div>}

        <button type="submit" className="auth-btn" disabled={loading}>
          {loading ? 'Iniciando sesión…' : 'Iniciar sesión'}
        </button>
      </form>

      <p className="auth-enlace">
        ¿Nuevo usuario? <Link to={rutaRegistro}>Regístrate</Link>
      </p>
    </div>
  );
}
