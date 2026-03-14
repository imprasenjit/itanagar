import { useState } from 'react';
import { Link } from 'react-router-dom';
import { forgotPassword } from '../api';
import { useToast } from '../components/Toast';

export default function ForgotPassword() {
  const addToast = useToast();
  const [email, setEmail]   = useState('');
  const [sent, setSent]     = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!email) return;
    setLoading(true);
    try {
      const r = await forgotPassword({ email });
      if (r.data.status) { setSent(true); addToast('Reset link sent!', 'success'); }
      else addToast(r.data.message || 'Email not found', 'error');
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
            <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
          </div>
          <h1 className="font-display font-bold text-2xl text-white">Forgot Password</h1>
          <p className="text-gray-500 text-sm mt-1">Enter your email and we'll send a reset link</p>
        </div>

        <div className="card p-6">
          {sent ? (
            <div className="text-center py-4">
              <p className="text-4xl mb-3">📧</p>
              <p className="text-white font-semibold mb-2">Check your inbox</p>
              <p className="text-sm text-gray-400">We sent a password reset link to <strong className="text-white">{email}</strong></p>
            </div>
          ) : (
            <form onSubmit={handleSubmit} noValidate className="space-y-4">
              <div>
                <label className="label">Email address</label>
                <input type="email" value={email} onChange={e => setEmail(e.target.value)}
                  className="input" placeholder="you@example.com" autoFocus/>
              </div>
              <button type="submit" disabled={loading || !email} className="btn-primary w-full justify-center disabled:opacity-40">
                {loading ? 'Sending…' : 'Send Reset Link'}
              </button>
            </form>
          )}
        </div>

        <p className="text-center text-sm text-gray-500 mt-5">
          <Link to="/login" className="text-brand-400 hover:text-brand-300 font-medium transition-colors">← Back to Sign in</Link>
        </p>
      </div>
    </div>
  );
}
