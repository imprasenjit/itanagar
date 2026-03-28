import { useLocation, Link, useNavigate } from 'react-router-dom';

export default function PaymentFailed() {
  const { state }   = useLocation();
  const navigate    = useNavigate();
  const reason: string  = state?.reason  ?? 'Payment could not be completed.';
  const orderId: string = state?.orderId ?? '';
  const errorCode: string = state?.errorCode ?? '';

  return (
    <div className="min-h-screen px-4 pt-4 pb-20 bg-gray-50">
      <div className="max-w-lg mx-auto">

        {/* Failed header */}
        <div className="card p-8 text-center mb-6">
          {/* X icon */}
          <div className="w-20 h-20 rounded-full bg-red-500/10 border border-red-500/25 flex items-center justify-center mx-auto mb-4">
            <svg className="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>

          <h1 className="font-display font-bold text-2xl text-gray-900 mb-1">Payment Failed</h1>
          <p className="text-gray-500 text-sm mb-5">
            Don't worry — your cart is still saved. You can try again.
          </p>

          {/* Error details */}
          <div className="bg-red-50 border border-red-200 rounded-xl p-4 text-left space-y-2 text-sm mb-6">
            <div className="flex gap-2">
              <span className="text-red-400 shrink-0">
                <svg className="w-4 h-4 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd"/>
                </svg>
              </span>
              <p className="text-red-700">{reason}</p>
            </div>

            {(orderId || errorCode) && (
              <div className="pt-2 border-t border-red-200 space-y-1.5">
                {orderId && (
                  <div className="flex justify-between gap-2">
                    <span className="text-gray-500 shrink-0">Order ID</span>
                    <span className="text-gray-700 font-mono text-xs break-all text-right">{orderId}</span>
                  </div>
                )}
                {errorCode && (
                  <div className="flex justify-between gap-2">
                    <span className="text-gray-500 shrink-0">Error Code</span>
                    <span className="text-gray-700 font-mono text-xs text-right">{errorCode}</span>
                  </div>
                )}
              </div>
            )}
          </div>

          {/* Actions */}
          <div className="flex flex-col sm:flex-row gap-3">
            <button
              onClick={() => navigate('/order/confirm')}
              className="btn-primary flex-1"
            >
              Try Again
            </button>
            <Link to="/cart" className="btn-secondary flex-1 text-center">
              Back to Cart
            </Link>
          </div>
        </div>

        {/* Help note */}
        <p className="text-center text-xs text-gray-400">
          If money was deducted, it will be refunded within 5–7 business days.{' '}
          <Link to="/contact" className="text-brand-600 hover:underline">Contact support</Link>
        </p>
      </div>
    </div>
  );
}
