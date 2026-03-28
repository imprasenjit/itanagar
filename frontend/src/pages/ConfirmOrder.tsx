import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { getOrderConfirm, getCart, createPayment, confirmPayment, cancelPayment } from '../api';
import LoadingSpinner from '../components/LoadingSpinner';
import { useToast } from '../components/Toast';

import type { CartItem } from '../types';

interface GuestForm { fname: string; address: string; mobile: string; email: string; }
const EMPTY_GUEST: GuestForm = { fname: '', address: '', mobile: '', email: '' };

export default function ConfirmOrder() {
  const [items, setItems]       = useState<CartItem[]>([]);
  const [loading, setLoading]   = useState(true);
  const [paying, setPaying]     = useState(false);
  const [isGuest, setIsGuest]   = useState(false);
  const [guest, setGuest]       = useState<GuestForm>(EMPTY_GUEST);
  const [guestErrors, setGuestErrors] = useState<Partial<GuestForm>>({});
  const navigate  = useNavigate();
  const addToast  = useToast();

  useEffect(() => {
    getOrderConfirm()
      .then(r => {
        setItems(r.data.data?.cart || []);
        setIsGuest(false);
      })
      .catch(async (err) => {
        if (err?.response?.status === 401) {
          setIsGuest(true);
          try {
            const cr = await getCart();
            setItems(cr.data.data?.cart || []);
          } catch {
            navigate('/cart');
          }
        } else {
          navigate('/cart');
        }
      })
      .finally(() => setLoading(false));
  }, [navigate]);

  const total = items.reduce((sum, i) => sum + Number(i.total_price || 0), 0);

  const validateGuest = (): boolean => {
    const errs: Partial<GuestForm> = {};
    if (!guest.fname.trim()) errs.fname = 'Name is required';
    if (!guest.mobile.trim()) errs.mobile = 'Mobile is required';
    else if (!/^\d{10}$/.test(guest.mobile.trim())) errs.mobile = 'Enter a valid 10-digit mobile number';
    setGuestErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handlePay = async () => {
    if (isGuest && !validateGuest()) return;
    setPaying(true);
    try {
      const r = await createPayment(isGuest ? guest : undefined);
      if (!r.data.status) throw new Error(r.data.message);
      const { order_id, amount, currency, key_id, user_name, user_email, user_mobile } = r.data.data;

      const options = {
        key: key_id,
        amount,
        currency,
        order_id,
        name: 'ItanagarChoice',
        description: 'Event Ticket Purchase',
        prefill: { name: user_name, email: user_email, contact: user_mobile },
        theme: { color: '#f97316' },
        handler: async (response: {
          razorpay_payment_id: string;
          razorpay_order_id: string;
          razorpay_signature: string;
        }) => {
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
              navigate('/payment/failed', {
                state: {
                  reason:  cr.data.message || 'Payment verification failed.',
                  orderId: order_id,
                },
              });
            }
          } catch {
            navigate('/payment/failed', {
              state: {
                reason:  'An error occurred while confirming your payment.',
                orderId: order_id,
              },
            });
          }
        },
        modal: {
          ondismiss: async () => {
            await cancelPayment({ order_id }).catch(() => {});
            setPaying(false);
            navigate('/payment/failed', {
              state: {
                reason:  'You cancelled the payment.',
                orderId: order_id,
              },
            });
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
    } catch (e: unknown) {
      addToast((e as Error).message || 'Failed to initiate payment', 'error');
      setPaying(false);
    }
  };

  if (loading) return <LoadingSpinner size="lg" text="Preparing order…"/>;

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-20">
      {/* Razorpay script */}
      <script src="https://checkout.razorpay.com/v1/checkout.js" async/>

      <h1 className="font-display font-bold text-3xl text-gray-900 mb-8">Confirm Order</h1>

      {items.length === 0 ? (
        <div className="text-center py-24 card">
          <p className="text-gray-400 mb-4">No items to confirm.</p>
          <Link to="/cart" className="btn-primary">Go to Cart</Link>
        </div>
      ) : (
        <div className="space-y-4">
          <div className="card overflow-hidden">
            <div className="px-5 py-3 border-b border-gray-200 bg-gray-50">
              <p className="text-sm font-semibold text-gray-700">Order Items</p>
            </div>
            <div className="divide-y divide-gray-100">
              {items.map(item => (
                <div key={item.id} className="flex items-center justify-between px-5 py-3.5">
                  <div>
                    <p className="text-xs text-gray-900">Ticket #{item.ticket_no}</p>
                    <p className="text-sm font-medium text-gray-500">{item.name}</p>
                    
                  </div>
                  <p className="text-sm font-semibold text-gray-900">₹{Number(item.total_price || 0).toLocaleString('en-IN')}</p>
                </div>
              ))}
            </div>
          </div>

          {/* ── Guest checkout form ───────────────────────────────────── */}
          {isGuest && (
            <div className="card overflow-hidden">
              <div className="px-5 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <p className="text-sm font-semibold text-gray-700">Your Details</p>
                <Link to="/login" className="text-xs text-brand-600 hover:text-brand-700 transition-colors">
                  Already have an account? Sign In →
                </Link>
              </div>
              <div className="p-5 space-y-4">
                {/* Name */}
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1.5">
                    Full Name <span className="text-brand-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={guest.fname}
                    onChange={e => { setGuest(g => ({ ...g, fname: e.target.value })); setGuestErrors(er => ({ ...er, fname: '' })); }}
                    placeholder="John Doe"
                    className={`w-full bg-white border rounded-lg px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 transition-colors ${guestErrors.fname ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-brand-500 focus:border-brand-500'}`}
                  />
                  {guestErrors.fname && <p className="mt-1 text-xs text-red-400">{guestErrors.fname}</p>}
                </div>

                {/* Mobile */}
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1.5">
                    Mobile Number <span className="text-brand-500">*</span>
                  </label>
                  <input
                    type="tel"
                    value={guest.mobile}
                    onChange={e => { setGuest(g => ({ ...g, mobile: e.target.value.replace(/\D/g, '').slice(0, 10) })); setGuestErrors(er => ({ ...er, mobile: '' })); }}
                    placeholder="10-digit mobile number"
                    className={`w-full bg-white border rounded-lg px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 transition-colors ${guestErrors.mobile ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-brand-500 focus:border-brand-500'}`}
                  />
                  {guestErrors.mobile && <p className="mt-1 text-xs text-red-400">{guestErrors.mobile}</p>}
                </div>

                {/* Email */}
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1.5">
                    Email Address <span className="text-gray-400 font-normal">(optional)</span>
                  </label>
                  <input
                    type="email"
                    value={guest.email}
                    onChange={e => setGuest(g => ({ ...g, email: e.target.value }))}
                    placeholder="you@example.com"
                    className="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                  />
                </div>

                {/* Address */}
                <div>
                  <label className="block text-xs font-medium text-gray-400 mb-1.5">
                    Address <span className="text-gray-600 font-normal">(optional)</span>
                  </label>
                  <input
                    type="text"
                    value={guest.address}
                    onChange={e => setGuest(g => ({ ...g, address: e.target.value }))}
                    placeholder="Your address"
                    className="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                  />
                </div>
              </div>
            </div>
          )}

          <div className="card p-5">
            <div className="flex justify-between items-center text-sm mb-2">
              <span className="text-gray-400">Subtotal ({items.length} tickets)</span>
              <span className="text-white">₹{total.toLocaleString('en-IN')}</span>
            </div>
            <div className="flex justify-between items-center text-sm mb-4">
              <span className="text-gray-400">Processing fee</span>
              <span className="text-emerald-400 text-xs font-semibold">FREE</span>
            </div>
            <div className="flex justify-between items-center border-t border-gray-200 pt-4">
              <span className="font-bold text-white text-base">Total Payable</span>
              <span className="font-black text-brand-600 text-xl">₹{total.toLocaleString('en-IN')}</span>
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
