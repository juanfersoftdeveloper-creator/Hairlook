import { createContext, useContext, useState } from 'react';

const AuthContext = createContext(null);

/**
 * Proveedor de contexto de autenticación global.
 * @param {{ children: import('react').ReactNode }} props
 */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [userType, setUserType] = useState(null);

  /**
   * Guarda la sesión del usuario autenticado.
   * @param {object} data - Datos del usuario
   * @param {'cliente'|'profesional'} tipo - Tipo de usuario
   */
  function login(data, tipo) {
    setUser(data);
    setUserType(tipo);
  }

  /** Cierra la sesión actual. */
  function logout() {
    setUser(null);
    setUserType(null);
  }

  const value = {
    user,
    userType,
    isAuthenticated: user !== null,
    login,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

/**
 * Hook para acceder al contexto de autenticación.
 * @returns {{ user: object|null, userType: string|null, isAuthenticated: boolean, login: Function, logout: Function }}
 */
export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }
  return context;
}
