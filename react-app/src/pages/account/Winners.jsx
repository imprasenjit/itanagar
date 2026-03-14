import { useEffect, useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { getWinners } from '../../api';
import LoadingSpinner from '../../components/LoadingSpinner';

export default function Winners() {
  const [winners, setWinners] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getWinners().then(r => setWinners(r.data.data || [])).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <AccountLayout><LoadingSpinner size="lg" text="Loading winners…"/></AccountLayout>;

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-white mb-6">My Winnings</h1>
      {winners.length === 0 ? (
        <div className="card text-center py-16">
          <p className="text-4xl mb-3">🏆</p>
          <p className="text-gray-500">You haven't won yet — good luck next time!</p>
        </div>
      ) : (
        <div className="space-y-3">
          {winners.map(w => (
            <div key={w.id} className="card p-4 flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400/20 to-orange-500/20 border border-yellow-500/20 flex items-center justify-center text-2xl shrink-0">🏆</div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-white">{w.game_name || w.name || `Order #${w.order_id}`}</p>
                <p className="text-xs text-gray-500">Ticket #{w.ticket_number || w.tickets} · {w.createdAt ? new Date(w.createdAt).toLocaleDateString('en-IN') : ''}</p>
              </div>
              <div className="text-right shrink-0">
                <p className="text-xs text-gray-500">Prize</p>
                <p className="text-lg font-bold text-brand-400">₹{Number(w.prize || 0).toLocaleString('en-IN')}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </AccountLayout>
  );
}
