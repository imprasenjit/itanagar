import { useState } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';
import { resetPassword } from '../api';
import { useToast } from '../components/Toast';

export default function ResetPassword() {
  const [params]    = useSearchParams();
  const navigate    = useNavigate();
  const addToast    = useToast();

  const email          = params.get('email') ?? '';
  const activationCode = params.get('code')  ?? '';

  const [form, setForm]       = useState({ password: '', password_confirmation: '' });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors]   = useState<Record<string, string>>({});

  const set = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.password)              e.password = 'Password is required';
    else if (form.password.length < 6) e.password = 'Password must be at least 6 characters';
    if (!form.password_confirmation) e.password_confirmation = 'Please confirm your password';
    else if (form.password !== form.password_confirmation) e.password_confirmation = 'Passwords do not match';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  if (!email || !activationCode) {
    return (
      <div className="min-h-screen flex items-center justify-center px-4 bg-hero-pattern">
        <div className="w-full max-w-md text-center">
          <div className="card p-6">
            <p className="text-4xl mb-3">⚠️</p>
            <p className="text-gray-900 font-semibold mb-2">Invalid Reset Link</p>
            <p className="text-sm text-gray-500 mb-4">This password reset link is invalid or has expired.</p>
            <Link to="/forgot-password" className="btn-primary inline-flex justify-center">Request a New Link</Link>
          </div>
        </div>
      </div>
    );
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    setLoading(true);
    try {
      const r = await resetPassword({
        email,
        activation_code: activationCode,
        password: form.password,
        password_confirmation: form.password_confirmation,
      });
      if (r.data.status) {
        addToast('Password reset successfully!', 'success');
        navigate('/login', { replace: true });
      } else {
        addToast(r.data.message || 'Reset failed', 'error');
      }
    } catch {
      addToast('Something went wrong. Try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center px-4 bg-hero-pattern">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center mx-auto mb-3 shadow-xl shadow-brand-500/30">
            <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          <h1 className="font-display font-bold text-2xl text-gray-900">Reset Password</h1>
          <p className="text-gray-500 text-sm mt-1">Enter your new password below</p>
        </div>

        <div className="card p-6">
          <form onSubmit={handleSubmit} noValidate className="space-y-4">
            <div>
              <label className="label">New Password</label>
              <input type="password" value={form.password} onChange={e => set('password', e.target.value)}
                className={`input ${errors.password ? 'border-red-500' : ''}`}
                placeholder="••••••••" autoFocus/>
              {errors.password && <p className="text-xs text-red-400 mt-1">{errors.password}</p>}
            </div>
            <div>
              <label className="label">Confirm Password</label>
              <input type="password" value={form.password_confirmation} onChange={e => set('password_confirmation', e.target.value)}
                className={`input ${errors.password_confirmation ? 'border-red-500' : ''}`}
                placeholder="••••••••"/>
              {errors.password_confirmation && <p className="text-xs text-red-400 mt-1">{errors.password_confirmation}</p>}
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full justify-center">
              {loading ? 'Resetting…' : 'Reset Password'}
            </button>
          </form>
        </div>

        <p className="text-center text-sm text-gray-500 mt-5">
          <Link to="/login" className="text-brand-600 hover:text-brand-700 font-medium transition-colors">← Back to Sign in</Link>
        </p>
      </div>
    </div>
  );
}
