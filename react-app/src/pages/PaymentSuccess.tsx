import { useLocation, Link } from 'react-router-dom';

export default function PaymentSuccess() {
  const { state } = useLocation();
  const order     = state?.order;

  return (
    <div className="min-h-screen flex items-center justify-center px-4 pt-24 pb-20">
      <div className="card p-8 max-w-md w-full text-center">
        {/* Success animation */}
        <div className="w-20 h-20 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto mb-6">
          <svg className="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/>
          </svg>
        </div>

        <h1 className="font-display font-bold text-2xl text-gray-900 mb-2">Payment Successful!</h1>
        <p className="text-gray-500 text-sm mb-6">Your Event tickets have been confirmed. Good luck! 🍀</p>

        {order && (
          <div className="bg-gray-50 border border-gray-200 rounded-xl p-4 text-left mb-6 space-y-2">
            {order.order_id && (
              <div className="flex justify-between text-sm">
                <span className="text-gray-500">Order ID</span>
                <span className="text-gray-900 font-mono text-xs">{order.order_id}</span>
              </div>
            )}
            {order.amount && (
              <div className="flex justify-between text-sm">
                <span className="text-gray-500">Amount Paid</span>
                <span className="text-gray-900 font-semibold">₹{Number(order.amount).toLocaleString('en-IN')}</span>
              </div>
            )}
          </div>
        )}

        <p className="text-xs text-gray-400 mb-6">A confirmation email has been sent to your registered email address.</p>

        <div className="flex flex-col gap-2">
          <Link to="/account/orders" className="btn-primary w-full justify-center">View My Tickets</Link>
          <Link to="/games"           className="btn-secondary w-full justify-center">Browse More Games</Link>
        </div>
      </div>
    </div>
  );
}
