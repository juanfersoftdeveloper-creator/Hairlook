import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as authService from '../../services/authService';
import './RegisterCliente.css';

const METODOS_PAGO = [
  { id: 'efectivo', label: '💵 Efectivo' },
  { id: 'tarjeta', label: '💳 Tarjeta' },
  { id: 'digital', label: '📱 Digital' },
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
 * Formulario de registro para clientes.
 */
export default function RegisterCliente() {
  const navigate = useNavigate();

  const [nombres, setNombres] = useState('');
  const [apellidos, setApellidos] = useState('');
  const [cedula, setCedula] = useState('');
  const [fechaNacimiento, setFechaNacimiento] = useState('');
  const [direccion, setDireccion] = useState('');
  const [correo, setCorreo] = useState('');
  const [password, setPassword] = useState('');
  const [confirmarPassword, setConfirmarPassword] = useState('');
  const [metodoPago, setMetodoPago] = useState('');
  const [error, setError] = useState('');
  const [exito, setExito] = useState('');
  const [loading, setLoading] = useState(false);

  /**
   * Valida los campos del formulario antes de enviar.
   * @returns {string|null} Mensaje de error o null si es válido
   */
  function validarFormulario() {
    if (
      !nombres.trim() ||
      !apellidos.trim() ||
      !cedula.trim() ||
      !fechaNacimiento ||
      !direccion.trim() ||
      !correo.trim() ||
      !password ||
      !confirmarPassword ||
      !metodoPago
    ) {
      return 'Todos los campos son obligatorios.';
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

    const datos = {
      nombres: nombres.trim(),
      apellidos: apellidos.trim(),
      cedula: cedula.trim(),
      fechaNacimiento,
      direccion: direccion.trim(),
      correo: correo.trim(),
      password,
      confirmarPassword,
      metodoPago,
    };

    const resultado = await authService.registerCliente(datos);

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
          <h2>Registro de Cliente</h2>
          <p>Crea tu cuenta para agendar citas</p>
        </div>
      </div>

      <form className="registro-form" onSubmit={handleSubmit}>
        <div className="registro-fila">
          <div className="registro-campo">
            <label htmlFor="nombres">Nombres</label>
            <input
              id="nombres"
              type="text"
              placeholder="Juan"
              value={nombres}
              onChange={(e) => setNombres(e.target.value)}
            />
          </div>
          <div className="registro-campo">
            <label htmlFor="apellidos">Apellidos</label>
            <input
              id="apellidos"
              type="text"
              placeholder="Pérez"
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
          <label htmlFor="fechaNacimiento">Fecha de nacimiento</label>
          <input
            id="fechaNacimiento"
            type="date"
            value={fechaNacimiento}
            onChange={(e) => setFechaNacimiento(e.target.value)}
          />
        </div>

        <div className="registro-campo">
          <label htmlFor="direccion">Dirección</label>
          <input
            id="direccion"
            type="text"
            placeholder="Calle 123, Ciudad"
            value={direccion}
            onChange={(e) => setDireccion(e.target.value)}
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

        <div className="registro-seccion">
          <label>Método de pago preferido</label>
          <div className="registro-chips">
            {METODOS_PAGO.map((metodo) => (
              <button
                key={metodo.id}
                type="button"
                className={`registro-chip${metodoPago === metodo.id ? ' registro-chip--activo' : ''}`}
                onClick={() => setMetodoPago(metodo.id)}
              >
                {metodo.label}
              </button>
            ))}
          </div>
        </div>

        {error && <div className="registro-error">{error}</div>}
        {exito && <div className="registro-exito">{exito}</div>}

        <button type="submit" className="registro-btn" disabled={loading}>
          {loading ? 'Registrando…' : 'Crear cuenta'}
        </button>
      </form>

      <p className="registro-enlace">
        ¿Ya tienes cuenta? <Link to="/">Inicia sesión</Link>
      </p>
    </div>
  );
}
