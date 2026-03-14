import { useEffect, useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { getWithdrawals, createWithdrawal } from '../../api';
import { useToast } from '../../components/Toast';

const STATUS: Record<number, [string, string]> = { 0: ['Pending', 'yellow'], 1: ['Approved', 'emerald'], 2: ['Rejected', 'red'] };

export default function Withdrawals() {
  const addToast = useToast();
  const [list, setList]           = useState<Record<string, any>[]>([]);
  const [form, setForm]           = useState<{ amount: string; account_number: string; ifsc: string; account_name: string }>({ amount: '', account_number: '', ifsc: '', account_name: '' });
  const [submitting, setSubmitting] = useState(false);

  const fetchList = () => getWithdrawals().then(r => setList(r.data.data || [])).catch(() => {});

  useEffect(() => { fetchList(); }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const r = await createWithdrawal(form);
      if (r.data.status) { addToast('Withdrawal request submitted!', 'success'); setForm({ amount: '', account_number: '', ifsc: '', account_name: '' }); fetchList(); }
      else addToast(r.data.message || 'Failed to submit withdrawal', 'error');
    } catch { addToast('Something went wrong', 'error'); }
    finally  { setSubmitting(false); }
  };

  const setF = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-white mb-6">Withdrawals</h1>

      <div className="card p-5 mb-5">
        <h2 className="text-sm font-semibold text-white mb-3">New Withdrawal Request</h2>
        <form onSubmit={handleSubmit} className="space-y-3">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div><label className="label">Amount (₹)</label><input type="number" value={form.amount} onChange={e => setF('amount', e.target.value)} className="input" placeholder="0" min="1" required/></div>
            <div><label className="label">Account Holder Name</label><input value={form.account_name} onChange={e => setF('account_name', e.target.value)} className="input" placeholder="As per bank records" required/></div>
            <div><label className="label">Account Number</label><input value={form.account_number} onChange={e => setF('account_number', e.target.value)} className="input" placeholder="Bank account number" required/></div>
            <div><label className="label">IFSC Code</label><input value={form.ifsc} onChange={e => setF('ifsc', e.target.value.toUpperCase())} className="input" placeholder="e.g. SBIN0001234" required/></div>
          </div>
          <button type="submit" disabled={submitting} className="btn-primary">
            {submitting ? 'Submitting…' : 'Submit Request'}
          </button>
        </form>
      </div>

      <div className="card overflow-hidden">
        <div className="px-5 py-3 border-b border-white/5"><h2 className="text-sm font-semibold text-white">Request History</h2></div>
        {list.length === 0 ? (
          <div className="text-center py-10 text-gray-500 text-sm">No withdrawal requests yet.</div>
        ) : (
          <div className="divide-y divide-white/5">
            {list.map(w => {
              const [label, color] = STATUS[w.status] || STATUS[0];
              return (
                <div key={w.id} className="flex items-center justify-between px-5 py-3.5">
                  <div>
                    <p className="text-sm font-medium text-white">₹{Number(w.amount).toLocaleString('en-IN')}</p>
                    <p className="text-xs text-gray-500">{w.account_number} · {w.ifsc}</p>
                  </div>
                  <span className={`badge bg-${color}-500/15 text-${color}-400 border border-${color}-500/20`}>{label}</span>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </AccountLayout>
  );
}
