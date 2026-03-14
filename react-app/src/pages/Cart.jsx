import { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import LoadingSpinner from '../components/LoadingSpinner';

export default function Cart() {
  const { items, cartLoading, removeItem, fetchCart } = useCart();
  const navigate = useNavigate();

  useEffect(() => { fetchCart(); }, []);

  // DB fields: ticket_no, total_price (joined name from tbl_webs)
  const total = items.reduce((sum, i) => sum + Number(i.total_price || 0), 0);

  if (cartLoading) return <LoadingSpinner size="lg" text="Loading cart…"/>;

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <h1 className="font-display font-bold text-3xl text-white mb-8">Your Cart</h1>

      {items.length === 0 ? (
        <div className="text-center py-24 card">
          <p className="text-5xl mb-4">🛒</p>
          <p className="text-gray-400 mb-6">Your cart is empty.</p>
          <Link to="/games" className="btn-primary">Browse Games</Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Items */}
          <div className="lg:col-span-2 space-y-3">
            {items.map(item => (
              <div key={item.id} className="card p-4 flex items-center gap-4">
                <div className="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center text-brand-400 text-lg shrink-0">🎫</div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-white">{item.name}</p>
                  <p className="text-xs text-gray-500">Ticket #{item.ticket_no}</p>
                </div>
                <div className="text-right shrink-0">
                  <p className="text-sm font-bold text-white">₹{Number(item.total_price || 0).toLocaleString('en-IN')}</p>
                </div>
                <button
                  onClick={() => removeItem(item.id)}
                  className="p-1.5 rounded-lg text-gray-600 hover:text-red-400 hover:bg-red-500/10 transition-colors shrink-0">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            ))}
          </div>

          {/* Summary */}
          <div className="lg:col-span-1">
            <div className="card p-5 sticky top-24">
              <h2 className="font-semibold text-white text-base mb-4">Order Summary</h2>
              <div className="space-y-2 mb-4">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-400">{items.length} ticket{items.length !== 1 ? 's' : ''}</span>
                  <span className="text-white font-medium">₹{total.toLocaleString('en-IN')}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-400">Processing fee</span>
                  <span className="text-emerald-400 text-xs font-semibold">FREE</span>
                </div>
              </div>
              <div className="border-t border-white/5 pt-3 mb-5">
                <div className="flex justify-between">
                  <span className="font-semibold text-white">Total</span>
                  <span className="font-bold text-brand-400 text-lg">₹{total.toLocaleString('en-IN')}</span>
                </div>
              </div>
              <button onClick={() => navigate('/order/confirm')} className="btn-primary w-full">
                Proceed to Checkout →
              </button>
              <Link to="/games" className="btn-secondary w-full mt-2 text-center block">
                Continue Shopping
              </Link>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
