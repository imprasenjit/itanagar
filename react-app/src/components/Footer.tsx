import { Link } from 'react-router-dom';
import logo from '../assets/logo.png';

export default function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="bg-dark-900 border-t border-white/5 mt-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="md:col-span-2">
            <div className="flex items-center gap-2.5 mb-4">
              <img src={logo} alt="ItanagarChoice" className="h-12 w-auto object-contain" />
            </div>
            <p className="text-sm text-gray-500 leading-relaxed max-w-xs">
              Your trusted platform for lottery tickets. Play responsibly and may luck be on your side.
            </p>
          </div>

          {/* Quick links */}
          <div>
            <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Quick links</h4>
            <ul className="space-y-2.5">
              {[['/', 'Home'], ['/games', 'Games'], ['/results', 'Results'], ['/faq', 'FAQ'], ['/contact', 'Contact']].map(([to, label]) => (
                <li key={to}><Link to={to} className="text-sm text-gray-500 hover:text-white transition-colors">{label}</Link></li>
              ))}
            </ul>
          </div>

          {/* Legal */}
          <div>
            <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Legal</h4>
            <ul className="space-y-2.5">
              {[['about', 'About Us'], ['terms', 'Terms & Conditions'], ['privacy', 'Privacy Policy'], ['refunds_cancellations', 'Refunds & Cancellations']].map(([type, label]) => (
                <li key={type}><Link to={`/page/${type}`} className="text-sm text-gray-500 hover:text-white transition-colors">{label}</Link></li>
              ))}
            </ul>
          </div>
        </div>

        <div className="mt-10 pt-6 border-t border-white/5 flex flex-col sm:flex-row justify-between items-center gap-3">
          <p className="text-xs text-gray-600">&copy; {year} ItanagarChoice. All rights reserved.</p>
          <p className="text-xs text-gray-600">Play responsibly. 18+ only.</p>
        </div>
      </div>
    </footer>
  );
}
