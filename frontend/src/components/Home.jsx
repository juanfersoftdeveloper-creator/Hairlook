import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import ClienteHome from './cliente/Home';
import './Home.css';

/**
 * Pantalla de inicio que redirecciona según el tipo de usuario (Cliente o Profesional).
 */
export default function Home() {
  const { user, userType, logout } = useAuth();
  const navigate = useNavigate();

  function handleLogout() {
    logout();
    navigate('/');
  }

  if (userType === 'cliente') {
    return <ClienteHome />;
  }

  return (
    <div className="home-page">
      <span className="home-badge">
        ✂️ Profesional
      </span>
      <h1>Bienvenido, {user?.nombre || 'Usuario'}</h1>
      <p>Sesión iniciada correctamente. Las pantallas de profesionales llegarán en la siguiente fase.</p>
      <button type="button" className="home-btn" onClick={handleLogout}>
        Cerrar sesión
      </button>
    </div>
  );
}

