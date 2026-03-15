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
        {/* Decorative lottery balls */}
        <div className="absolute top-1/3 right-12 w-14 h-14 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 opacity-20 animate-float pointer-events-none hidden lg:block"/>
        <div className="absolute top-1/2 left-16 w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 opacity-20 animate-float pointer-events-none hidden lg:block" style={{ animationDelay: '1.5s' }}/>
        <div className="absolute bottom-1/3 right-1/3 w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 opacity-20 animate-float pointer-events-none hidden lg:block" style={{ animationDelay: '3s' }}/>

        <div className="relative z-10 text-center px-4 max-w-4xl mx-auto pt-24">
          {/* Site logo */}
          <div className="flex justify-center mb-6">
            <img src={logo} alt="ItanagarChoice" className="h-32 w-auto object-contain drop-shadow-2xl"/>
          </div>

          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-semibold mb-4">
            <span className="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"/>
            Live Lottery Draws
          </div>

          <p className="text-sm font-semibold text-gray-400 uppercase tracking-[0.3em] mb-2">Contest for your chance to</p>
          <h1 className="font-display font-black text-6xl sm:text-7xl lg:text-8xl leading-none tracking-tight mb-4">
            <span className="bg-gradient-to-r from-brand-400 via-brand-500 to-orange-400 bg-clip-text text-transparent">
              BIG WIN
            </span>
          </h1>
          <h2 className="font-display font-bold text-2xl sm:text-3xl text-white mb-6">
            with ItanagarChoice
          </h2>

          <p className="text-base text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            India's most trusted online lottery platform. Browse games, pick your lucky numbers, and claim your prize today.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link to="/games" className="btn-primary text-base px-8 py-3.5">
              Participate Now
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

      {/* ── Trust Strip ─────────────────────────────────────────────── */}
      <div className="bg-dark-800 border-y border-white/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
          <div className="flex flex-wrap justify-center gap-x-12 gap-y-4">
            {[
              { icon: '🏛️', text: 'Govt. Licensed',    sub: 'Fully regulated'    },
              { icon: '⚡', text: 'Instant Payout',    sub: 'Same-day winnings'  },
              { icon: '🔐', text: 'SSL Secured',       sub: 'Bank-grade safety'  },
              { icon: '🎯', text: 'Daily Draws',       sub: 'Win every single day'},
              { icon: '🏆', text: 'Verified Winners',  sub: 'Transparent results' },
            ].map(({ icon, text, sub }) => (
              <div key={text} className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-lg shrink-0">{icon}</div>
                <div>
                  <p className="text-sm font-semibold text-white leading-tight">{text}</p>
                  <p className="text-xs text-gray-500">{sub}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

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
          <div className="text-center mb-14">
            <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-2">Simple &amp; Secure</p>
            <h2 className="font-display font-bold text-3xl text-white">How to Play</h2>
            <p className="text-gray-500 text-sm mt-2 max-w-md mx-auto">Follow these 3 easy steps to start winning today</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            {/* Connector lines (desktop only) */}
            <div className="hidden md:block absolute top-12 left-1/3 right-1/3 h-px bg-gradient-to-r from-brand-500/20 via-brand-500/50 to-brand-500/20 pointer-events-none"/>
            {[
              { step: '01', icon: '🎰', title: 'Choose a Game', desc: 'Register to ItanagarChoice & browse our selection of exciting lottery draws.' },
              { step: '02', icon: '🎫', title: 'Buy Your Tickets', desc: 'Pick your lucky numbers & complete your purchase securely in minutes.' },
              { step: '03', icon: '🏆', title: 'Win Prizes',       desc: 'Check results on draw day. Winners are notified instantly — prizes go to your wallet.' },
            ].map(({ step, icon, title, desc }) => (
              <div key={step} className="card p-8 text-center relative overflow-hidden group hover:border-brand-500/30 hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-300">
                <div className="absolute -top-6 -right-4 text-9xl font-display font-black text-white/[0.025] select-none pointer-events-none">{step}</div>
                {/* Step icon circle */}
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-4xl mx-auto mb-5 shadow-lg shadow-brand-500/30 group-hover:scale-110 transition-transform duration-300">
                  {icon}
                </div>
                <div className="inline-flex items-center gap-1 bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-bold px-2.5 py-0.5 rounded-full mb-3">
                  STEP {step}
                </div>
                <h3 className="font-display font-bold text-white text-lg mb-2">{title}</h3>
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

          {/* Top Winner Spotlight */}
          {results[0] && (
            <div className="card mb-6 overflow-hidden bg-gradient-to-r from-dark-700 to-dark-800 border-yellow-500/20 relative">
              <div className="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 pointer-events-none"/>
              <div className="relative flex flex-col sm:flex-row items-center gap-6 p-6">
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-4xl shrink-0 shadow-lg shadow-yellow-500/30">
                  🏆
                </div>
                <div className="flex-1 text-center sm:text-left">
                  <p className="text-xs text-yellow-400 font-bold uppercase tracking-widest mb-0.5">🎉 Top Winner of the Month</p>
                  <p className="font-display font-bold text-xl text-white">{results[0].name || results[0].game_name || 'Lucky Winner'}</p>
                  {results[0].createdAt && (
                    <p className="text-xs text-gray-500 mt-0.5">Won on {new Date(results[0].createdAt).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                  )}
                </div>
                <div className="text-center sm:text-right shrink-0">
                  <p className="text-xs text-gray-500 mb-0.5">Prize Won</p>
                  <p className="font-display font-black text-3xl bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent">
                    ₹{Number(results[0].prize || 0).toLocaleString('en-IN')}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Winners grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {results.slice(1, 7).map(r => (
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
