import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './AgendaProfesional.css';

/**
 * Componente Agenda Profesional (Bloque 6).
 */
export default function AgendaProfesional() {
  const navigate = useNavigate();
  const [dias, setDias] = useState({
    lunes: { activo: true, inicio: '09:00', fin: '17:00' },
    martes: { activo: true, inicio: '09:00', fin: '17:00' },
    miercoles: { activo: true, inicio: '09:00', fin: '17:00' },
    jueves: { activo: true, inicio: '09:00', fin: '17:00' },
    viernes: { activo: true, inicio: '09:00', fin: '17:00' },
    sabado: { activo: true, inicio: '09:00', fin: '15:00' },
    domingo: { activo: false, inicio: '00:00', fin: '00:00' },
  });

  const diasLabels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

  const handleToggleDia = (dia) => {
    setDias({
      ...dias,
      [dia]: { ...dias[dia], activo: !dias[dia].activo },
    });
  };

  const handleChangeHora = (dia, campo, valor) => {
    setDias({
      ...dias,
      [dia]: { ...dias[dia], [campo]: valor },
    });
  };

  const handleGuardarDisponibilidad = () => {
    alert('Disponibilidad guardada exitosamente');
  };

  return (
    <div className="agenda-profesional-container">
      {/* Header */}
      <div className="agenda-header">
        <button onClick={() => navigate('/pro/inicio')} className="back-btn">
          ← Volver
        </button>
        <h1>Disponibilidad</h1>
      </div>

      {/* Contenido */}
      <div className="agenda-content">
        <h2>Gestiona tu horario</h2>
        <p className="intro-text">Define los días y horas en que estás disponible para atender a tus clientes.</p>

        <div className="dias-disponibilidad">
          {Object.keys(dias).map((dia, idx) => (
            <div key={dia} className="dia-item">
              <div className="dia-header">
                <label className="dia-label">
                  <input
                    type="checkbox"
                    checked={dias[dia].activo}
                    onChange={() => handleToggleDia(dia)}
                  />
                  <span>{diasLabels[idx]}</span>
                </label>
              </div>

              {dias[dia].activo && (
                <div className="dia-horario">
                  <div className="horario-input">
                    <label>Inicio</label>
                    <input
                      type="time"
                      value={dias[dia].inicio}
                      onChange={(e) => handleChangeHora(dia, 'inicio', e.target.value)}
                    />
                  </div>
                  <div className="horario-input">
                    <label>Fin</label>
                    <input
                      type="time"
                      value={dias[dia].fin}
                      onChange={(e) => handleChangeHora(dia, 'fin', e.target.value)}
                    />
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>

        <button className="btn-guardar-agenda" onClick={handleGuardarDisponibilidad}>
          💾 Guardar disponibilidad
        </button>
      </div>

      {/* Bottom Navigation */}
      <nav className="bottom-nav-pro">
        <button className="nav-btn-pro" onClick={() => navigate('/pro/inicio')}>
          <span>Inicio</span>
        </button>
        <button className="nav-btn-pro" onClick={() => navigate('/pro/citas')}>
          <span>Citas</span>
        </button>
        <button className="nav-btn-pro active" onClick={() => navigate('/pro/agenda')}>
          <span>Agenda</span>
        </button>
        <button className="nav-btn-pro" onClick={() => navigate('/pro/perfil')}>
          <span>Perfil</span>
        </button>
      </nav>
    </div>
  );
}
