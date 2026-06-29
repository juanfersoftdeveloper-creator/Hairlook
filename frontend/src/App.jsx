import { Routes, Route } from 'react-router-dom';
import Login from './components/auth/Login';
import RegisterCliente from './components/auth/RegisterCliente';
import RegisterProfesional from './components/auth/RegisterProfesional';
import Home from './components/Home';
import ClientPlaceholder from './components/cliente/Placeholder';
import AgendarCita from './components/cliente/AgendarCita';
import MisCitas from './components/cliente/MisCitas';
import HomeProfesional from './components/profesional/HomeProfesional';
import CitasProfesional from './components/profesional/CitasProfesional';
import AgendaProfesional from './components/profesional/AgendaProfesional';
import PerfilProfesional from './components/profesional/PerfilProfesional';
import NotificacionesProfesional from './components/profesional/NotificacionesProfesional';
import './App.css';

/**
 * Componente raíz con las rutas de autenticación de Hairlook.
 */
export default function App() {
  return (
    <div id="app">
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/registro-cliente" element={<RegisterCliente />} />
        <Route path="/registro-profesional" element={<RegisterProfesional />} />
        <Route path="/home" element={<Home />} />
        
        {/* Rutas adicionales de navegación para el cliente */}
        <Route path="/cercanos" element={<ClientPlaceholder title="Peluqueros Cercanos" />} />
        <Route path="/agendar" element={<AgendarCita />} />
        <Route path="/citas" element={<MisCitas />} />
        <Route path="/perfil" element={<ClientPlaceholder title="Mi Perfil" />} />
        <Route path="/notificaciones" element={<ClientPlaceholder title="Notificaciones" />} />
        <Route path="/ofertas" element={<ClientPlaceholder title="Ofertas y Promociones" />} />

        {/* Rutas para el profesional */}
        <Route path="/pro/inicio" element={<HomeProfesional />} />
        <Route path="/pro/citas" element={<CitasProfesional />} />
        <Route path="/pro/agenda" element={<AgendaProfesional />} />
        <Route path="/pro/perfil" element={<PerfilProfesional />} />
        <Route path="/pro/notificaciones" element={<NotificacionesProfesional />} />
      </Routes>
    </div>
  );
}

