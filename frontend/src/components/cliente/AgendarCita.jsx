import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import './AgendarCita.css';

/**
 * Componente Agendar Cita para el rol de Cliente (Bloque 3).
 * Flujo de 3 pasos: Servicio, Profesional/Fecha, Estilo.
 */
export default function AgendarCita() {
  const navigate = useNavigate();
  
  // Estado del flujo
  const [paso, setPaso] = useState(1);
  const [exito, setExito] = useState(false);

  // Estado del formulario
  const [servicioSeleccionado, setServicioSeleccionado] = useState(null);
  const [modalidad, setModalidad] = useState('local');
  const [profesionalSeleccionado, setProfesionalSeleccionado] = useState(null);
  const [diaSeleccionado, setDiaSeleccionado] = useState(null);
  const [horaSeleccionada, setHoraSeleccionada] = useState(null);
  const [estiloSeleccionado, setEstiloSeleccionado] = useState(null);

  // Datos hardcodeados
  const servicios = [
    { id: 'corte', nombre: 'Corte de cabello', duracion: '45 min', precio: 25000 },
    { id: 'barba', nombre: 'Corte y barba', duracion: '60 min', precio: 35000 },
    { id: 'tinte', nombre: 'Tinte', duracion: '90 min', precio: 55000 },
    { id: 'tratamiento', nombre: 'Tratamiento capilar', duracion: '60 min', precio: 45000 },
  ];

  const profesionales = [
    { id: 1, nombre: 'Carlos Mendez', especialidad: 'Cortes clásicos', rating: 4.8 },
    { id: 2, nombre: 'María García', especialidad: 'Tintes y tratamientos', rating: 4.9 },
    { id: 3, nombre: 'Juan Rodríguez', especialidad: 'Barbería tradicional', rating: 4.7 },
  ];

  const estilos = [
    { id: 'moderno', nombre: 'Moderno', emoji: '🕺' },
    { id: 'clasico', nombre: 'Clásico', emoji: '🎩' },
    { id: 'urbano', nombre: 'Urbano', emoji: '👔' },
    { id: 'creativo', nombre: 'Creativo', emoji: '🎨' },
  ];

  // Generar próximos 5 días
  const generarDias = () => {
    const dias = [];
    const hoy = new Date();
    for (let i = 0; i < 5; i++) {
      const fecha = new Date(hoy);
      fecha.setDate(fecha.getDate() + i);
      dias.push({
        fecha: fecha.toISOString().split('T')[0],
        día: fecha.toLocaleDateString('es-ES', { weekday: 'short' }),
        num: fecha.getDate(),
      });
    }
    return dias;
  };

  const dias = generarDias();

  // Horas disponibles hardcodeadas
  const horas = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

  // Funciones de navegación
  const handleContinuar = () => {
    if (paso === 1 && !servicioSeleccionado) {
      alert('Por favor selecciona un servicio');
      return;
    }
    if (paso === 2 && (!profesionalSeleccionado || !diaSeleccionado || !horaSeleccionada)) {
      alert('Por favor completa todos los campos');
      return;
    }
    if (paso === 3 && !estiloSeleccionado) {
      alert('Por favor selecciona un estilo');
      return;
    }

    if (paso < 3) {
      setPaso(paso + 1);
    } else {
      // Confirmar reserva
      setExito(true);
      setTimeout(() => {
        navigate('/citas');
      }, 1500);
    }
  };

  const handleAtras = () => {
    if (paso > 1) {
      setPaso(paso - 1);
    }
  };

  // Obtener datos seleccionados
  const servicioData = servicios.find(s => s.id === servicioSeleccionado);
  const profesionalData = profesionales.find(p => p.id === profesionalSeleccionado);
  const estiloData = estilos.find(e => e.id === estiloSeleccionado);

  // Pantalla de éxito
  if (exito) {
    return (
      <div className="agendar-container">
        <div className="success-screen">
          <div className="success-icon">✓</div>
          <h2>¡Cita agendada!</h2>
          <p>Tu reserva ha sido confirmada exitosamente.</p>
          <div className="success-details">
            <p><strong>Servicio:</strong> {servicioData?.nombre}</p>
            <p><strong>Profesional:</strong> {profesionalData?.nombre}</p>
            <p><strong>Precio:</strong> ${servicioData?.precio?.toLocaleString('es-CO')}</p>
          </div>
          <p className="redirect-msg">Redirigiendo a mis citas...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="agendar-container">
      <div className="agendar-header">
        <button onClick={() => navigate('/home')} className="back-header-btn">
          ← Volver
        </button>
        <h1>Agendar Cita</h1>
        <div style={{ width: '40px' }} />
      </div>

      {/* Stepper */}
      <div className="stepper">
        <div className="stepper-container">
          {[1, 2, 3].map((num) => (
            <div key={num} className="stepper-step">
              <div className={`stepper-circle ${paso >= num ? 'active' : ''} ${paso === num ? 'current' : ''}`}>
                {paso > num ? '✓' : num}
              </div>
              {num < 3 && (
                <div className={`stepper-line ${paso > num ? 'active' : ''}`} />
              )}
            </div>
          ))}
        </div>
        <div className="stepper-labels">
          <span>Servicio</span>
          <span>Profesional</span>
          <span>Confirmación</span>
        </div>
      </div>

      {/* Contenido del paso */}
      <div className="agendar-content">
        {/* PASO 1: Servicio */}
        {paso === 1 && (
          <div className="paso-container">
            <h2>Selecciona un servicio</h2>
            
            <div className="servicios-list">
              {servicios.map((servicio) => (
                <div
                  key={servicio.id}
                  className={`servicio-card ${servicioSeleccionado === servicio.id ? 'selected' : ''}`}
                  onClick={() => setServicioSeleccionado(servicio.id)}
                >
                  <div className="radio-circle">
                    {servicioSeleccionado === servicio.id && <div className="radio-inner" />}
                  </div>
                  <div className="servicio-info">
                    <h4>{servicio.nombre}</h4>
                    <p className="duracion">{servicio.duracion}</p>
                  </div>
                  <div className="servicio-precio">
                    ${servicio.precio.toLocaleString('es-CO')}
                  </div>
                </div>
              ))}
            </div>

            {/* Modalidad */}
            <div className="modalidad-section">
              <h3>¿Dónde deseas el servicio?</h3>
              <div className="modalidad-buttons">
                <button
                  className={`modalidad-btn ${modalidad === 'local' ? 'active' : ''}`}
                  onClick={() => setModalidad('local')}
                >
                  📍 En el salón
                </button>
                <button
                  className={`modalidad-btn ${modalidad === 'domicilio' ? 'active' : ''}`}
                  onClick={() => setModalidad('domicilio')}
                >
                  🏠 A domicilio
                </button>
              </div>
            </div>
          </div>
        )}

        {/* PASO 2: Profesional y Fecha */}
        {paso === 2 && (
          <div className="paso-container">
            <h2>Elige profesional y horario</h2>

            {/* Profesionales */}
            <div className="profesionales-section">
              <h3>Profesionales disponibles</h3>
              <div className="profesionales-list">
                {profesionales.map((prof) => (
                  <div
                    key={prof.id}
                    className={`profesional-card ${profesionalSeleccionado === prof.id ? 'selected' : ''}`}
                    onClick={() => setProfesionalSeleccionado(prof.id)}
                  >
                    <div className="radio-circle">
                      {profesionalSeleccionado === prof.id && <div className="radio-inner" />}
                    </div>
                    <div className="prof-info">
                      <h4>{prof.nombre}</h4>
                      <p>{prof.especialidad}</p>
                      <span className="prof-rating">⭐ {prof.rating}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Días */}
            <div className="calendario-section">
              <h3>Selecciona un día</h3>
              <div className="dias-grid">
                {dias.map((dia) => (
                  <button
                    key={dia.fecha}
                    className={`dia-btn ${diaSeleccionado === dia.fecha ? 'active' : ''}`}
                    onClick={() => setDiaSeleccionado(dia.fecha)}
                  >
                    <div className="dia-label">{dia.día.toUpperCase()}</div>
                    <div className="dia-num">{dia.num}</div>
                  </button>
                ))}
              </div>
            </div>

            {/* Horas */}
            {diaSeleccionado && (
              <div className="horas-section">
                <h3>Elige una hora</h3>
                <div className="horas-grid">
                  {horas.map((hora) => (
                    <button
                      key={hora}
                      className={`hora-btn ${horaSeleccionada === hora ? 'active' : ''}`}
                      onClick={() => setHoraSeleccionada(hora)}
                    >
                      {hora}
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}

        {/* PASO 3: Estilo y Confirmación */}
        {paso === 3 && (
          <div className="paso-container">
            <h2>Elige tu estilo y confirma</h2>

            {/* Estilos */}
            <div className="estilos-section">
              <h3>Referencia de estilo</h3>
              <div className="estilos-grid">
                {estilos.map((estilo) => (
                  <div
                    key={estilo.id}
                    className={`estilo-card ${estiloSeleccionado === estilo.id ? 'selected' : ''}`}
                    onClick={() => setEstiloSeleccionado(estilo.id)}
                  >
                    <div className="estilo-emoji">{estilo.emoji}</div>
                    <div className="estilo-nombre">{estilo.nombre}</div>
                  </div>
                ))}
              </div>
            </div>

            {/* Resumen */}
            <div className="resumen-section">
              <h3>Resumen de tu cita</h3>
              <div className="resumen-box">
                <div className="resumen-item">
                  <span className="label">Servicio:</span>
                  <span className="value">{servicioData?.nombre}</span>
                </div>
                <div className="resumen-item">
                  <span className="label">Profesional:</span>
                  <span className="value">{profesionalData?.nombre}</span>
                </div>
                <div className="resumen-item">
                  <span className="label">Día:</span>
                  <span className="value">
                    {diaSeleccionado ? new Date(diaSeleccionado).toLocaleDateString('es-CO') : '-'}
                  </span>
                </div>
                <div className="resumen-item">
                  <span className="label">Hora:</span>
                  <span className="value">{horaSeleccionada || '-'}</span>
                </div>
                <div className="resumen-item">
                  <span className="label">Modalidad:</span>
                  <span className="value">{modalidad === 'local' ? 'En el salón' : 'A domicilio'}</span>
                </div>
                <div className="resumen-item">
                  <span className="label">Estilo:</span>
                  <span className="value">{estiloData?.nombre}</span>
                </div>
                <div className="resumen-divider" />
                <div className="resumen-item resumen-total">
                  <span className="label">Total:</span>
                  <span className="value">${servicioData?.precio?.toLocaleString('es-CO')}</span>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Botones de navegación */}
      <div className="agendar-footer">
        <button
          className="btn-atras"
          onClick={handleAtras}
          disabled={paso === 1}
        >
          ← Atrás
        </button>
        <button
          className="btn-continuar"
          onClick={handleContinuar}
        >
          {paso === 3 ? 'Confirmar reserva ✓' : 'Continuar →'}
        </button>
      </div>
    </div>
  );
}
