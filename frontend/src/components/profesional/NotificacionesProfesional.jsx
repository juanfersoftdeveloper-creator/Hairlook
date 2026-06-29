import { useNavigate } from 'react-router-dom';
import './NotificacionesProfesional.css';

/**
 * Componente Notificaciones Profesional (Bloque 7).
 */
export default function NotificacionesProfesional() {
  const navigate = useNavigate();

  // Datos hardcodeados
  const notificaciones = [
    {
      id: 1,
      tipo: 'nueva_cita',
      cliente: 'Juan Pérez',
      servicio: 'Corte de cabello',
      hora: '14:00',
      leida: false,
    },
    {
      id: 2,
      tipo: 'recordatorio',
      titulo: 'Recordatorio: Cita hoy a las 11:00',
      cliente: 'María García',
      leida: false,
    },
    {
      id: 3,
      tipo: 'confirmacion',
      titulo: 'Cita confirmada por Pedro López',
      hora: 'Mañana 15:30',
      leida: true,
    },
  ];

  const handleAceptarCita = (id) => {
    alert('Cita aceptada exitosamente');
  };

  const handleRechazarCita = (id) => {
    alert('Cita rechazada');
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
        {notificaciones.length === 0 ? (
          <div className="empty-notif">
            <p>No tienes notificaciones</p>
          </div>
        ) : (
          <div className="notif-list">
            {notificaciones.map((notif) => (
              <div
                key={notif.id}
                className={`notif-card ${notif.tipo} ${notif.leida ? 'leida' : 'no-leida'}`}
              >
                <div className="notif-dot" />

                <div className="notif-content-inner">
                  {/* Nueva Cita */}
                  {notif.tipo === 'nueva_cita' && (
                    <>
                      <div className="notif-titulo">
                        <span className="notif-badge nueva-cita">🆕 Nueva cita</span>
                        <span className="notif-hora">{notif.hora}</span>
                      </div>
                      <h4>{notif.cliente}</h4>
                      <p>{notif.servicio}</p>
                      <div className="notif-actions">
                        <button
                          className="btn-aceptar"
                          onClick={() => handleAceptarCita(notif.id)}
                        >
                          ✓ Aceptar
                        </button>
                        <button
                          className="btn-rechazar"
                          onClick={() => handleRechazarCita(notif.id)}
                        >
                          ✕ Rechazar
                        </button>
                      </div>
                    </>
                  )}

                  {/* Recordatorio */}
                  {notif.tipo === 'recordatorio' && (
                    <>
                      <div className="notif-titulo">
                        <span className="notif-badge recordatorio">⏰ Recordatorio</span>
                      </div>
                      <p>{notif.titulo}</p>
                      <p className="notif-cliente">{notif.cliente}</p>
                    </>
                  )}

                  {/* Confirmación */}
                  {notif.tipo === 'confirmacion' && (
                    <>
                      <div className="notif-titulo">
                        <span className="notif-badge confirmacion">✓ Confirmada</span>
                        <span className="notif-hora">{notif.hora}</span>
                      </div>
                      <p>{notif.titulo}</p>
                    </>
                  )}
                </div>
              </div>
            ))}
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
