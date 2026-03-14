import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { getOrderConfirm, createPayment, confirmPayment, cancelPayment } from '../api';
import LoadingSpinner from '../components/LoadingSpinner';
import { useToast } from '../components/Toast';

export default function ConfirmOrder() {
  const [items, setItems]     = useState([]);
  const [loading, setLoading] = useState(true);
  const [paying, setPaying]   = useState(false);
  const navigate  = useNavigate();
  const addToast  = useToast();

  useEffect(() => {
    getOrderConfirm()
      // order_confirm response: { status, data: { items: [...], total, isGuest } }
      .then(r => setItems(r.data.data?.items || []))
      .catch(() => navigate('/cart'))
      .finally(() => setLoading(false));
  }, [navigate]);

  const total = items.reduce((sum, i) => sum + Number(i.total_price || 0), 0);

  const handlePay = async () => {
    setPaying(true);
    try {
      const r = await createPayment();
      if (!r.data.status) throw new Error(r.data.message);
      const { order_id, amount, currency, key_id, user_name, user_email, user_mobile } = r.data.data;

      const options = {
        key: key_id,
        amount,
        currency,
        order_id,
        name: 'ItanagarChoice',
        description: 'Lottery Ticket Purchase',
        prefill: { name: user_name, email: user_email, contact: user_mobile },
        theme: { color: '#f97316' },
        handler: async (response) => {
          try {
            const cr = await confirmPayment({
              razorpay_order_id:   response.razorpay_order_id,
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_signature:  response.razorpay_signature,
              order_id,
            });
            if (cr.data.status) {
              addToast('Payment successful! 🎉', 'success');
              navigate('/payment/success', { state: { order: cr.data.data } });
            } else {
              addToast(cr.data.message || 'Payment verification failed', 'error');
            }
          } catch {
            addToast('Payment confirmation failed', 'error');
          }
        },
        modal: {
          ondismiss: async () => {
            await cancelPayment({ order_id }).catch(() => {});
            addToast('Payment cancelled.', 'info');
            setPaying(false);
          },
        },
      };

      if (!window.Razorpay) {
        addToast('Payment gateway not loaded. Refresh and try again.', 'error');
        setPaying(false);
        return;
      }
      const rzp = new window.Razorpay(options);
      rzp.open();
    } catch (e) {
      addToast(e.message || 'Failed to initiate payment', 'error');
      setPaying(false);
    }
  };

  if (loading) return <LoadingSpinner size="lg" text="Preparing order…"/>;

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      {/* Razorpay script */}
      <script src="https://checkout.razorpay.com/v1/checkout.js" async/>

      <h1 className="font-display font-bold text-3xl text-white mb-8">Confirm Order</h1>

      {items.length === 0 ? (
        <div className="text-center py-24 card">
          <p className="text-gray-400 mb-4">No items to confirm.</p>
          <Link to="/cart" className="btn-primary">Go to Cart</Link>
        </div>
      ) : (
        <div className="space-y-4">
          <div className="card overflow-hidden">
            <div className="px-5 py-3 border-b border-white/5 bg-dark-600/30">
              <p className="text-sm font-semibold text-gray-300">Order Items</p>
            </div>
            <div className="divide-y divide-white/5">
              {items.map(item => (
                <div key={item.id} className="flex items-center justify-between px-5 py-3.5">
                  <div>
                    <p className="text-sm font-medium text-white">{item.name}</p>
                    <p className="text-xs text-gray-500">Ticket #{item.ticket_no}</p>
                  </div>
                  <p className="text-sm font-semibold text-white">₹{Number(item.total_price || 0).toLocaleString('en-IN')}</p>
                </div>
              ))}
            </div>
          </div>

          <div className="card p-5">
            <div className="flex justify-between items-center text-sm mb-2">
              <span className="text-gray-400">Subtotal ({items.length} tickets)</span>
              <span className="text-white">₹{total.toLocaleString('en-IN')}</span>
            </div>
            <div className="flex justify-between items-center text-sm mb-4">
              <span className="text-gray-400">Processing fee</span>
              <span className="text-emerald-400 text-xs font-semibold">FREE</span>
            </div>
            <div className="flex justify-between items-center border-t border-white/5 pt-4">
              <span className="font-bold text-white text-base">Total Payable</span>
              <span className="font-black text-brand-400 text-xl">₹{total.toLocaleString('en-IN')}</span>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row gap-3">
            <Link to="/cart" className="btn-secondary flex-1 justify-center">← Back to Cart</Link>
            <button onClick={handlePay} disabled={paying} className="btn-primary flex-1">
              {paying ? 'Processing…' : `Pay ₹${total.toLocaleString('en-IN')} →`}
            </button>
          </div>

          {/* Security badge */}
          <div className="flex items-center justify-center gap-2 text-xs text-gray-600">
            <svg className="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd"/></svg>
            Secured by Razorpay — 256-bit SSL encryption
          </div>
        </div>
      )}
    </div>
  );
}
