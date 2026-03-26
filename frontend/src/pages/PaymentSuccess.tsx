import { useLocation, Link } from 'react-router-dom';

interface TicketDetail {
  ticketNo: string;
  gameName: string;
  heading: string;
  logo: string;
  resultDate: string;
  price: string;
}

export default function PaymentSuccess() {
  const { state } = useLocation();
  const order     = state?.order;
  const tickets: TicketDetail[] = order?.tickets ?? [];

  const formatDate = (d: string) => {
    if (!d) return '—';
    const dt = new Date(d);
    return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
  };

  return (
    <div className="min-h-screen px-4 pt-24 pb-20 bg-gray-50">
      <div className="max-w-2xl mx-auto">
        {/* Success header */}
        <div className="card p-8 text-center mb-6">
          <div className="w-20 h-20 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto mb-4">
            <svg className="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h1 className="font-display font-bold text-2xl text-gray-900 mb-1">Payment Successful!</h1>
          <p className="text-gray-500 text-sm mb-4">Your tickets have been confirmed. Good luck! 🍀</p>

          {order && (
            <div className="bg-gray-50 border border-gray-200 rounded-xl p-4 text-left space-y-2 text-sm">
              {order.order_id && (
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500 shrink-0">Order ID</span>
                  <span className="text-gray-800 font-mono text-xs break-all text-right">{order.order_id}</span>
                </div>
              )}
              {order.payment_id && (
                <div className="flex justify-between gap-2">
                  <span className="text-gray-500 shrink-0">Payment ID</span>
                  <span className="text-gray-800 font-mono text-xs break-all text-right">{order.payment_id}</span>
                </div>
              )}
            </div>
          )}

          <p className="text-xs text-gray-400 mt-4">A confirmation email has been sent to your registered email address.</p>
        </div>

        {/* Ticket cards */}
        {tickets.length > 0 && (
          <div className="space-y-4 mb-6">
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
                    {/* Ticket badge — top right */}
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

                {/* Ticket details row */}
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

        {/* Actions */}
        <div className="flex flex-col gap-2">
          <Link to="/account/orders" className="btn-primary w-full justify-center">View My Tickets</Link>
          <Link to="/games"          className="btn-secondary w-full justify-center">Browse More Games</Link>
        </div>
      </div>
    </div>
  );
}

