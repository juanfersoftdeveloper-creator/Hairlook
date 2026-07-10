import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

/**
 * Proveedor de contexto de autenticación global.
 * @param {{ children: import('react').ReactNode }} props
 */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [userType, setUserType] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // Helper: normalize backend user object shapes into { id, nombre, correo }
  function normalizeUser(u) {
    if (!u) return null;
    const rawId = u.ID_Usuario ?? u.ID_Profesional ?? u.id ?? u.ID ?? null;
    const id = rawId !== null && rawId !== undefined ? String(rawId) : null;
    return {
      ...u,
      id,
      nombre: u.Nombre ?? u.nombre ?? '',
      correo: u.Correo ?? u.correo ?? '',
    };
  }

  // Restaurar sesión desde localStorage al montar (normalizando si es necesario)
  useEffect(() => {
    const savedUser = localStorage.getItem('hairlook_user');
    const savedUserType = localStorage.getItem('hairlook_userType');

    if (savedUser && savedUserType) {
      try {
        const parsed = JSON.parse(savedUser);
        setUser(normalizeUser(parsed));
        setUserType(savedUserType);
      } catch (e) {
        console.error('Error restoring session:', e);
        localStorage.removeItem('hairlook_user');
        localStorage.removeItem('hairlook_userType');
      }
    }

    setIsLoading(false);
  }, []);

  /**
   * Guarda la sesión del usuario autenticado.
   * @param {object} data - Datos del usuario
   * @param {'cliente'|'profesional'} tipo - Tipo de usuario
   */
  function login(data, tipo) {
    const normalized = normalizeUser(data);
    setUser(normalized);
    setUserType(tipo);
    localStorage.setItem('hairlook_user', JSON.stringify(normalized));
    localStorage.setItem('hairlook_userType', tipo);
  }

  /** Cierra la sesión actual. */
  function logout() {
    setUser(null);
    setUserType(null);
    localStorage.removeItem('hairlook_user');
    localStorage.removeItem('hairlook_userType');
  }

  const value = {
    user,
    userType,
    isAuthenticated: user !== null,
    isLoading,
    login,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

/**
 * Hook para acceder al contexto de autenticación.
 * @returns {{ user: object|null, userType: string|null, isAuthenticated: boolean, isLoading: boolean, id: string|null, login: Function, logout: Function }}
 */
export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }
  
  // Si user tiene 'id', exponlo en el nivel raíz también
  const id = context.user?.id || null;
  
  return {
    ...context,
    id, // Agregar id al nivel raíz para desestructuración fácil
    user: context.user || { id, nombre: '', correo: '', tipo: '' }
  };
}
