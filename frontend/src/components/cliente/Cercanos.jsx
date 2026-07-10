import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { traerProfesionales } from '../../services/citasService';
import './Cercanos.css';

export default function Cercanos() {
  const navigate = useNavigate();
  const [profesionales, setProfesionales] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [query, setQuery] = useState('');

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      setError(null);
      const res = await traerProfesionales();
      if (res.ok) setProfesionales(res.data || []);
      else setError(res.error || 'Error al cargar profesionales');
      setLoading(false);
    };
    load();
  }, []);

  const filtered = profesionales.filter((p) =>
    p.nombre.toLowerCase().includes(query.toLowerCase()) ||
    p.especialidad.toLowerCase().includes(query.toLowerCase())
  );

  return (
    <div className="cercanos-container">
      <div className="cercanos-header">
        <button className="back-btn" onClick={() => navigate(-1)}>← Volver</button>
        <h1>Peluqueros cerca de ti</h1>
      </div>

      <div className="cercanos-search">
        <input
          placeholder="Buscar por nombre o especialidad"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
        />
      </div>

      <div className="cercanos-content">
        {loading && <div className="info-card">Cargando profesionales…</div>}
        {error && <div className="info-card">Error: {error}</div>}

        {!loading && !error && (
          <div className="prof-list">
            {filtered.length === 0 ? (
              <div className="info-card">No se encontraron profesionales.</div>
            ) : (
              filtered.map((p) => (
                <div className="prof-card" key={p.id} onClick={() => navigate(`/pro/perfil/${p.id}`)}>
                  <div className="prof-left">
                    <div className="prof-avatar">💈</div>
                    <div className="prof-info">
                      <div className="prof-name">{p.nombre}</div>
                      <div className="prof-esp">{p.especialidad}</div>
                    </div>
                  </div>
                  <div className="prof-actions">
                    <div className="prof-rating">⭐ {p.rating || '—'}</div>
                    <button className="btn-reservar" onClick={(e) => { e.stopPropagation(); navigate('/agendar'); }}>Reservar</button>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </div>

      <nav className="bottom-nav">
        <button className="nav-btn" onClick={() => navigate('/home')}>Inicio</button>
        <button className="nav-btn active" onClick={() => navigate('/cercanos')}>Cercanos</button>
        <button className="nav-btn" onClick={() => navigate('/agendar')}>Agendar</button>
        <button className="nav-btn" onClick={() => navigate('/citas')}>Mis citas</button>
        <button className="nav-btn" onClick={() => navigate('/perfil')}>Perfil</button>
      </nav>
    </div>
  );
}
