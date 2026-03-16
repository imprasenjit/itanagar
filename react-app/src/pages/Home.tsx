import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getHome } from '../api';
import logo from '../assets/logo.png';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';

import type { Game } from '../types';

function stripHtml(s: string) { return s ? s.replace(/<[^>]*>/g, '').trim() : ''; }



export default function Home() {
  const [data, setData]       = useState<{ games: Game[]; results: Record<string, unknown>[]; stats: Record<string, unknown> } | null>(null);
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

  const featuredGame = !loading && games.length > 0 ? games[0] : null;
  const gridGames    = !loading ? games : [];

  return (
    <>
   

        

      

      {/* ── ACTIVE GAMES GRID ─────────────────────────────────────────────── */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="flex items-end justify-between mb-8">
          <div>
            <p className="text-xs text-brand-600 font-semibold uppercase tracking-widest mb-1">Available Now</p>
            <h2 className="font-display font-bold text-3xl text-gray-900">Active Games</h2>
          </div>
          <Link to="/games" className="hidden sm:inline-flex items-center gap-1 text-sm text-brand-600 hover:text-brand-700 font-medium transition-colors">
            View all
            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/></svg>
          </Link>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          {loading
            ? Array.from({ length: 4 }).map((_, i) => <SkeletonCard key={i}/>)
            : gridGames.map(g => <GameCard key={g.id} game={g}/>)
          }
        </div>
        <div className="mt-6 text-center sm:hidden">
          <Link to="/games" className="text-sm text-brand-600 hover:text-brand-700 font-medium transition-colors">View all games →</Link>
        </div>
      </section>

      {/* ── HOW TO PLAY ───────────────────────────────────────────────────── */}
      <section className="relative py-24 overflow-hidden">
        <div className="absolute inset-0 bg-gray-100/70 pointer-events-none"/>
        <div
          className="absolute inset-0 pointer-events-none"
          style={{ backgroundImage: 'linear-gradient(rgba(225,29,38,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(225,29,38,0.05) 1px, transparent 1px)', backgroundSize: '48px 48px' }}
        />
        <div className="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-brand-300/40 to-transparent pointer-events-none"/>
        <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-brand-300/40 to-transparent pointer-events-none"/>

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-14">
            <p className="text-xs text-brand-600 font-semibold uppercase tracking-widest mb-2">Simple &amp; Secure</p>
            <h2 className="font-display font-bold text-3xl text-gray-900">How to Play</h2>
            <p className="text-gray-500 text-sm mt-2 max-w-md mx-auto">Follow these 3 easy steps to start winning today</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div className="hidden md:block absolute top-12 left-1/3 right-1/3 h-px bg-gradient-to-r from-brand-500/20 via-brand-500/50 to-brand-500/20 pointer-events-none"/>
            {[
              { step: '01', icon: '🎰', title: 'Choose a Game',    desc: 'Register to ItanagarChoice & browse our selection of exciting Event draws.' },
              { step: '02', icon: '🎫', title: 'Buy Your Tickets', desc: 'Pick your lucky tickets & complete your purchase securely in minutes.' },
              { step: '03', icon: '🏆', title: 'Win Prizes',       desc: 'Check results on draw day. Winners are notified instantly — prizes go to your wallet.' },
            ].map(({ step, icon, title, desc }) => (
              <div key={step} className="card p-8 text-center relative overflow-hidden group hover:border-brand-400 hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-300">
                <div className="absolute -top-6 -right-4 text-9xl font-display font-black text-gray-900/[0.04] select-none pointer-events-none">{step}</div>
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-4xl mx-auto mb-5 shadow-lg shadow-brand-500/30 group-hover:scale-110 transition-transform duration-300">
                  {icon}
                </div>
                <div className="inline-flex items-center gap-1 bg-brand-50 border border-brand-200 text-brand-600 text-xs font-bold px-2.5 py-0.5 rounded-full mb-3">
                  STEP {step}
                </div>
                <h3 className="font-display font-bold text-gray-900 text-lg mb-2">{title}</h3>
                <p className="text-sm text-gray-500 leading-relaxed">{desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── RECENT WINNERS ────────────────────────────────────────────────── */}
      {results.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="flex items-end justify-between mb-8">
            <div>
              <p className="text-xs text-brand-600 font-semibold uppercase tracking-widest mb-1">Latest Wins</p>
              <h2 className="font-display font-bold text-3xl text-gray-900">Recent Winners</h2>
            </div>
            <Link to="/results" className="text-sm text-brand-600 hover:text-brand-700 font-medium transition-colors hidden sm:block">All results →</Link>
          </div>

          {results[0] && (
            <div className="card mb-6 overflow-hidden relative border-yellow-500/20">
              <div className="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 pointer-events-none"/>
              <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-yellow-500/40 to-transparent pointer-events-none"/>
              <div className="relative flex flex-col sm:flex-row items-center gap-6 p-6">
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-4xl shrink-0 shadow-lg shadow-yellow-500/30">🏆</div>
                <div className="flex-1 text-center sm:text-left">
                  <p className="text-xs text-yellow-600 font-bold uppercase tracking-widest mb-0.5">🎉 Top Winner</p>
                  <p className="font-display font-bold text-xl text-gray-900">{String(results[0].name || results[0].game_name || 'Lucky Winner')}</p>
                  {results[0].createdAt != null && (
                    <p className="text-xs text-gray-500 mt-0.5">Won on {new Date(String(results[0].createdAt)).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
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

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {results.slice(1, 7).map((r, i) => (
              <div key={String(r.id ?? i)} className="card p-4 flex items-center gap-4 hover:border-yellow-300 transition-colors">
                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400/20 to-orange-500/20 border border-yellow-400/30 flex items-center justify-center text-lg shrink-0">🏆</div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-gray-900 truncate">{String(r.name || r.game_name || '—')}</p>
                  <p className="text-xs text-gray-500">Prize: <span className="text-yellow-400 font-semibold">₹{Number(r.prize || 0).toLocaleString('en-IN')}</span></p>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* ── BOTTOM CTA ────────────────────────────────────────────────────── */}
      <section className="relative py-24 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-red-50 via-gray-50 to-white pointer-events-none"/>
        <div className="absolute top-1/4 left-1/4 w-80 h-80 bg-brand-100/60 rounded-full blur-[100px] pointer-events-none animate-pulse-slow"/>
        <div className="absolute bottom-1/4 right-1/4 w-64 h-64 bg-orange-100/60 rounded-full blur-[80px] pointer-events-none animate-pulse-slow" style={{ animationDelay: '1.5s' }}/>
        <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-300/40 to-transparent pointer-events-none"/>

        <div className="relative max-w-3xl mx-auto px-4 text-center">
          {/* Mini Event balls row */}
          <div className="flex justify-center gap-3 mb-8">
            {([{ n: 7, cls: 'from-brand-400 to-brand-600' }, { n: 14, cls: 'from-blue-400 to-blue-600' }, { n: 22, cls: 'from-emerald-400 to-emerald-600' }, { n: 35, cls: 'from-yellow-400 to-orange-500' }, { n: 42, cls: 'from-purple-400 to-purple-600' }] as const).map(({ n, cls }) => (
              <div key={n} className={`w-12 h-12 rounded-full bg-gradient-to-br ${cls} flex items-center justify-center shadow-lg text-white font-black text-sm opacity-80`}>
                {n}
              </div>
            ))}
          </div>

          <h2 className="font-display font-black text-4xl sm:text-5xl text-gray-900 mb-4 leading-tight">
            Ready to Try Your{' '}
            <span className="bg-gradient-to-r from-brand-500 to-orange-500 bg-clip-text text-transparent">Luck?</span>
          </h2>
          <p className="text-gray-600 text-base mb-8 max-w-xl mx-auto leading-relaxed">
            Join thousands of winners on India's most trusted Event platform. Your jackpot is just a ticket away.
          </p>
          <Link to="/games" className="inline-flex items-center gap-2 btn-primary text-base px-10 py-4 shadow-xl shadow-brand-500/25">
            Play Now — It's Free to Join
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </Link>
          <p className="text-xs text-gray-400 mt-5">No hidden fees · Secure payments · Instant tickets</p>
        </div>
      </section>
    </>
  );
}
