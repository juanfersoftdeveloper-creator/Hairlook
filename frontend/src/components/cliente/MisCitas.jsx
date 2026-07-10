import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { traerCitasUsuario, cambiarEstadoCita } from '../../services/citasService';
import { enviarCalificacion } from '../../services/calificacionesService';
import './MisCitas.css';

/**
 * Componente Mis Citas para el rol de Cliente (Bloque 4).
 * Muestra citas próximas e historial con opción de calificación.
 */
export default function MisCitas() {
  const navigate = useNavigate();
  const { id: usuarioId } = useAuth();

  // Estados
  const [tabActivo, setTabActivo] = useState('proximas');
  const [modalAbierto, setModalAbierto] = useState(false);
  const [puntuacionSeleccionada, setPuntuacionSeleccionada] = useState(0);
  const [comentario, setComentario] = useState('');
  const [citaSeleccionada, setCitaSeleccionada] = useState(null);

  // UI: expanded card id for details toggle
  const [expandedId, setExpandedId] = useState(null);

  // Estados para datos del backend
  const [citasProximas, setCitasProximas] = useState([]);
  const [citasHistorial, setCitasHistorial] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);

  // Cargar citas al montar
  useEffect(() => {
    const cargarCitas = async () => {
      setCargando(true);
      setError(null);
      const res = await traerCitasUsuario(usuarioId);
      if (res.ok && Array.isArray(res.data)) {
        // Próximas: Estado que no sea completada ni cancelada
        const proximas = res.data.filter(c =>
          c.Estado && !['completada', 'cancelada'].includes(c.Estado.toLowerCase())
        );
        // Historial: Completadas o canceladas
        const historial = res.data.filter(c =>
          c.Estado && ['completada', 'cancelada'].includes(c.Estado.toLowerCase())
        );
        setCitasProximas(proximas);
        setCitasHistorial(historial);
      } else {
        setError(res.error || 'Error al cargar citas');
      }
      setCargando(false);
    };
    if (usuarioId) cargarCitas();
  }, [usuarioId]);

  // Funciones
  const handleCancelarCita = async (id) => {
    if (!confirm('¿Estás seguro de que deseas cancelar esta cita?')) return;
    const res = await cambiarEstadoCita(id, 'cancelada');
    if (res.ok) {
      setCitasProximas(prev => prev.filter(c => c.ID_Cita !== id));
      alert('Cita cancelada exitosamente');
    } else {
      alert(res.error || 'No se pudo cancelar la cita');
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

  const handleEnviarCalificacion = async () => {
    if (puntuacionSeleccionada === 0) {
      alert('Por favor selecciona una puntuación');
      return;
    }
    
    const res = await enviarCalificacion({
      id_cita: citaSeleccionada?.ID_Cita,
      id_usuario: usuarioId,
      id_profesional: citaSeleccionada?.ID_Profesional,
      puntuacion: puntuacionSeleccionada,
      comentario: comentario || null,
    });
    
    if (res.ok) {
      alert('Calificación enviada exitosamente');
      setCitasHistorial(prev =>
        prev.map(c =>
          c.ID_Cita === citaSeleccionada?.ID_Cita
            ? { ...c, calificado: true, rating: puntuacionSeleccionada, comentario }
            : c
        )
      );
      handleCerrarModal();
    } else {
      alert(res.error || 'Error al enviar calificación');
    }
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

  // Selected cita for the half-screen detail panel
  const selectedCita = expandedId
    ? (citasProximas.concat(citasHistorial)).find(c => c.ID_Cita === expandedId) || null
    : null;

  const [panelTab, setPanelTab] = useState('info');

  // Reset panel tab to 'info' whenever a new cita is opened
  useEffect(() => {
    if (expandedId) setPanelTab('info');
  }, [expandedId]);

  return (
    <div className="mis-citas-container">
      {/* Header */}
      <div className="mis-citas-header">
        <div className="header-left">
          <button className="btn-home-header" onClick={() => navigate('/home')} aria-label="Volver al home">🏠 Inicio</button>
          <h1>Mis Citas</h1>
        </div>
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
        {error && <div className="error-message">⚠️ {error}</div>}
        
        {cargando ? (
          <div className="loading-state">
            <p>Cargando citas...</p>
          </div>
        ) : (
          <>
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
                  <div key={cita.ID_Cita} className="cita-card">
                    <div className="cita-card-top">
                      <div className="cita-main">
                        <h3 className="cita-servicio">{cita.NombreServicio || 'Servicio'}</h3>
                        <div className="cita-meta">
                          <span className="precio">${parseFloat(cita.Precio).toLocaleString('es-CO')}</span>
                          <span className={`badge badge-${cita.Estado.toLowerCase()}`}>
                            {cita.Estado}
                          </span>
                        </div>
                      </div>
                      <div className="cita-sub-and-toggle">
                        <div className="cita-sub">👤 <strong>{cita.NombreProfesional}</strong>{cita.Especialidad ? ` · ${cita.Especialidad}` : ''}</div>
                        <button
                          type="button"
                          className={`toggle-btn ${expandedId === cita.ID_Cita ? 'open' : ''}`}
                          onClick={() => setExpandedId(expandedId === cita.ID_Cita ? null : cita.ID_Cita)}
                          aria-expanded={expandedId === cita.ID_Cita}
                        >
                          {expandedId === cita.ID_Cita ? 'Ocultar ▴' : 'Detalles ▾'}
                        </button>
                      </div>
                    </div>

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
                  <div key={cita.ID_Cita} className="cita-card historial">
                    <div className="cita-card-top">
                      <div className="cita-main">
                        <h3 className="cita-servicio">{cita.NombreServicio || 'Servicio'}</h3>
                        <div className="cita-meta">
                          <span className="precio">${parseFloat(cita.Precio).toLocaleString('es-CO')}</span>
                          <span className={`badge badge-${cita.Estado.toLowerCase()}`}>
                            {cita.Estado}
                          </span>
                        </div>
                      </div>
                      <div className="cita-sub-and-toggle">
                        <div className="cita-sub">👤 <strong>{cita.NombreProfesional}</strong></div>
                        <button
                          type="button"
                          className={`toggle-btn ${expandedId === cita.ID_Cita ? 'open' : ''}`}
                          onClick={() => setExpandedId(expandedId === cita.ID_Cita ? null : cita.ID_Cita)}
                        >
                          {expandedId === cita.ID_Cita ? 'Ocultar ▴' : 'Detalles ▾'}
                        </button>
                      </div>
                    </div>

                  </div>
                ))}
              </div>
            )}
          </div>
        )}
          </>
        )}
      </div>

      {/* Panel de detalles medio-pantalla */}
      {selectedCita && (
        <>
          <div className="detail-backdrop" onClick={() => setExpandedId(null)} />
          <aside className="detail-panel" role="dialog" aria-modal="true" onClick={(e) => e.stopPropagation()}>
            <div className="detail-header">
              <h2 className="detail-title">{selectedCita.NombreServicio || 'Servicio'}</h2>
              <div className="detail-actions-head">
                <button className="btn-home" onClick={() => navigate('/home')} aria-label="Volver al home">🏠 Home</button>
                <span className="detail-prof">👤 {selectedCita.NombreProfesional}</span>
                <button className="detail-close" onClick={() => setExpandedId(null)} aria-label="Cerrar detalles">✕</button>
              </div>
            </div>

            <div className="detail-body">
              {/* Tabs */}
              <div className="detail-tabs">
                <button className={`tab-btn ${panelTab === 'info' ? 'active' : ''}`} onClick={() => setPanelTab('info')}>Información</button>
                <button className={`tab-btn ${panelTab === 'services' ? 'active' : ''}`} onClick={() => setPanelTab('services')}>Servicios</button>
                <button className={`tab-btn ${panelTab === 'actions' ? 'active' : ''}`} onClick={() => setPanelTab('actions')}>Acciones</button>
              </div>

              {panelTab === 'info' && (
                <div className="detail-grid">
                  <div className="detail-item">
                    <div className="label">📅 Fecha</div>
                    <div className="value">{formatearFecha(selectedCita.Fecha)}</div>
                  </div>
                  <div className="detail-item">
                    <div className="label">🕐 Hora</div>
                    <div className="value">{selectedCita.Hora}</div>
                  </div>
                  <div className="detail-item">
                    <div className="label">💰 Precio</div>
                    <div className="value">${parseFloat(selectedCita.Precio).toLocaleString('es-CO')}</div>
                  </div>
                  <div className="detail-item">
                    <div className="label">🏷️ Estado</div>
                    <div className="value">{selectedCita.Estado}</div>
                  </div>
                </div>
              )}

              {panelTab === 'services' && (
                <div className="detail-services">
                  <h4>Servicios incluidos</h4>
                  <p className="service-list">{selectedCita.NombreServicio || '—'}</p>
                </div>
              )}

              {panelTab === 'actions' && (
                <div className="detail-actions-panel">
                  <div className="cita-actions">
                    <button className="btn-cancelar" onClick={() => { handleCancelarCita(selectedCita.ID_Cita); setExpandedId(null); }}>✕ Cancelar cita</button>
                    {(!selectedCita.calificado && selectedCita.Estado === 'completada') && (
                      <button className="btn-calificar" onClick={() => { handleAbrirModalCalificacion(selectedCita); setExpandedId(null); }}>⭐ Calificar</button>
                    )}
                  </div>
                </div>
              )}

            </div>
          </aside>
        </>
      )}

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
                <p className="cita-titulo">{citaSeleccionada.NombreServicio || 'Servicio'}</p>
                <p className="cita-prof">{citaSeleccionada.NombreProfesional}</p>

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
      {/* Bottom navigation (mobile-friendly) */}
      <nav className="bottom-nav" role="navigation" aria-label="Navegación inferior">
        <button className="nav-btn" onClick={() => navigate('/home')} aria-label="Inicio">🏠<span>Inicio</span></button>
        <button className="nav-btn active" onClick={() => navigate('/citas')} aria-label="Citas">📅<span>Citas</span></button>
        <button className="nav-btn" onClick={() => navigate('/cercanos')} aria-label="Cercanos">📍<span>Cercanos</span></button>
        <button className="nav-btn" onClick={() => navigate('/perfil')} aria-label="Perfil">👤<span>Perfil</span></button>
      </nav>

    </div>
  );
}
