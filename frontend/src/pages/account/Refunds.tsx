import { useEffect, useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { getRefunds, createRefund } from '../../api';
import { useToast } from '../../components/Toast';

const STATUS: Record<number, [string, string]> = { 0: ['Pending', 'yellow'], 1: ['Approved', 'emerald'], 2: ['Rejected', 'red'] };

export default function Refunds() {
  const addToast = useToast();
  const [refunds, setRefunds] = useState<Record<string, any>[]>([]);
  const [form, setForm]       = useState<{ order_id: string; reason: string }>({ order_id: '', reason: '' });
  const [submitting, setSubmitting] = useState(false);

  const fetchRefunds = () => getRefunds().then(r => setRefunds(r.data.data || [])).catch(() => {});

  useEffect(() => { fetchRefunds(); }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const r = await createRefund(form);
      if (r.data.status) { addToast('Refund request submitted!', 'success'); setForm({ order_id: '', reason: '' }); fetchRefunds(); }
      else addToast(r.data.message || 'Failed to submit refund', 'error');
    } catch { addToast('Something went wrong', 'error'); }
    finally  { setSubmitting(false); }
  };

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-gray-900 mb-6">Refund Requests</h1>

      {/* Request form */}
      <div className="card p-5 mb-5">
        <h2 className="text-sm font-semibold text-gray-900 mb-3">New Refund Request</h2>
        <form onSubmit={handleSubmit} className="space-y-3">
          <div>
            <label className="label">Order ID</label>
            <input value={form.order_id} onChange={e => setForm(f => ({ ...f, order_id: e.target.value }))}
              className="input" placeholder="Enter your Order ID" required/>
          </div>
          <div>
            <label className="label">Reason</label>
            <textarea value={form.reason} onChange={e => setForm(f => ({ ...f, reason: e.target.value }))}
              className="input min-h-[80px]" placeholder="Describe the reason for refund" rows={3} required/>
          </div>
          <button type="submit" disabled={submitting} className="btn-primary">
            {submitting ? 'Submitting…' : 'Submit Request'}
          </button>
        </form>
      </div>

      {/* History */}
      <div className="card overflow-hidden">
        <div className="px-5 py-3 border-b border-gray-200">
          <h2 className="text-sm font-semibold text-gray-900">Request History</h2>
        </div>
        {refunds.length === 0 ? (
          <div className="text-center py-10 text-gray-500 text-sm">No refund requests yet.</div>
        ) : (
          <div className="divide-y divide-gray-100">
            {refunds.map(r => {
              const [label, color] = STATUS[r.status] || STATUS[0];
              return (
                <div key={r.id} className="flex items-center justify-between px-5 py-3.5">
                  <div>
                    <p className="text-sm font-medium text-gray-900">Order #{r.order_id}</p>
                    <p className="text-xs text-gray-500 truncate max-w-xs">{r.reason}</p>
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
