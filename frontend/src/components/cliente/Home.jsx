import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import './Home.css';

/**
 * Componente Home para el rol de Cliente (Bloque 2).
 */
export default function ClienteHome() {
  const { user } = useAuth();
  const navigate = useNavigate();

  // Nombre de pila del usuario (o valor por defecto)
  const nombreUsuario = user?.nombre || 'Cliente';

  // Navegar a agendar cita
  function handleAgendarClick() {
    navigate('/agendar');
  }

  return (
    <div className="cliente-home-container">
      <div className="home-pad">
        {/* Header con Saludo y Notificaciones */}
        <div className="home-header-row">
          <div className="home-greeting">
            <h2>Hola, {nombreUsuario} 👋</h2>
            <p>¿Qué servicio necesitas hoy?</p>
          </div>
          <button
            type="button"
            className="bell-btn"
            onClick={() => navigate('/notificaciones')}
            aria-label="Ver notificaciones"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="var(--navy)"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span className="notif-badge">2</span>
          </button>
        </div>

        {/* Card Grande CTA - Agendar Cita */}
        <div className="cta-card">
          <div className="cta-bg-icon">
            <svg
              width="110"
              height="110"
              viewBox="0 0 24 24"
              fill="none"
              stroke="white"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
          </div>
          <h3>Agendar cita</h3>
          <p>Reserva tu próximo corte de cabello o servicio de barbería.</p>
          <button type="button" className="cta-btn" onClick={handleAgendarClick}>
            → Reservar ahora
          </button>
        </div>

        {/* Grid 2x2 de Accesos Rápidos */}
        <div className="quick-grid">
          <div className="quick-card" onClick={() => navigate('/cercanos')}>
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="var(--navy)"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <h4>Peluqueros</h4>
            <p>Cerca de ti</p>
          </div>

          <div className="quick-card" onClick={() => navigate('/citas')}>
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="var(--navy)"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <h4>Mis citas</h4>
            <p>Ver historial</p>
          </div>

          <div className="quick-card" onClick={() => navigate('/perfil')}>
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="var(--navy)"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <h4>Perfil</h4>
            <p>Gestionar datos</p>
          </div>

          <div className="quick-card" onClick={() => navigate('/notificaciones')}>
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="var(--navy)"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <h4>Notificaciones</h4>
            <p style={{ color: 'var(--red)', fontWeight: 700 }}>2 nuevas</p>
          </div>
        </div>

        {/* Card de Promoción / Oferta (Estático) */}
        <div className="promo-card" onClick={() => navigate('/ofertas')}>
          <div className="promo-icon">
            <svg
              width="26"
              height="26"
              viewBox="0 0 24 24"
              fill="none"
              stroke="white"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <circle cx="6" cy="6" r="3" />
              <circle cx="6" cy="18" r="3" />
              <line x1="20" y1="4" x2="8.12" y2="15.88" />
              <line x1="14.47" y1="14.48" x2="20" y2="20" />
              <line x1="8.12" y1="8.12" x2="12" y2="12" />
            </svg>
          </div>
          <div className="promo-info">
            <h4>Oferta de la semana</h4>
            <p>20% de descuento en cortes clásicos.</p>
          </div>
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#bbb"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <polyline points="9 18 15 12 9 6" />
          </svg>
        </div>
      </div>

      {/* Bottom Navigation con 5 Botones */}
      <nav className="bottom-nav">
        <NavLink
          to="/home"
          className={({ isActive }) => (isActive ? 'nav-btn active' : 'nav-btn')}
        >
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#bbb"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
          </svg>
          <span>Inicio</span>
        </NavLink>

        <NavLink
          to="/cercanos"
          className={({ isActive }) => (isActive ? 'nav-btn active' : 'nav-btn')}
        >
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#bbb"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
          <span>Cercanos</span>
        </NavLink>

        <NavLink
          to="/agendar"
          className={({ isActive }) => (isActive ? 'nav-btn active' : 'nav-btn')}
        >
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#bbb"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          <span>Agendar</span>
        </NavLink>

        <NavLink
          to="/citas"
          className={({ isActive }) => (isActive ? 'nav-btn active' : 'nav-btn')}
        >
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#bbb"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <polyline points="1 4 1 10 7 10" />
            <path d="M3.51 15a9 9 0 1 0 .49-4.5" />
          </svg>
          <span>Mis citas</span>
        </NavLink>

        <NavLink
          to="/perfil"
          className={({ isActive }) => (isActive ? 'nav-btn active' : 'nav-btn')}
        >
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#bbb"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          <span>Perfil</span>
        </NavLink>
      </nav>
    </div>
  );
}
