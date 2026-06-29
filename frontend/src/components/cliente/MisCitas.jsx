import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './MisCitas.css';

/**
 * Componente Mis Citas para el rol de Cliente (Bloque 4).
 * Muestra citas próximas e historial con opción de calificación.
 */
export default function MisCitas() {
  const navigate = useNavigate();

  // Estados
  const [tabActivo, setTabActivo] = useState('proximas');
  const [modalAbierto, setModalAbierto] = useState(false);
  const [puntuacionSeleccionada, setPuntuacionSeleccionada] = useState(0);
  const [comentario, setComentario] = useState('');
  const [citaSeleccionada, setCitaSeleccionada] = useState(null);

  // Datos hardcodeados - Citas Próximas
  const citasProximas = [
    {
      id: 1,
      servicio: 'Corte de cabello',
      profesional: 'Carlos Mendez',
      fecha: '2026-07-05',
      hora: '10:00',
      precio: 25000,
      estado: 'Confirmada',
      modalidad: 'En el salón',
    },
    {
      id: 2,
      servicio: 'Corte y barba',
      profesional: 'Juan Rodríguez',
      fecha: '2026-07-08',
      hora: '14:30',
      precio: 35000,
      estado: 'Pendiente',
      modalidad: 'En el salón',
    },
    {
      id: 3,
      servicio: 'Tinte',
      profesional: 'María García',
      fecha: '2026-07-12',
      hora: '15:00',
      precio: 55000,
      estado: 'Confirmada',
      modalidad: 'A domicilio',
    },
  ];

  // Datos hardcodeados - Historial de Citas
  const citasHistorial = [
    {
      id: 101,
      servicio: 'Corte de cabello',
      profesional: 'Carlos Mendez',
      fecha: '2026-06-28',
      hora: '11:00',
      precio: 25000,
      calificado: false,
      rating: 0,
    },
    {
      id: 102,
      servicio: 'Tratamiento capilar',
      profesional: 'María García',
      fecha: '2026-06-20',
      hora: '09:30',
      precio: 45000,
      calificado: true,
      rating: 5,
      comentario: 'Excelente servicio, muy profesional',
    },
    {
      id: 103,
      servicio: 'Corte y barba',
      profesional: 'Juan Rodríguez',
      fecha: '2026-06-15',
      hora: '16:00',
      precio: 35000,
      calificado: true,
      rating: 4,
      comentario: 'Buen trabajo, rápido y profesional',
    },
  ];

  // Funciones
  const handleCancelarCita = (id) => {
    if (confirm('¿Estás seguro de que deseas cancelar esta cita?')) {
      alert('Cita cancelada exitosamente');
    }
  };

  const handleAbrirModalCalificacion = (cita) => {
    setCitaSeleccionada(cita);
    setPuntuacionSeleccionada(cita.rating || 0);
    setComentario(cita.comentario || '');
    setModalAbierto(true);
  };

  const handleCerrarModal = () => {
    setModalAbierto(false);
    setCitaSeleccionada(null);
    setPuntuacionSeleccionada(0);
    setComentario('');
  };

  const handleEnviarCalificacion = () => {
    if (puntuacionSeleccionada === 0) {
      alert('Por favor selecciona una puntuación');
      return;
    }
    alert(`Calificación de ${puntuacionSeleccionada} estrellas guardada${comentario ? ' con comentario' : ''}`);
    handleCerrarModal();
  };

  const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-CO', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  const renderEstrellas = (puntuacion) => {
    return (
      <div className="estrellas-display">
        {[1, 2, 3, 4, 5].map((star) => (
          <span key={star} className={`estrella ${star <= puntuacion ? 'filled' : ''}`}>
            ★
          </span>
        ))}
      </div>
    );
  };

  return (
    <div className="mis-citas-container">
      {/* Header */}
      <div className="mis-citas-header">
        <h1>Mis Citas</h1>
        <button
          type="button"
          className="btn-nueva-cita"
          onClick={() => navigate('/agendar')}
        >
          + Nueva cita
        </button>
      </div>

      {/* Tabs */}
      <div className="tabs-container">
        <button
          className={`tab-btn ${tabActivo === 'proximas' ? 'active' : ''}`}
          onClick={() => setTabActivo('proximas')}
        >
          Próximas
        </button>
        <button
          className={`tab-btn ${tabActivo === 'historial' ? 'active' : ''}`}
          onClick={() => setTabActivo('historial')}
        >
          Historial
        </button>
      </div>

      {/* Contenido */}
      <div className="mis-citas-content">
        {/* Tab Próximas */}
        {tabActivo === 'proximas' && (
          <div className="tab-content">
            {citasProximas.length === 0 ? (
              <div className="empty-state">
                <p>No tienes citas próximas</p>
                <button onClick={() => navigate('/agendar')} className="btn-agendar">
                  Agendar una cita
                </button>
              </div>
            ) : (
              <div className="citas-list">
                {citasProximas.map((cita) => (
                  <div key={cita.id} className="cita-card">
                    <div className="cita-header">
                      <h3>{cita.servicio}</h3>
                      <span className={`badge badge-${cita.estado.toLowerCase()}`}>
                        {cita.estado}
                      </span>
                    </div>

                    <div className="cita-info">
                      <div className="info-row">
                        <span className="label">👤 Profesional:</span>
                        <span className="value">{cita.profesional}</span>
                      </div>
                      <div className="info-row">
                        <span className="label">📅 Fecha:</span>
                        <span className="value">{formatearFecha(cita.fecha)}</span>
                      </div>
                      <div className="info-row">
                        <span className="label">🕐 Hora:</span>
                        <span className="value">{cita.hora}</span>
                      </div>
                      <div className="info-row">
                        <span className="label">📍 Modalidad:</span>
                        <span className="value">{cita.modalidad}</span>
                      </div>
                      <div className="info-row">
                        <span className="label">💰 Precio:</span>
                        <span className="value price">
                          ${cita.precio.toLocaleString('es-CO')}
                        </span>
                      </div>
                    </div>

                    <button
                      className="btn-cancelar"
                      onClick={() => handleCancelarCita(cita.id)}
                    >
                      ✕ Cancelar cita
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* Tab Historial */}
        {tabActivo === 'historial' && (
          <div className="tab-content">
            {citasHistorial.length === 0 ? (
              <div className="empty-state">
                <p>No tienes historial de citas</p>
              </div>
            ) : (
              <div className="citas-list">
                {citasHistorial.map((cita) => (
                  <div key={cita.id} className="cita-card historial">
                    <div className="cita-header">
                      <h3>{cita.servicio}</h3>
                      {cita.calificado && renderEstrellas(cita.rating)}
                    </div>

                    <div className="cita-info">
                      <div className="info-row">
                        <span className="label">👤 Profesional:</span>
                        <span className="value">{cita.profesional}</span>
                      </div>
                      <div className="info-row">
                        <span className="label">📅 Fecha:</span>
                        <span className="value">{formatearFecha(cita.fecha)}</span>
                      </div>
                      <div className="info-row">
                        <span className="label">💰 Precio:</span>
                        <span className="value price">
                          ${cita.precio.toLocaleString('es-CO')}
                        </span>
                      </div>
                      {cita.calificado && cita.comentario && (
                        <div className="info-row">
                          <span className="label">💬 Comentario:</span>
                          <span className="value">{cita.comentario}</span>
                        </div>
                      )}
                    </div>

                    {!cita.calificado && (
                      <button
                        className="btn-calificar"
                        onClick={() => handleAbrirModalCalificacion(cita)}
                      >
                        ⭐ Calificar servicio
                      </button>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>

      {/* Modal de Calificación */}
      {modalAbierto && (
        <div className="modal-overlay" onClick={handleCerrarModal}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Calificar servicio</h2>
              <button
                className="modal-close-btn"
                onClick={handleCerrarModal}
                aria-label="Cerrar modal"
              >
                ✕
              </button>
            </div>

            {citaSeleccionada && (
              <div className="modal-body">
                <p className="cita-titulo">{citaSeleccionada.servicio}</p>
                <p className="cita-prof">{citaSeleccionada.profesional}</p>

                {/* Estrellas interactivas */}
                <div className="estrellas-selector">
                  <label>¿Cómo fue tu experiencia?</label>
                  <div className="estrellas-input">
                    {[1, 2, 3, 4, 5].map((star) => (
                      <button
                        key={star}
                        type="button"
                        className={`estrella-btn ${star <= puntuacionSeleccionada ? 'selected' : ''}`}
                        onClick={() => setPuntuacionSeleccionada(star)}
                        aria-label={`${star} estrellas`}
                      >
                        ★
                      </button>
                    ))}
                  </div>
                </div>

                {/* Comentario */}
                <div className="comentario-section">
                  <label htmlFor="comentario">Comentario (opcional)</label>
                  <textarea
                    id="comentario"
                    className="comentario-input"
                    placeholder="Cuéntanos tu experiencia..."
                    value={comentario}
                    onChange={(e) => setComentario(e.target.value)}
                    rows="4"
                  />
                </div>

                {/* Botones */}
                <div className="modal-buttons">
                  <button
                    className="btn-cancelar-modal"
                    onClick={handleCerrarModal}
                  >
                    Cancelar
                  </button>
                  <button
                    className="btn-enviar-calificacion"
                    onClick={handleEnviarCalificacion}
                  >
                    Enviar calificación ✓
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
