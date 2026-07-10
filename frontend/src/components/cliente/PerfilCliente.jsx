import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { traerCitasUsuario } from '../../services/citasService';
import './PerfilCliente.css';

export default function PerfilCliente() {
  const navigate = useNavigate();
  const { user, id, logout } = useAuth();
  const [subTab, setSubTab] = useState('info');

  const [citas, setCitas] = useState([]);
  const [cargandoCitas, setCargandoCitas] = useState(false);
  const [errorCitas, setErrorCitas] = useState(null);

  const nombre = user?.nombre || 'Cliente';
  const correo = user?.correo || '';

  useEffect(() => {
    const cargar = async () => {
      if (!id) return;
      setCargandoCitas(true);
      setErrorCitas(null);
      const res = await traerCitasUsuario(id);
      if (res.ok) {
        setCitas(res.data || []);
      } else {
        setErrorCitas(res.error || 'Error al cargar citas');
      }
      setCargandoCitas(false);
    };

    if (subTab === 'historial') cargar();
  }, [id, subTab]);

  const handleLogout = () => {
    if (confirm('¿Deseas cerrar sesión?')) {
      logout();
      navigate('/');
    }
  };

  return (
    <div className="perfil-cliente-container">
      <div className="perfil-header">
        <button onClick={() => navigate(-1)} className="back-btn">← Volver</button>
        <h1>Mi Perfil</h1>
      </div>

      <div className="perfil-banner">
        <div className="avatar-grande">🙂</div>
        <h2>{nombre}</h2>
        <p className="perfil-correo">{correo}</p>
      </div>

      <div className="sub-tabs">
        <button className={`sub-tab-btn ${subTab === 'info' ? 'active' : ''}`} onClick={() => setSubTab('info')}>Información</button>
        <button className={`sub-tab-btn ${subTab === 'historial' ? 'active' : ''}`} onClick={() => setSubTab('historial')}>Historial</button>
        <button className={`sub-tab-btn ${subTab === 'cuenta' ? 'active' : ''}`} onClick={() => setSubTab('cuenta')}>Cuenta</button>
      </div>

      <div className="perfil-content">
        {subTab === 'info' && (
          <div className="info-section">
            <div className="info-card">
              <h3>Datos personales</h3>
              <p><strong>Nombre:</strong> {nombre}</p>
              <p><strong>Correo:</strong> {correo}</p>
            </div>

            <div className="info-card">
              <h3>Preferencias</h3>
              <p>No has definido preferencias aún.</p>
            </div>
          </div>
        )}

        {subTab === 'historial' && (
          <div className="historial-section">
            <h3>Historial de citas</h3>

            {cargandoCitas && <p>Cargando citas…</p>}
            {errorCitas && <div className="info-card">Error: {errorCitas}</div>}

            {!cargandoCitas && !errorCitas && (
              <div className="citas-list">
                {citas.length === 0 ? (
                  <div className="info-card">No hay citas registradas.</div>
                ) : (
                  citas.map((c) => (
                    <div className="cita-card" key={c.ID_Cita}>
                      <div className="cita-left">
                        <div className="cita-fecha">{c.Fecha ?? ''} {c.Hora ? `· ${c.Hora}` : ''}</div>
                        <div className="cita-prof">{c.NombreProfesional ?? 'Profesional'}</div>
                        <div className="cita-servicio">{c.NombreServicio ?? ''}</div>
                      </div>
                      <div className="cita-right">
                        <div className={`cita-estado cita-estado--${c.Estado}`} >{c.Estado}</div>
                        <div className="cita-precio">${c.Precio ?? ''}</div>
                      </div>
                    </div>
                  ))
                )}
              </div>
            )}
          </div>
        )}

        {subTab === 'cuenta' && (
          <div className="cuenta-section">
            <button className="cuenta-item" onClick={() => navigate('/registro-cliente')}>✏️ Editar perfil</button>
            <button className="cuenta-item" onClick={() => navigate('/cambiar-password')}>🔒 Cambiar contraseña</button>
            <button className="cuenta-item logout" onClick={handleLogout}>🚪 Cerrar sesión</button>
          </div>
        )}
      </div>

      <nav className="bottom-nav">
        <button className="nav-btn" onClick={() => navigate('/home')}>Inicio</button>
        <button className="nav-btn" onClick={() => navigate('/cercanos')}>Cercanos</button>
        <button className="nav-btn" onClick={() => navigate('/agendar')}>Agendar</button>
        <button className="nav-btn" onClick={() => navigate('/citas')}>Mis citas</button>
        <button className="nav-btn active" onClick={() => navigate('/perfil')}>Perfil</button>
      </nav>
    </div>
  );
}
