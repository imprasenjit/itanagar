import { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import { getMe, login as apiLogin, logout as apiLogout, register as apiRegister } from '../api';
import type { User } from '../types';

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (credentials: { email: string; password: string }) => Promise<unknown>;
  logout: () => Promise<void>;
  signup: (payload: Record<string, string>) => Promise<unknown>;
  refresh: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser]       = useState<User | null>(null);
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

  const login = async (credentials: { email: string; password: string }) => {
    const { data } = await apiLogin(credentials);
    // login response: { status, data: { user: {...}, cartCount, redirect }, message }
    if (data.status) { setUser(data.data.user); return data; }
    throw new Error(data.message || 'Login failed');
  };

  const logout = async () => {
    await apiLogout();
    setUser(null);
  };

  const signup = async (payload: Record<string, string>) => {
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

export const useAuth = () => useContext(AuthContext)!;
