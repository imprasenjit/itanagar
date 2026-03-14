import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import AccountLayout from '../../components/AccountLayout';
import { getOrders } from '../../api';
import LoadingSpinner from '../../components/LoadingSpinner';

import type { CartItem } from '../../types';

const STATUS: Record<number, [string, string]> = { 0: ['Pending', 'yellow'], 1: ['Completed', 'emerald'], 2: ['Failed', 'red'], 3: ['Refunded', 'blue'] };

export default function OrderHistory() {
  const [orders, setOrders]   = useState<Record<string, any>[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getOrders().then(r => setOrders(r.data.data?.orders || [])).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <AccountLayout><LoadingSpinner size="lg" text="Loading orders…"/></AccountLayout>;

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-white mb-6">My Orders</h1>
      {orders.length === 0 ? (
        <div className="card text-center py-16">
          <p className="text-4xl mb-3">📋</p>
          <p className="text-gray-500 mb-4">No orders yet.</p>
          <Link to="/games" className="btn-primary inline-flex">Browse Games</Link>
        </div>
      ) : (
        <div className="space-y-3">
          {orders.map(o => {
            const [label, color] = STATUS[o.paid_status] || STATUS[0];
            return (
              <div key={o.id} className="card p-4">
                <div className="flex items-start justify-between gap-4">
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-white">{o.name || o.game_name || `Order #${o.id}`}</p>
                    <p className="text-xs text-gray-500 mt-0.5">
                      {o.tickets && <span>Tickets: {Array.isArray(o.tickets) ? o.tickets.join(', ') : o.tickets}</span>}
                      {o.createdAt && <span> · {new Date(o.createdAt).toLocaleDateString('en-IN')}</span>}
                    </p>
                    {o.prize && Number(o.prize) > 0 && (
                      <p className="text-xs font-semibold text-brand-400 mt-1">🏆 Won: ₹{Number(o.prize).toLocaleString('en-IN')}</p>
                    )}
                  </div>
                  <div className="text-right shrink-0">
                    <span className={`badge bg-${color}-500/15 text-${color}-400 border border-${color}-500/20`}>{label}</span>
                    {o.amount && <p className="text-sm font-bold text-white mt-1.5">₹{Number(o.amount).toLocaleString('en-IN')}</p>}
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
