export default function ScamSecurity() {
  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <h1 className="font-display font-bold text-3xl text-gray-900 mb-6">Scam Security</h1>

      <div className="card p-6 sm:p-8 space-y-6">
        <p className="text-gray-700 text-base leading-relaxed">
          <span className="font-semibold text-brand-600">Itanagar Choice</span> strongly warns users against fraudulent activities.
        </p>

        <div>
          <h2 className="font-display font-semibold text-lg text-gray-900 mb-3">Important Safety Guidelines</h2>
          <ul className="space-y-3">
            {[
              <>Only purchase tickets from the official website <a href="https://theitanagarchoice.com" className="text-brand-600 font-medium hover:underline" target="_blank" rel="noopener noreferrer">theitanagarchoice.com</a></>,
              'Never trust unofficial agents or third-party sellers',
              'Always verify your ticket using the Check Ticket feature',
              'Report suspicious activity immediately',
            ].map((item, i) => (
              <li key={i} className="flex items-start gap-3">
                <svg className="w-5 h-5 text-brand-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span className="text-gray-700">{item}</span>
              </li>
            ))}
          </ul>
        </div>

        <p className="text-gray-700 font-medium text-base">
          Your safety and trust are our priority.
        </p>
      </div>
    </div>
  );
}
