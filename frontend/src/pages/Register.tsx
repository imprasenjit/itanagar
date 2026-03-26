import { useState } from 'react';
import logo from '../assets/logo.png';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../components/Toast';

export default function Register() {
  const { signup }  = useAuth();
  const navigate    = useNavigate();
  const addToast    = useToast();

  const [form, setForm]       = useState<{ name: string; email: string; mobile: string; password: string; confirm_password: string }>({ name: '', email: '', mobile: '', password: '', confirm_password: '' });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors]   = useState<Record<string, string>>({});

  const set = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.name.trim()) e.name = 'Name is required';
    if (!form.email)       e.email = 'Email is required';
    if (!form.mobile || !/^\d{10}$/.test(form.mobile)) e.mobile = 'Enter a valid 10-digit mobile number';
    if (!form.password || form.password.length < 6) e.password = 'Password must be at least 6 characters';
    if (form.password !== form.confirm_password) e.confirm_password = 'Passwords do not match';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    setLoading(true);
    try {
      await signup(form);
      addToast('Account created! Welcome 🎉', 'success');
      navigate('/');
    } catch (err: unknown) {
      addToast((err as Error).message || 'Registration failed', 'error');
    } finally {
      setLoading(false);
    }
  };

  const field = (key: keyof typeof form, label: string, type = 'text', placeholder = '', extra: React.InputHTMLAttributes<HTMLInputElement> = {}) => (
    <div key={key}>
      <label className="label">{label}</label>
      <input type={type} value={form[key]} onChange={e => set(key, e.target.value)}
        className={`input ${errors[key] ? 'border-red-500' : ''}`}
        placeholder={placeholder} {...extra}/>
      {errors[key] && <p className="text-xs text-red-400 mt-1">{errors[key]}</p>}
    </div>
  );

  return (
    <div className="min-h-screen flex items-center justify-center px-4 py-20 bg-hero-pattern">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <img src={logo} alt="ItanagarChoice" className="h-16 w-auto object-contain mx-auto mb-3" />
          <h1 className="font-display font-bold text-2xl text-gray-900">Create account</h1>
          <p className="text-gray-500 text-sm mt-1">Join ItanagarChoice and start winning</p>
        </div>

        <div className="card p-6">
          <form onSubmit={handleSubmit} noValidate className="space-y-4">
            {field('name',             'Full Name',        'text',     'Your full name',    { autoFocus: true })}
            {field('email',            'Email address',    'email',    'you@example.com',   { autoComplete: 'email' })}
            {field('mobile',           'Mobile Number',    'tel',      '10-digit number')}
            {field('password',         'Password',         'password', 'Min. 6 characters', { autoComplete: 'new-password' })}
            {field('confirm_password', 'Confirm Password', 'password', 'Repeat password',   { autoComplete: 'new-password' })}
            <button type="submit" disabled={loading} className="btn-primary w-full justify-center mt-2">
              {loading ? 'Creating account…' : 'Create Account'}
            </button>
          </form>
        </div>

        <p className="text-center text-sm text-gray-500 mt-5">
          Already have an account?{' '}
          <Link to="/login" className="text-brand-600 hover:text-brand-700 font-medium transition-colors">Sign in</Link>
        </p>
      </div>
    </div>
  );
}
