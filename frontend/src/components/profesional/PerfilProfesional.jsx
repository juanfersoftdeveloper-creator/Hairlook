import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { traerCalificacionesProfesional } from '../../services/calificacionesService';
import { traerProfesionales } from '../../services/citasService';
import './PerfilProfesional.css';

/**
 * Componente Perfil Profesional (Bloque 6).
 */
export default function PerfilProfesional() {
  const navigate = useNavigate();
  const params = useParams();
  const { id: profesionalIdFromAuth, logout, user } = useAuth();
  const [subTab, setSubTab] = useState('info');
  const [calificaciones, setCalificaciones] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [profData, setProfData] = useState(null);

  // Determinar si se visita el perfil usando la ruta con id (cliente) o el propio profesional autenticado
  const viewingId = params.id ?? profesionalIdFromAuth;
  const isViewingAsClient = Boolean(params.id && String(params.id) !== String(profesionalIdFromAuth));

  // Cargar datos del profesional y calificaciones
  useEffect(() => {
    const cargar = async () => {
      if (!viewingId) return;

      // Si se pide por URL, obtener el profesional desde la lista (backend no tiene endpoint single)
      if (params.id) {
        const resP = await traerProfesionales();
        if (resP.ok) {
          const found = (resP.data || []).find((p) => String(p.id) === String(params.id));
          if (found) {
            setProfData({
              nombre: found.nombre || found.Nombre || 'Profesional',
              especialidad: found.especialidad || found.Especialidad || '',
              avatar: '💈',
              rating: found.rating ?? 0,
              servicios: 0,
              bio: '',
              especialidades: [],
              certificaciones: [],
            });
          } else {
            setProfData(null);
          }
        }
      } else {
        // Vista del propio profesional: usar datos del usuario del contexto
        setProfData({
          nombre: user?.nombre || user?.Nombre || 'Profesional',
          especialidad: user?.especialidad || user?.Especialidad || '',
          avatar: '💈',
          rating: user?.rating ?? 0,
          servicios: 0,
          bio: '',
          especialidades: [],
          certificaciones: [],
        });
      }

      // Calificaciones
      setCargando(true);
      const res = await traerCalificacionesProfesional(viewingId);
      if (res.ok) setCalificaciones(res.data || []);
      setCargando(false);
    };

    cargar();
  }, [params.id, profesionalIdFromAuth, user]);

  // Calcular distribución de calificaciones
  const calcularRatingDistribution = () => {
    const dist = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    calificaciones.forEach((cal) => {
      if (cal.Puntuacion) {
        dist[cal.Puntuacion]++;
      }
    });
    return dist;
  };

  const ratingDistribution = calcularRatingDistribution();

  const handleLogout = () => {
    if (confirm('¿Deseas cerrar sesión?')) {
      logout();
      navigate('/');
    }
  };

  return (
    <div className="perfil-profesional-container">
      {/* Header */}
      <div className="perfil-header">
        <button onClick={() => (isViewingAsClient ? navigate(-1) : navigate('/pro/inicio'))} className="back-btn">
          ← Volver
        </button>
        <h1>Mi Perfil</h1>
      </div>

      {/* Banner con Avatar */}
      <div className="perfil-banner">
        <div className="avatar-grande">{profData?.avatar ?? '💈'}</div>
        <h2>{profData?.nombre ?? 'Profesional'}</h2>
        <p>{profData?.especialidad}</p>
        <div className="perfil-stats">
          <span>⭐ {profData?.rating ?? '—'}</span>
          <span>📝 {calificaciones.length} reviews</span>
          <span>✓ {profData?.servicios ?? 0} servicios</span>
        </div>
      </div>

      {/* Sub Tabs */}
      <div className="sub-tabs">
        <button
          className={`sub-tab-btn ${subTab === 'info' ? 'active' : ''}`}
          onClick={() => setSubTab('info')}
        >
          Información
        </button>
        <button
          className={`sub-tab-btn ${subTab === 'reviews' ? 'active' : ''}`}
          onClick={() => setSubTab('reviews')}
        >
          Reviews
        </button>
        <button
          className={`sub-tab-btn ${subTab === 'cuenta' ? 'active' : ''}`}
          onClick={() => setSubTab('cuenta')}
        >
          Cuenta
        </button>
      </div>

      {/* Contenido */}
      <div className="perfil-content">
        {/* Información */}
        {subTab === 'info' && (
          <div className="info-section">
            <div className="info-card">
              <h3>Biografía</h3>
              <p>{profesional.bio}</p>
            </div>

            <div className="info-card">
              <h3>Especialidades</h3>
              <ul className="especialidades-list">
                {profesional.especialidades.map((esp, idx) => (
                  <li key={idx}>✓ {esp}</li>
                ))}
              </ul>
            </div>

            <div className="info-card">
              <h3>Certificaciones</h3>
              <ul className="certificaciones-list">
                {profesional.certificaciones.map((cert, idx) => (
                  <li key={idx}>🏆 {cert}</li>
                ))}
              </ul>
            </div>
          </div>
        )}

        {/* Reviews */}
        {subTab === 'reviews' && (
          <div className="reviews-section">
            <div className="rating-summary">
              <h3>Resumen de calificaciones</h3>
              <div className="rating-bars">
                {[5, 4, 3, 2, 1].map((star) => (
                  <div key={star} className="rating-bar-item">
                    <span className="stars">{star}⭐</span>
                    <div className="bar">
                      <div
                        className="bar-fill"
                        style={{ width: `${(ratingDistribution[star] / 100) * 100}%` }}
                      />
                    </div>
                    <span className="count">{ratingDistribution[star]}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="reviews-list">
              <h3>Comentarios recientes</h3>
              {calificaciones.length === 0 ? (
                <p>No hay calificaciones aún</p>
              ) : (
                calificaciones.slice(0, 5).map((cal, idx) => (
                  <div key={idx} className="review-card">
                    <div className="review-header">
                      <h4>{cal.NombreUsuario || 'Cliente anónimo'}</h4>
                      <span className="review-stars">{'⭐'.repeat(cal.Puntuacion)}</span>
                    </div>
                    <p>{cal.Comentario || 'Sin comentario'}</p>
                  </div>
                ))
              )}
            </div>
          </div>
        )}

        {/* Cuenta */}
        {subTab === 'cuenta' && (
          <div className="cuenta-section">
            <div className="cuenta-menu">
              <button className="cuenta-item">
                <span>📧 Cambiar correo</span>
                <span>→</span>
              </button>
              <button className="cuenta-item">
                <span>🔒 Cambiar contraseña</span>
                <span>→</span>
              </button>
              <button className="cuenta-item">
                <span>🔔 Notificaciones</span>
                <span>→</span>
              </button>
              <button className="cuenta-item">
                <span>📋 Términos y privacidad</span>
                <span>→</span>
              </button>
              <button className="cuenta-item logout" onClick={handleLogout}>
                <span>🚪 Cerrar sesión</span>
                <span>→</span>
              </button>
            </div>
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
        <button className="nav-btn-pro active" onClick={() => navigate('/pro/perfil')}>
          <span>Perfil</span>
        </button>
      </nav>
    </div>
  );
}
