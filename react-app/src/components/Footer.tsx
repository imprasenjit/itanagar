import { Link } from 'react-router-dom';
import logo from '../assets/logo.png';

export default function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="bg-gray-900 border-t border-white/5 mt-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="md:col-span-2">
            <div className="flex items-center gap-2.5 mb-4">
              <img src={logo} alt="ItanagarChoice" className="h-12 w-auto object-contain" />
            </div>
            {/* <p className="text-sm text-gray-400 leading-relaxed max-w-xs">
             If you have any questions, concerns, or need support, you can contact the Itanagar Choice support team.
Support may be available through: 
            </p>
            <p className="text-sm text-gray-400 leading-relaxed max-w-xs">
Phone: 8974558500 <br/>
Email: theitanagarchoice.com <br/>
WhatsApp: 8974558500 <br/>
Our team will respond as soon as possible.
            </p> */}
          </div>

          {/* Quick links */}
          <div>
            {/* <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Scam Security</h4>

<p className="text-sm text-gray-400 leading-relaxed">Itanagar Choice strongly warns users against fraudulent activities.</p>
<ul className="mt-2 space-y-1.5 text-sm text-gray-400 leading-relaxed">
  <li>• Only purchase tickets from the official website <span className="text-white font-medium">theitanagarchoice.com</span></li>
  <li>• Never trust unofficial agents or third-party sellers</li>
  <li>• Always verify your ticket using the Check Ticket feature</li>
  <li>• Report suspicious activity immediately</li>
</ul> */}
<p className="mt-2 text-sm text-gray-500 italic">Your safety and trust are our priority.</p>
          </div>

          {/* Legal */}
          <div>
            <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Legal</h4>
            <ul className="space-y-2.5">
              {[['/about', 'About Us'], ['/terms', 'Terms & Conditions'], ['/privacy', 'Privacy Policy'], ['/refunds', 'Refunds & Cancellations']].map(([to, label]) => (
                <li key={to}><Link to={to} className="text-sm text-gray-400 hover:text-white transition-colors">{label}</Link></li>
              ))}
            </ul>
          </div>
        </div>

        <div className="mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-3">
          <p className="text-xs text-gray-500">&copy; {year} ItanagarChoice. All rights reserved.</p>
          <p className="text-xs text-gray-500">Play responsibly. 18+ only.</p>
        </div>
      </div>
    </footer>
  );
}
