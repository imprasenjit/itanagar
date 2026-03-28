import { type ReactNode } from 'react';
import { NavLink, Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import LoadingSpinner from './LoadingSpinner';

const links = [
  { to: '/account/profile',     icon: '👤', label: 'Profile' },
  { to: '/account/orders',      icon: '📋', label: 'My Orders' },
  // { to: '/account/wallet',      icon: '💳', label: 'Wallet' },
  { to: '/account/winners',     icon: '🏆', label: 'Winners' },
  // { to: '/account/refunds',     icon: '↩️', label: 'Refunds' },
  // { to: '/account/withdrawals', icon: '💸', label: 'Withdrawals' },
  // { to: '/account/transfers',   icon: '🔄', label: 'Transfers' },
];

export default function AccountLayout({ children }: { children: ReactNode }) {
  const { user, loading } = useAuth();

  if (loading) return <LoadingSpinner size="lg" text="Loading..." />;
  if (!user)   return <Navigate to="/login" replace />;

  return (
    <div className="min-h-screen pt-4 pb-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col lg:flex-row gap-6">
          {/* Sidebar */}
          <aside className="lg:w-60 shrink-0">
            <div className="card p-3 sticky top-24">
              {/* User summary */}
              <div className="flex items-center gap-3 px-3 py-3 mb-2">
                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-base">
                  {user.name?.charAt(0).toUpperCase()}
                </div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-gray-900 truncate">{user.name}</p>
                  <p className="text-xs text-gray-500 truncate">{user.email}</p>
                </div>
              </div>
              <div className="border-t border-gray-200 my-1"/>
              {links.map(({ to, icon, label }) => (
                <NavLink key={to} to={to}
                  className={({ isActive }) =>
                    `flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
                      isActive ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`
                  }>
                  <span className="text-base">{icon}</span>
                  {label}
                </NavLink>
              ))}
            </div>
          </aside>

          {/* Main */}
          <main className="flex-1 min-w-0">{children}</main>
        </div>
      </div>
    </div>
  );
}
