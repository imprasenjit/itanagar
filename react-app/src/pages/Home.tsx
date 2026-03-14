import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getHome } from '../api';
import logo from '../assets/logo.png';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';

import type { Game } from '../types';

export default function Home() {
  const [data, setData]  = useState<{ games: Game[]; results: Record<string, any>[]; stats: Record<string, any> } | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getHome()
      .then(r => setData(r.data.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  const games   = data?.games   || [];
  const results = data?.results || [];
  const stats   = data?.stats   || {};

  return (
    <>
      {/* ── Hero ────────────────────────────────────────────────────────── */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-hero-pattern">
        {/* Animated orbs */}
        <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-600/10 rounded-full blur-[100px] animate-pulse-slow pointer-events-none"/>
        <div className="absolute bottom-1/4 right-1/4 w-72 h-72 bg-blue-600/10 rounded-full blur-[80px] animate-pulse-slow delay-1000 pointer-events-none"/>

        <div className="relative z-10 text-center px-4 max-w-4xl mx-auto pt-24">
          {/* Site logo */}
          <div className="flex justify-center mb-6">
            <img src={logo} alt="ItanagarChoice" className="h-32 w-auto object-contain drop-shadow-2xl"/>
          </div>

          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-semibold mb-6">
            <span className="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"/>
            Live Lottery Draws
          </div>

          <h1 className="font-display font-black text-5xl sm:text-6xl lg:text-7xl text-white leading-none tracking-tight mb-6">
            Win Big with<br/>
            <span className="bg-gradient-to-r from-brand-400 via-brand-500 to-orange-400 bg-clip-text text-transparent">
              ItanagarChoice
            </span>
          </h1>

          <p className="text-lg text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            India's most trusted online lottery platform. Browse games, pick your lucky numbers, and claim your prize today.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link to="/games" className="btn-primary text-base px-8 py-3.5">
              Browse Games
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </Link>
            <Link to="/results" className="btn-secondary text-base px-8 py-3.5">View Results</Link>
          </div>

          {/* Stats */}
          {(stats.games || stats.users || stats.paid) && (
            <div className="mt-16 grid grid-cols-3 gap-4 max-w-lg mx-auto">
              {[
                { label: 'Active Games',    value: stats.games },
                { label: 'Happy Players',   value: stats.users ? `${Number(stats.users).toLocaleString()}+` : null  },
                { label: 'Tickets Issued',  value: stats.paid  ? `${Number(stats.paid).toLocaleString()}+`  : null  },
              ].filter(s => s.value).map(s => (
                <div key={s.label} className="card p-4 text-center">
                  <p className="text-2xl font-display font-bold text-white">{s.value}</p>
                  <p className="text-xs text-gray-500 mt-0.5">{s.label}</p>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-gray-600 animate-bounce">
          <p className="text-xs">Scroll</p>
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/></svg>
        </div>
      </section>

      {/* ── Active Games ────────────────────────────────────────────────── */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div className="flex items-end justify-between mb-8">
          <div>
            <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Available Now</p>
            <h2 className="font-display font-bold text-3xl text-white">Active Games</h2>
          </div>
          <Link to="/games" className="text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors">View all →</Link>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          {loading
            ? Array.from({ length: 4 }).map((_, i) => <SkeletonCard key={i}/>)
            : games.slice(0, 8).map(g => <GameCard key={g.id} game={g}/>)
          }
        </div>
      </section>

      {/* ── How it works ────────────────────────────────────────────────── */}
      <section className="bg-dark-800/50 py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Simple &amp; Secure</p>
            <h2 className="font-display font-bold text-3xl text-white">How it Works</h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {[
              { step: '01', title: 'Choose a Game', desc: 'Browse our selection of exciting lottery games and pick the one that suits you.' },
              { step: '02', title: 'Buy Your Tickets', desc: 'Select your lucky numbers or let us pick for you. Add tickets to your cart and pay securely.' },
              { step: '03', title: 'Win Prizes', desc: 'Check results on draw day. Winners are notified instantly and prizes credited to your wallet.' },
            ].map(({ step, title, desc }) => (
              <div key={step} className="card p-6 relative overflow-hidden">
                <div className="absolute -top-4 -right-4 text-7xl font-display font-black text-white/[0.03] select-none">{step}</div>
                <div className="w-10 h-10 rounded-xl bg-brand-500/15 border border-brand-500/20 flex items-center justify-center text-brand-400 font-display font-bold text-sm mb-4">{step}</div>
                <h3 className="font-semibold text-white text-base mb-2">{title}</h3>
                <p className="text-sm text-gray-500 leading-relaxed">{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Recent Winners ──────────────────────────────────────────────── */}
      {results.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="flex items-end justify-between mb-8">
            <div>
              <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Latest Wins</p>
              <h2 className="font-display font-bold text-3xl text-white">Recent Winners</h2>
            </div>
            <Link to="/results" className="text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors">All results →</Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {results.slice(0, 6).map(r => (
              <div key={r.id} className="card p-4 flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-dark-900 font-bold text-lg shrink-0">🏆</div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-white truncate">{r.name || r.game_name}</p>
                  <p className="text-xs text-gray-500">Prize: <span className="text-brand-400 font-semibold">₹{Number(r.prize || 0).toLocaleString('en-IN')}</span></p>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}
    </>
  );
}
