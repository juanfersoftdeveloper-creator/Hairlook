import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import './HomeProfesional.css';

/**
 * Componente Home para el rol de Profesional (Bloque 5).
 */
export default function HomeProfesional() {
  const { user } = useAuth();
  const navigate = useNavigate();

  // Datos hardcodeados
  const stats = {
    ingresosDelMes: 2850000,
    rating: 4.8,
    serviciosCompletados: 47,
  };

  const citasHoy = [
    { id: 1, cliente: 'Pedro López', servicio: 'Corte de cabello', hora: '10:00', estado: 'nueva' },
    { id: 2, cliente: 'Ana García', servicio: 'Tinte', hora: '14:00', estado: 'confirmada' },
  ];

  const citasPendientes = 2;

  return (
    <div className="profesional-home-container">
      <div className="home-pad">
        {/* Hero Card con Stats */}
        <div className="hero-card">
          <div className="hero-header">
            <h2>Bienvenido, Carlos 👋</h2>
            <button
              className="bell-btn"
              onClick={() => navigate('/pro/notificaciones')}
              aria-label="Notificaciones"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
              </svg>
              <span className="notif-badge">{citasPendientes}</span>
            </button>
          </div>

          <div className="stats-grid">
            <div className="stat-item">
              <span className="stat-label">Ingresos del mes</span>
              <span className="stat-value">${(stats.ingresosDelMes / 1000).toFixed(0)}K</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Rating</span>
              <span className="stat-value">⭐ {stats.rating}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Servicios</span>
              <span className="stat-value">{stats.serviciosCompletados}</span>
            </div>
          </div>
        </div>

        {/* Alerta de Citas Pendientes */}
        {citasPendientes > 0 && (
          <div className="alert-banner">
            <span className="alert-icon">⚠️</span>
            <div className="alert-text">
              <h4>Tienes {citasPendientes} citas por aprobar</h4>
              <p>Revisa y confirma tus próximas citas</p>
            </div>
            <button
              onClick={() => navigate('/pro/citas')}
              className="alert-btn"
            >
              Ver →
            </button>
          </div>
        )}

        {/* Citas de Hoy */}
        <div className="citas-hoy-section">
          <h3>Citas de hoy</h3>
          <div className="citas-hoy-list">
            {citasHoy.map((cita) => (
              <div key={cita.id} className={`cita-hoy-card ${cita.estado}`}>
                <div className="cita-hoy-badge">{cita.estado === 'nueva' ? '🆕' : '✓'}</div>
                <div className="cita-hoy-info">
                  <h4>{cita.cliente}</h4>
                  <p>{cita.servicio}</p>
                </div>
                <span className="cita-hoy-hora">{cita.hora}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Accesos Rápidos */}
        <div className="quick-grid">
          <div className="quick-card" onClick={() => navigate('/pro/agenda')}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" strokeWidth="2">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <h4>Ver agenda</h4>
          </div>

          <div className="quick-card" onClick={() => navigate('/pro/perfil')}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" strokeWidth="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <h4>Mi perfil</h4>
          </div>

          <div className="quick-card" onClick={() => navigate('/pro/citas')}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" strokeWidth="2">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <h4>Mis citas</h4>
          </div>

          <div className="quick-card" onClick={() => navigate('/pro/notificaciones')}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" strokeWidth="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <h4>Notificaciones</h4>
          </div>
        </div>
      </div>

      {/* Bottom Navigation */}
      <nav className="bottom-nav-pro">
        <button
          className="nav-btn-pro active"
          onClick={() => navigate('/pro/inicio')}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg>
          <span>Inicio</span>
        </button>

        <button
          className="nav-btn-pro"
          onClick={() => navigate('/pro/citas')}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          <span>Citas</span>
        </button>

        <button
          className="nav-btn-pro"
          onClick={() => navigate('/pro/agenda')}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M19 3H5c-1 0-2 1-2 2v14c0 1 1 2 2 2h14c1 0 2-1 2-2V5c0-1-1-2-2-2z" />
            <line x1="16" y1="1" x2="16" y2="7" />
            <line x1="8" y1="1" x2="8" y2="7" />
            <line x1="3" y1="12" x2="21" y2="12" />
          </svg>
          <span>Agenda</span>
        </button>

        <button
          className="nav-btn-pro"
          onClick={() => navigate('/pro/perfil')}
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          <span>Perfil</span>
        </button>
      </nav>
    </div>
  );
}
