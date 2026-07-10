import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { traerAgendaProfesional } from '../../services/agendaService';
import { cambiarEstadoCita } from '../../services/citasService';
import './CitasProfesional.css';

/**
 * Componente Citas Profesional - ver y gestionar citas.
 */
export default function CitasProfesional() {
  const navigate = useNavigate();
  const { id: profesionalId } = useAuth();
  const [citas, setCitas] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);

  // Cargar citas del profesional
  useEffect(() => {
    const cargarCitas = async () => {
      if (!profesionalId) {
        setError('No se pudo identificar el profesional');
        setCargando(false);
        return;
      }

      setCargando(true);
      setError(null);
      const res = await traerAgendaProfesional(profesionalId, false);
      
      if (res.ok && Array.isArray(res.data)) {
        setCitas(res.data);
      } else {
        setError(res.error || 'Error al cargar citas');
      }
      
      setCargando(false);
    };

    cargarCitas();
  }, [profesionalId]);

  const handleEstadoChange = async (citaId, nuevoEstado) => {
    const res = await cambiarEstadoCita(citaId, nuevoEstado);
    if (res.ok) {
      // Actualizar estado local
      setCitas(prev => prev.map(c => 
        c.ID_Cita === citaId ? { ...c, Estado: nuevoEstado } : c
      ));
      alert(`Cita ${nuevoEstado} exitosamente`);
    } else {
      alert(res.error || 'Error al cambiar estado de cita');
    }
  };

  return (
    <div className="citas-profesional-container">
      <div className="citas-header">
        <button onClick={() => navigate('/pro/inicio')} className="back-btn">← Volver</button>
        <h1>Mis Citas</h1>
      </div>

      <div className="citas-content">
        {cargando ? (
          <p>Cargando citas...</p>
        ) : error ? (
          <p className="error-message">{error}</p>
        ) : citas.length === 0 ? (
          <p>No hay citas aún</p>
        ) : (
              citas.map((cita) => {
                const servicioNombre = cita.Servicios?.[0]?.Nombre || 'Servicio';
                const precio = cita.Servicios?.[0]?.Precio_aplicado || cita.Servicios?.[0]?.Precio || '0';
                const hora = cita.Fecha_hora ? new Date(cita.Fecha_hora).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }) : 'Hora';
                const fecha = cita.Fecha_hora ? new Date(cita.Fecha_hora).toLocaleDateString('es-CO') : 'Fecha';
                return (
                  <div key={cita.ID_Cita} className={`cita-card ${cita.Estado}`}>
                    <div className="cita-info">
                      <h3>{cita.Cliente || 'Cliente'}</h3>
                      <p>{servicioNombre} - {fecha} {hora}</p>
                      <p className="precio">Precio: ${precio}</p>
                    </div>
                    <div className="cita-actions">
                      <span className="badge">{cita.Estado}</span>
                      {cita.Estado === 'pendiente' && (
                        <div className="action-buttons">
                          <button 
                            onClick={() => handleEstadoChange(cita.ID_Cita, 'confirmada')}
                            className="btn-aceptar"
                          >
                            ✓ Aceptar
                          </button>
                          <button 
                            onClick={() => handleEstadoChange(cita.ID_Cita, 'cancelada')}
                            className="btn-rechazar"
                          >
                            ✕ Rechazar
                          </button>
                        </div>
                      )}
                    </div>
                  </div>
                );
              })
        )}
      </div>

      <nav className="bottom-nav-pro">
        <button onClick={() => navigate('/pro/inicio')}>Inicio</button>
        <button className="active" onClick={() => navigate('/pro/citas')}>Citas</button>
        <button onClick={() => navigate('/pro/agenda')}>Agenda</button>
        <button onClick={() => navigate('/pro/perfil')}>Perfil</button>
      </nav>
    </div>
  );
}
