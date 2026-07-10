import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { traerNotificaciones, marcarNotificacionLeida } from '../../services/notificacionesService';
import { cambiarEstadoCita } from '../../services/citasService';
import './NotificacionesProfesional.css';

/**
 * Componente Notificaciones Profesional (Bloque 7).
 */
export default function NotificacionesProfesional() {
  const navigate = useNavigate();
  const { id: profesionalId } = useAuth();
  const [notificaciones, setNotificaciones] = useState([]);
  const [cargando, setCargando] = useState(true);

  // Cargar notificaciones al montar
  useEffect(() => {
    const cargarNotificaciones = async () => {
      if (!profesionalId) return;
      setCargando(true);
      const res = await traerNotificaciones(profesionalId);
      if (res.ok) {
        setNotificaciones(res.data || []);
      }
      setCargando(false);
    };
    cargarNotificaciones();
  }, [profesionalId]);

  const handleAceptarCita = async (notificacion) => {
    if (!notificacion.ID_Cita) return;
    const res = await cambiarEstadoCita(notificacion.ID_Cita, 'confirmada');
    if (res.ok) {
      await marcarNotificacionLeida(notificacion.ID_Notificacion);
      setNotificaciones(prev => prev.filter(n => n.ID_Notificacion !== notificacion.ID_Notificacion));
      alert('Cita aceptada exitosamente');
    } else {
      alert(res.error || 'Error al aceptar cita');
    }
  };

  const handleRechazarCita = async (notificacion) => {
    if (!notificacion.ID_Cita) return;
    const res = await cambiarEstadoCita(notificacion.ID_Cita, 'cancelada');
    if (res.ok) {
      await marcarNotificacionLeida(notificacion.ID_Notificacion);
      setNotificaciones(prev => prev.filter(n => n.ID_Notificacion !== notificacion.ID_Notificacion));
      alert('Cita rechazada');
    } else {
      alert(res.error || 'Error al rechazar cita');
    }
  };

  return (
    <div className="notificaciones-profesional-container">
      {/* Header */}
      <div className="notif-header">
        <button onClick={() => navigate('/pro/inicio')} className="back-btn">
          ← Volver
        </button>
        <h1>Notificaciones</h1>
      </div>

      {/* Lista de Notificaciones */}
      <div className="notif-content">
        {cargando ? (
          <div className="empty-notif"><p>Cargando notificaciones...</p></div>
        ) : notificaciones.length === 0 ? (
          <div className="empty-notif">
            <p>No tienes notificaciones</p>
          </div>
        ) : (
          <div className="notif-list">
            {notificaciones.map((notif) => {
              const tipoNotificacion = notif.Tipo || 'nueva_cita';
              const leida = notif.Leida ? 'leida' : 'no-leida';
              
              return (
                <div
                  key={notif.ID_Notificacion}
                  className={`notif-card ${tipoNotificacion} ${leida}`}
                >
                  <div className="notif-dot" />

                  <div className="notif-content-inner">
                    {tipoNotificacion === 'nueva_cita' && (
                      <>
                        <div className="notif-titulo">
                          <span className="notif-badge nueva-cita">🆕 Nueva cita</span>
                          <span className="notif-hora">{notif.Hora || 'Por confirmar'}</span>
                        </div>
                        <h4>{notif.NombreCliente || 'Cliente'}</h4>
                        <p>{notif.Servicio || 'Servicio sin especificar'}</p>
                        <div className="notif-actions">
                          <button
                            className="btn-aceptar"
                            onClick={() => handleAceptarCita(notif)}
                          >
                            ✓ Aceptar
                          </button>
                          <button
                            className="btn-rechazar"
                            onClick={() => handleRechazarCita(notif)}
                          >
                            ✕ Rechazar
                          </button>
                        </div>
                      </>
                    )}

                    {tipoNotificacion === 'recordatorio' && (
                      <>
                        <div className="notif-titulo">
                          <span className="notif-badge recordatorio">⏰ Recordatorio</span>
                        </div>
                        <p>{notif.Mensaje || 'Recordatorio de cita'}</p>
                        <p className="notif-cliente">{notif.NombreCliente || 'Cliente'}</p>
                      </>
                    )}

                    {tipoNotificacion === 'confirmacion' && (
                      <>
                        <div className="notif-titulo">
                          <span className="notif-badge confirmacion">✓ Confirmada</span>
                          <span className="notif-hora">{notif.Hora || 'Por confirmar'}</span>
                        </div>
                        <p>{notif.Mensaje || 'Cita confirmada'}</p>
                      </>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* Bottom Navigation */}
      <nav className="bottom-nav-pro">
        <button className="nav-btn-pro" onClick={() => navigate('/pro/inicio')}>
          <span>Inicio</span>
        </button>
        <button className="nav-btn-pro" onClick={() => navigate('/pro/citas')}>
          <span>Citas</span>
        </button>
        <button className="nav-btn-pro" onClick={() => navigate('/pro/agenda')}>
          <span>Agenda</span>
        </button>
        <button className="nav-btn-pro" onClick={() => navigate('/pro/perfil')}>
          <span>Perfil</span>
        </button>
      </nav>
    </div>
  );
}
