import { useEffect, useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { getTransfers, createTransfer } from '../../api';
import { useToast } from '../../components/Toast';

export default function Transfers() {
  const addToast = useToast();
  const [list, setList]           = useState<Record<string, any>[]>([]);
  const [form, setForm]           = useState<{ to_user: string; amount: string; note: string }>({ to_user: '', amount: '', note: '' });
  const [submitting, setSubmitting] = useState(false);

  const fetchList = () => getTransfers().then(r => setList(r.data.data || [])).catch(() => {});

  useEffect(() => { fetchList(); }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const r = await createTransfer(form);
      if (r.data.status) { addToast('Transfer successful!', 'success'); setForm({ to_user: '', amount: '', note: '' }); fetchList(); }
      else addToast(r.data.message || 'Transfer failed', 'error');
    } catch { addToast('Something went wrong', 'error'); }
    finally  { setSubmitting(false); }
  };

  const setF = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-white mb-6">Wallet Transfers</h1>

      <div className="card p-5 mb-5">
        <h2 className="text-sm font-semibold text-white mb-3">Transfer to User</h2>
        <form onSubmit={handleSubmit} className="space-y-3">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div><label className="label">Recipient Email / ID</label><input value={form.to_user} onChange={e => setF('to_user', e.target.value)} className="input" placeholder="Email or User ID" required/></div>
            <div><label className="label">Amount (₹)</label><input type="number" value={form.amount} onChange={e => setF('amount', e.target.value)} className="input" placeholder="0" min="1" required/></div>
          </div>
          <div><label className="label">Note (optional)</label><input value={form.note} onChange={e => setF('note', e.target.value)} className="input" placeholder="Add a note…"/></div>
          <button type="submit" disabled={submitting} className="btn-primary">
            {submitting ? 'Transferring…' : '🔄 Transfer'}
          </button>
        </form>
      </div>

      <div className="card overflow-hidden">
        <div className="px-5 py-3 border-b border-white/5"><h2 className="text-sm font-semibold text-white">Transfer History</h2></div>
        {list.length === 0 ? (
          <div className="text-center py-10 text-gray-500 text-sm">No transfers yet.</div>
        ) : (
          <div className="divide-y divide-white/5">
            {list.map(t => (
              <div key={t.id} className="flex items-center justify-between px-5 py-3.5">
                <div>
                  <p className="text-sm font-medium text-white">{t.to_user || t.to_email || `User #${t.to_user_id}`}</p>
                  {t.note && <p className="text-xs text-gray-500">{t.note}</p>}
                </div>
                <p className="text-sm font-bold text-red-400">-₹{Number(t.amount).toLocaleString('en-IN')}</p>
              </div>
            ))}
          </div>
        )}
      </div>
    </AccountLayout>
  );
}
