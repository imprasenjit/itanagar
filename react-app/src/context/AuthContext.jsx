import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { getMe, login as apiLogin, logout as apiLogout, register as apiRegister } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser]       = useState(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      const { data } = await getMe();
      // me() always returns status:true; use isLoggedIn inside data to decide
      setUser(data.status && data.data?.isLoggedIn ? data.data.user : null);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { refresh(); }, [refresh]);

  const login = async (credentials) => {
    const { data } = await apiLogin(credentials);
    // login response: { status, data: { user: {...}, cartCount, redirect }, message }
    if (data.status) { setUser(data.data.user); return data; }
    throw new Error(data.message || 'Login failed');
  };

  const logout = async () => {
    await apiLogout();
    setUser(null);
  };

  const signup = async (payload) => {
    const { data } = await apiRegister(payload);
    // register does NOT create a session — just returns { userId }
    // caller is responsible for redirecting to login
    if (data.status) return data;
    throw new Error(data.message || 'Registration failed');
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, signup, refresh }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
