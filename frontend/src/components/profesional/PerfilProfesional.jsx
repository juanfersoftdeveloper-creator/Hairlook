import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './PerfilProfesional.css';

/**
 * Componente Perfil Profesional (Bloque 6).
 */
export default function PerfilProfesional() {
  const navigate = useNavigate();
  const [subTab, setSubTab] = useState('info');

  // Datos hardcodeados
  const profesional = {
    nombre: 'Carlos Mendez',
    especialidad: 'Cortes clásicos y modernos',
    avatar: '💈',
    rating: 4.8,
    reviews: 127,
    servicios: 47,
    bio: 'Barbero con 8 años de experiencia. Especialista en cortes clásicos, modernos y diseños personalizados. Atiendo con cuidado y profesionalismo.',
    especialidades: ['Cortes clásicos', 'Cortes modernos', 'Diseños a medida', 'Cuidado de barba'],
    certificaciones: ['Barbería profesional', 'Higiene y salud', 'Atención al cliente'],
  };

  const reviews = [
    { cliente: 'Juan P.', rating: 5, comentario: 'Excelente servicio, muy profesional' },
    { cliente: 'Pedro G.', rating: 4, comentario: 'Buen corte, rápido y amable' },
    { cliente: 'Luis M.', rating: 5, comentario: 'El mejor barbero de la ciudad' },
  ];

  const ratingDistribution = {
    5: 95,
    4: 20,
    3: 10,
    2: 2,
    1: 0,
  };

  const handleLogout = () => {
    if (confirm('¿Deseas cerrar sesión?')) {
      navigate('/');
    }
  };

  return (
    <div className="perfil-profesional-container">
      {/* Header */}
      <div className="perfil-header">
        <button onClick={() => navigate('/pro/inicio')} className="back-btn">
          ← Volver
        </button>
        <h1>Mi Perfil</h1>
      </div>

      {/* Banner con Avatar */}
      <div className="perfil-banner">
        <div className="avatar-grande">{profesional.avatar}</div>
        <h2>{profesional.nombre}</h2>
        <p>{profesional.especialidad}</p>
        <div className="perfil-stats">
          <span>⭐ {profesional.rating}</span>
          <span>📝 {profesional.reviews} reviews</span>
          <span>✓ {profesional.servicios} servicios</span>
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
              {reviews.map((review, idx) => (
                <div key={idx} className="review-card">
                  <div className="review-header">
                    <h4>{review.cliente}</h4>
                    <span className="review-stars">{'⭐'.repeat(review.rating)}</span>
                  </div>
                  <p>{review.comentario}</p>
                </div>
              ))}
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
