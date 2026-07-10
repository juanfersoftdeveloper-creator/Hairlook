import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import ClienteHome from './cliente/Home';
import HomeProfesional from './profesional/HomeProfesional';
import './Home.css';

/**
 * Pantalla de inicio que redirecciona según el tipo de usuario (Cliente o Profesional).
 */
export default function Home() {
  const { user, userType, logout, isLoading } = useAuth();
  const navigate = useNavigate();

  if (isLoading) {
    return (
      <div className="home-page">
        <p>Cargando...</p>
      </div>
    );
  }

  function handleLogout() {
    logout();
    navigate('/');
  }

  if (userType === 'cliente') {
    return <ClienteHome />;
  }

  if (userType === 'profesional') {
    return <HomeProfesional />;
  }

  // Si no hay userType, mostrar interfaz neutral
  return (
    <div className="home-page">
      <span className="home-badge">
        HairLook
      </span>
      <h1>Bienvenido, {user?.nombre || 'Usuario'}</h1>
      <p>Sesión iniciada correctamente.</p>
      <button type="button" className="home-btn" onClick={handleLogout}>
        Cerrar sesión
      </button>
    </div>
  );
}

