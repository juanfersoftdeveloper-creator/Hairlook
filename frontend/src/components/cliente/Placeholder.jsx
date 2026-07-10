import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import './Home.css'; // Reuses bottom-nav styles

/**
 * Componente temporal para las secciones en desarrollo del Cliente.
 * @param {{ title: string }} props
 */
export default function ClientPlaceholder({ title }) {
  const navigate = useNavigate();
  const { logout } = useAuth();

  const handleLogout = () => {
    if (confirm('¿Deseas cerrar sesión?')) {
      logout();
      navigate('/');
    }
  };

  return (
    <div className="cliente-home-container" style={{ display: 'flex', flexDirection: 'column', height: '100svh' }}>
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '20px', textAlign: 'center' }}>
        <h2 style={{ color: 'var(--navy)', fontSize: '22px', fontWeight: 800, margin: '0 0 10px 0' }}>
          {title}
        </h2>
        <p style={{ color: 'var(--gray)', fontSize: '14px', margin: 0, maxWidth: '280px', lineHeight: 1.4 }}>
          Esta pantalla se implementará en los siguientes bloques de desarrollo.
        </p>

        {title === 'Mi Perfil' && (
          <div style={{ marginTop: 18 }}>
            <button className="btn-nueva-cita" onClick={() => navigate('/home')} style={{ marginRight: 8 }}>Volver a Home</button>
            <button className="btn-nueva-cita" onClick={handleLogout} style={{ backgroundColor: '#ef4444' }}>Cerrar sesión</button>
          </div>
        )}
      </div>

      {/* Bottom Navigation */}
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
