import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import AccountLayout from '../../components/AccountLayout';
import LoadingSpinner from '../../components/LoadingSpinner';
import { getOrderDetail } from '../../api';

const STATUS: Record<string, [string, string]> = {
  CREATED:   ['Pending',   'yellow'],
  PAID:      ['Completed', 'emerald'],
  RELEASED:  ['Refunded',  'blue'],
  CANCELLED: ['Cancelled', 'red'],
  '0': ['Pending',   'yellow'],
  '1': ['Completed', 'emerald'],
  '2': ['Failed',    'red'],
};

interface TicketDetail {
  ticketNo: string;
  gameName: string;
  heading: string;
  logo: string;
  resultDate: string;
  price: string;
}

export default function OrderDetail() {
  const { id } = useParams<{ id: string }>();
  const [order, setOrder]   = useState<Record<string, any> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState('');

  useEffect(() => {
    if (!id) return;
    getOrderDetail(id)
      .then(r => setOrder(r.data.data?.order ?? null))
      .catch(() => setError('Order not found or you do not have access.'))
      .finally(() => setLoading(false));
  }, [id]);

  const formatDate = (d: string) => {
    if (!d) return '—';
    const dt = new Date(d);
    return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
  };

  if (loading) return <AccountLayout><LoadingSpinner size="lg" text="Loading order…" /></AccountLayout>;

  if (error || !order) return (
    <AccountLayout>
      <div className="card text-center py-16">
        <p className="text-4xl mb-3">⚠️</p>
        <p className="text-gray-500 mb-4">{error || 'Order not found.'}</p>
        <Link to="/account/orders" className="btn-primary inline-flex">Back to Orders</Link>
      </div>
    </AccountLayout>
  );

  const tickets: TicketDetail[] = order.tickets ?? [];
  const [label, color] = STATUS[String(order.paid_status)] ?? STATUS['CREATED'];

  return (
    <AccountLayout>
      <div className="flex items-center gap-3 mb-6">
        <Link to="/account/orders" className="text-gray-400 hover:text-gray-600 transition-colors">
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <h1 className="font-display font-bold text-2xl text-gray-900">Order #{order.id}</h1>
      </div>

      {/* Order summary card */}
      <div className="card p-5 mb-6 space-y-3 text-sm">
        <div className="flex items-center justify-between">
          <span className="text-gray-500">Status</span>
          <span className={`badge bg-${color}-500/15 text-${color}-400 border border-${color}-500/20`}>{label}</span>
        </div>
        {order.order_id && (
          <div className="flex items-center justify-between gap-4">
            <span className="text-gray-500 shrink-0">Order ID</span>
            <span className="text-gray-800 font-mono text-xs break-all text-right">{order.order_id}</span>
          </div>
        )}
        {order.createdAt && (
          <div className="flex items-center justify-between">
            <span className="text-gray-500">Date</span>
            <span className="text-gray-800">{formatDate(order.createdAt)}</span>
          </div>
        )}
        {order.total_price && (
          <div className="flex items-center justify-between border-t border-gray-100 pt-3 mt-1">
            <span className="text-gray-700 font-semibold">Total Paid</span>
            <span className="text-gray-900 font-bold text-base">₹{Number(order.total_price).toLocaleString('en-IN')}</span>
          </div>
        )}
      </div>

      {/* Ticket cards — same design as PaymentSuccess */}
      {tickets.length > 0 && (
        <div className="space-y-4">
          <h2 className="font-semibold text-gray-700 text-sm uppercase tracking-wide px-1">Your Tickets</h2>
          {tickets.map((t, i) => (
            <div key={i} className="rounded-2xl overflow-hidden shadow-lg border border-gray-200">
              {/* Logo banner */}
              {t.logo ? (
                <div
                  className="h-40 w-full bg-cover bg-center relative"
                  style={{ backgroundImage: `url(${t.logo})` }}
                >
                  <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
                  <div className="absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 text-right">
                    <p className="text-[10px] text-gray-500 font-medium uppercase tracking-wide">Ticket No.</p>
                    <p className="text-gray-900 font-bold text-xl font-mono leading-tight">{t.ticketNo}</p>
                  </div>
                  <div className="absolute bottom-3 left-4 right-4">
                    <p className="text-white font-bold text-lg leading-tight">{t.gameName}</p>
                    {t.heading && (
                      <p className="text-white/80 text-xs leading-snug line-clamp-2 mt-0.5">{t.heading}</p>
                    )}
                  </div>
                </div>
              ) : (
                <div className="bg-gradient-to-r from-brand-500 to-indigo-600 h-24 flex items-center justify-between px-5">
                  <div>
                    <p className="text-white font-bold text-lg">{t.gameName}</p>
                    {t.heading && <p className="text-white/80 text-xs mt-0.5 line-clamp-1">{t.heading}</p>}
                  </div>
                  <div className="bg-white/20 rounded-xl px-3 py-1.5 text-right">
                    <p className="text-white/70 text-[10px] uppercase tracking-wide">Ticket No.</p>
                    <p className="text-white font-bold text-xl font-mono">{t.ticketNo}</p>
                  </div>
                </div>
              )}

              {/* Details row */}
              <div className="bg-white px-4 py-3 flex items-center gap-4 text-sm">
                {t.resultDate && (
                  <div className="flex-1">
                    <p className="text-gray-400 text-[10px] uppercase tracking-wide">Draw Date</p>
                    <p className="text-gray-800 font-semibold">{formatDate(t.resultDate)}</p>
                  </div>
                )}
                {t.price && (
                  <div>
                    <p className="text-gray-400 text-[10px] uppercase tracking-wide">Price</p>
                    <p className="text-gray-800 font-semibold">₹{Number(t.price).toLocaleString('en-IN')}</p>
                  </div>
                )}
                <div className="flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg px-2 py-1">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7"/>
                  </svg>
                  <span className="text-xs font-semibold">Confirmed</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </AccountLayout>
  );
}
