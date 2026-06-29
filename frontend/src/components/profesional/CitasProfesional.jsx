import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './CitasProfesional.css';

/**
 * Componente Citas Profesional - ver y gestionar citas.
 */
export default function CitasProfesional() {
  const navigate = useNavigate();

  const citasProximas = [
    { id: 1, cliente: 'Pedro López', servicio: 'Corte', fecha: '2026-07-05', hora: '10:00', estado: 'nueva' },
    { id: 2, cliente: 'Ana García', servicio: 'Tinte', fecha: '2026-07-06', hora: '14:00', estado: 'confirmada' },
  ];

  return (
    <div className="citas-profesional-container">
      <div className="citas-header">
        <button onClick={() => navigate('/pro/inicio')} className="back-btn">← Volver</button>
        <h1>Mis Citas</h1>
      </div>

      <div className="citas-content">
        {citasProximas.map((cita) => (
          <div key={cita.id} className={`cita-card ${cita.estado}`}>
            <h3>{cita.cliente}</h3>
            <p>{cita.servicio} - {cita.fecha} {cita.hora}</p>
            <span className="badge">{cita.estado}</span>
          </div>
        ))}
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
