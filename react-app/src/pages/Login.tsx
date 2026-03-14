import { useState } from 'react';
import logo from '../assets/logo.png';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../components/Toast';

export default function Login() {
  const { login }   = useAuth();
  const navigate    = useNavigate();
  const location    = useLocation();
  const addToast    = useToast();
  const redirect    = location.state?.from || '/';

  const [form, setForm]       = useState<{ email: string; password: string }>({ email: '', password: '' });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors]   = useState<Record<string, string>>({});

  const set = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.email) e.email = 'Email is required';
    if (!form.password) e.password = 'Password is required';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    setLoading(true);
    try {
      await login(form);
      addToast('Welcome back!', 'success');
      navigate(redirect, { replace: true });
    } catch (err: unknown) {
      addToast((err as Error).message || 'Invalid credentials', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center px-4 bg-hero-pattern">
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-8">
          <img src={logo} alt="ItanagarChoice" className="h-16 w-auto object-contain mx-auto mb-3" />
          <h1 className="font-display font-bold text-2xl text-white">Sign in</h1>
          <p className="text-gray-500 text-sm mt-1">Welcome back to ItanagarChoice</p>
        </div>

        <div className="card p-6">
          <form onSubmit={handleSubmit} noValidate className="space-y-4">
            <div>
              <label className="label">Email address</label>
              <input type="email" value={form.email} onChange={e => set('email', e.target.value)}
                className={`input ${errors.email ? 'border-red-500' : ''}`}
                placeholder="you@example.com" autoComplete="email" autoFocus/>
              {errors.email && <p className="text-xs text-red-400 mt-1">{errors.email}</p>}
            </div>
            <div>
              <div className="flex justify-between mb-1.5">
                <label className="label m-0">Password</label>
                <Link to="/forgot-password" className="text-xs text-brand-400 hover:text-brand-300 transition-colors">Forgot password?</Link>
              </div>
              <input type="password" value={form.password} onChange={e => set('password', e.target.value)}
                className={`input ${errors.password ? 'border-red-500' : ''}`}
                placeholder="••••••••" autoComplete="current-password"/>
              {errors.password && <p className="text-xs text-red-400 mt-1">{errors.password}</p>}
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full justify-center">
              {loading ? 'Signing in…' : 'Sign in'}
            </button>
          </form>
        </div>

        <p className="text-center text-sm text-gray-500 mt-5">
          Don't have an account?{' '}
          <Link to="/register" className="text-brand-400 hover:text-brand-300 font-medium transition-colors">Create one</Link>
        </p>
      </div>
    </div>
  );
}
