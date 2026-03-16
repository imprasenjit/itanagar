import { useState } from 'react';
import { postContact } from '../api';
import { useToast } from '../components/Toast';

export default function Contact() {
  const addToast = useToast();
  const [form, setForm]       = useState<{ name: string; email: string; mobile: string; message: string }>({ name: '', email: '', mobile: '', message: '' });
  const [loading, setLoading] = useState(false);
  const [sent, setSent]       = useState(false);

  const set = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const r = await postContact(form);
      if (r.data.status) { setSent(true); addToast('Message sent!', 'success'); }
      else addToast(r.data.message || 'Failed to send message', 'error');
    } catch {
      addToast('Something went wrong. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <div className="text-center mb-10">
        <p className="text-xs text-brand-600 font-semibold uppercase tracking-widest mb-1">Get in Touch</p>
        <h1 className="font-display font-bold text-3xl text-gray-900">Contact Us</h1>
        <p className="text-gray-500 text-sm mt-2">Have a question or need support? We're here to help.</p>
      </div>

      <div className="card p-6">
        {sent ? (
          <div className="text-center py-8">
            <p className="text-5xl mb-3">✅</p>
            <p className="text-white font-semibold text-lg mb-2">Message Sent!</p>
            <p className="text-sm text-gray-500">We'll get back to you within 24 hours.</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} noValidate className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="label">Name</label>
                <input value={form.name} onChange={e => set('name', e.target.value)}
                  className="input" placeholder="Your name" required/>
              </div>
              <div>
                <label className="label">Mobile</label>
                <input value={form.mobile} onChange={e => set('mobile', e.target.value)}
                  className="input" placeholder="10-digit number" type="tel"/>
              </div>
            </div>
            <div>
              <label className="label">Email</label>
              <input type="email" value={form.email} onChange={e => set('email', e.target.value)}
                className="input" placeholder="you@example.com" required/>
            </div>
            <div>
              <label className="label">Message</label>
              <textarea value={form.message} onChange={e => set('message', e.target.value)}
                className="input min-h-[120px] resize-y" placeholder="How can we help you?" rows={4} required/>
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full justify-center">
              {loading ? 'Sending…' : 'Send Message'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
}
