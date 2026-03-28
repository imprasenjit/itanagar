import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import AccountLayout from '../../components/AccountLayout';
import { getOrders } from '../../api';
import LoadingSpinner from '../../components/LoadingSpinner';

import type { CartItem } from '../../types';

const STATUS: Record<string, [string, string]> = {
  CREATED:   ['Pending',   'yellow'],
  PAID:      ['Confirmed', 'emerald'],
  RELEASED:  ['Refunded',  'blue'],
  CANCELLED: ['Cancelled', 'red'],
  // legacy numeric values
  '0': ['Pending',   'yellow'],
  '1': ['Confirmed', 'emerald'],
  '2': ['Failed',    'red'],
};

export default function OrderHistory() {
  const [orders, setOrders]   = useState<Record<string, any>[]>([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    getOrders().then(r => setOrders(r.data.data?.orders || [])).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <AccountLayout><LoadingSpinner size="lg" text="Loading orders…"/></AccountLayout>;

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-gray-900 mb-6">My Orders</h1>
      {orders.length === 0 ? (
        <div className="card text-center py-16">
          <p className="text-4xl mb-3">📋</p>
          <p className="text-gray-500 mb-4">No orders yet.</p>
          <Link to="/games" className="btn-primary inline-flex">Browse Games</Link>
        </div>
      ) : (
        <div className="space-y-3">
          {orders.map(o => {
            const [label, color] = STATUS[String(o.paid_status)] ?? STATUS['CREATED'];
            const ticketNos: string[] = (() => {
              try {
                const parsed = typeof o.tickets === 'string' ? JSON.parse(o.tickets) : o.tickets;
                return Array.isArray(parsed) ? parsed.map((t: any) => String(t.ticket_no ?? t)).filter(Boolean) : [];
              } catch { return []; }
            })();
            const amount = o.total_price ?? o.amount;
            return (
              <div
                key={o.id}
                className="card p-4 cursor-pointer hover:shadow-md transition-shadow"
                onClick={() => navigate(`/account/orders/${o.id}`)}
              >
                <div className="flex items-start justify-between gap-4">
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2 flex-wrap">
                      <p className="text-sm font-semibold text-gray-900">{`Order #${o.id}`}</p>
                      {o.createdAt && (
                        <p className="text-xs text-gray-400">{new Date(o.createdAt).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}</p>
                      )}
                    </div>
                    {ticketNos.length > 0 && (
                      <div className="mt-1.5">
                        <p className="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Tickets</p>
                        <div className="flex flex-wrap gap-1">
                          {ticketNos.map((no, i) => (
                            <span key={i} className="text-xs font-mono bg-gray-100 text-gray-600 rounded px-1.5 py-0.5">{no}</span>
                          ))}
                        </div>
                      </div>
                    )}
                    {o.prize && Number(o.prize) > 0 && (
                      <p className="text-xs font-semibold text-brand-600 mt-1.5">🏆 Won: ₹{Number(o.prize).toLocaleString('en-IN')}</p>
                    )}
                  </div>
                  <div className="text-right shrink-0">
                    <span className={`badge rounded-full bg-${color}-500/15 text-${color}-400 border border-${color}-500/20 px-2.5 py-0.5 text-xs font-semibold`}>{label}</span>
                    {amount && <p className="text-sm font-bold text-gray-900 mt-1.5">₹{Number(amount).toLocaleString('en-IN')}</p>}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </AccountLayout>
  );
}
