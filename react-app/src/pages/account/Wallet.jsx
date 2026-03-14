import { useEffect, useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { getWallet, walletTopup, createPayment, confirmPayment, cancelPayment } from '../../api';
import { useToast } from '../../components/Toast';

export default function Wallet() {
  const addToast = useToast();
  const [data, setData]       = useState(null);
  const [topupAmount, setTopupAmount] = useState('');
  const [topping, setTopping] = useState(false);

  const fetchWallet = () => getWallet().then(r => setData(r.data.data)).catch(() => {});

  useEffect(() => { fetchWallet(); }, []);

  const handleTopup = async () => {
    const amount = Number(topupAmount);
    if (!amount || amount < 1) { addToast('Enter a valid amount', 'error'); return; }
    setTopping(true);
    try {
      const r = await walletTopup({ amount });
      if (!r.data.status) throw new Error(r.data.message);
      const { order_id, amount: rzpAmount, currency, key_id, user_name, user_email, user_mobile } = r.data.data;

      const options = {
        key: key_id,
        amount: rzpAmount,
        currency,
        order_id,
        name: 'ItanagarChoice Wallet',
        description: 'Wallet Top-up',
        prefill: { name: user_name, email: user_email, contact: user_mobile },
        theme: { color: '#f97316' },
        handler: async (response) => {
          const cr = await confirmPayment({ ...response, order_id, type: 'wallet' });
          if (cr.data.status) { addToast('Wallet topped up! 💳', 'success'); fetchWallet(); setTopupAmount(''); }
          else addToast(cr.data.message || 'Verification failed', 'error');
        },
        modal: { ondismiss: async () => { await cancelPayment({ order_id }).catch(() => {}); setTopping(false); } },
      };
      const rzp = new window.Razorpay(options);
      rzp.open();
    } catch (e) {
      addToast(e.message || 'Failed to initiate topup', 'error');
      setTopping(false);
    }
  };

  const balance  = data?.balance ?? 0;
  const history  = data?.history || [];

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-white mb-6">My Wallet</h1>

      {/* Balance card */}
      <div className="card p-5 mb-5 bg-gradient-to-br from-brand-500/10 to-brand-700/5 border-brand-500/20">
        <p className="text-xs text-brand-400 uppercase tracking-widest font-semibold mb-1">Available Balance</p>
        <p className="font-display font-black text-4xl text-white">₹{Number(balance).toLocaleString('en-IN')}</p>
      </div>

      {/* Top-up */}
      <div className="card p-5 mb-5">
        <h2 className="text-sm font-semibold text-white mb-3">Add Money to Wallet</h2>
        <div className="flex gap-2">
          <input type="number" value={topupAmount} onChange={e => setTopupAmount(e.target.value)}
            className="input flex-1" placeholder="Enter amount (₹)" min="1"/>
          <button onClick={handleTopup} disabled={topping} className="btn-primary px-6 shrink-0 disabled:opacity-40">
            {topping ? 'Processing…' : '+ Add Money'}
          </button>
        </div>
        <div className="flex gap-2 mt-2">
          {[100, 200, 500, 1000].map(a => (
            <button key={a} onClick={() => setTopupAmount(String(a))}
              className="px-3 py-1.5 rounded-lg text-xs font-medium bg-dark-600 border border-white/5 text-gray-400 hover:text-white hover:border-brand-500/50 transition-colors">
              ₹{a}
            </button>
          ))}
        </div>
      </div>

      {/* Transaction history */}
      <div className="card overflow-hidden">
        <div className="px-5 py-3 border-b border-white/5">
          <h2 className="text-sm font-semibold text-white">Transaction History</h2>
        </div>
        {history.length === 0 ? (
          <div className="text-center py-10 text-gray-500 text-sm">No transactions yet.</div>
        ) : (
          <div className="divide-y divide-white/5">
            {history.map(t => (
              <div key={t.id} className="flex items-center justify-between px-5 py-3.5">
                <div>
                  <p className="text-sm font-medium text-white">{t.description || t.type || 'Transaction'}</p>
                  <p className="text-xs text-gray-500">{t.createdDtm ? new Date(t.createdDtm).toLocaleDateString('en-IN') : ''}</p>
                </div>
                <span className={`text-sm font-bold ${t.type === 'credit' || Number(t.amount) > 0 ? 'text-emerald-400' : 'text-red-400'}`}>
                  {t.type === 'credit' || Number(t.amount) > 0 ? '+' : '-'}₹{Math.abs(Number(t.amount)).toLocaleString('en-IN')}
                </span>
              </div>
            ))}
          </div>
        )}
      </div>
    </AccountLayout>
  );
}
