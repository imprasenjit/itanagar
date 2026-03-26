import { Link } from 'react-router-dom';
import logo from '../assets/logo.png';

export default function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="bg-white border-t border-gray-200 mt-7">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
        <div className="flex flex-col items-center gap-2">
          {/* Logo */}
          <img src={logo} alt="ItanagarChoice" className="h-20 w-auto object-contain" />
          <p className='text-gray-500'>Your Ticketing partner since 2024</p>
          {/* Social icons */}
          <div className="flex items-center gap-5">
            {/* Facebook */}
            <a href="https://facebook.com/itanagarchoice" target="_blank" rel="noopener noreferrer" aria-label="Facebook"
              className="text-gray-400 hover:text-brand-600 transition-colors">
              <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
            </a>
            {/* WhatsApp */}
            <a href="https://wa.me/918974558500" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"
              className="text-gray-400 hover:text-brand-600 transition-colors">
              <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
            {/* YouTube */}
            <a href="https://youtube.com/@itanagarchoice" target="_blank" rel="noopener noreferrer" aria-label="YouTube"
              className="text-gray-400 hover:text-brand-600 transition-colors">
              <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
          </div>

          {/* Legal links */}
          <div className="flex flex-wrap justify-center gap-x-4 gap-y-1">
            {[['/about', 'About Us'], ['/terms', 'Terms'], ['/privacy', 'Privacy'], ['/refunds', 'Refunds']].map(([to, label]) => (
              <Link key={to} to={to} className="text-xs text-gray-500 hover:text-brand-600 transition-colors">{label}</Link>
            ))}
          </div>

          {/* Copyright */}
          <div className="border-t border-gray-200 w-full pt-5 text-center">
            <p className="text-xs text-gray-500">&copy; {year} ItanagarChoice. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>
  );
}
