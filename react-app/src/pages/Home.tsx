import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getHome } from '../api';
import logo from '../assets/logo.png';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';

import type { Game } from '../types';

function stripHtml(s: string) { return s ? s.replace(/<[^>]*>/g, '').trim() : ''; }

// Decorative lottery balls scattered in the hero
const HERO_BALLS = [
  { n: 7,  cls: 'from-brand-400 to-brand-600',    style: { top: '18%',    left: '6%',   animationDelay: '0s'   } },
  { n: 14, cls: 'from-blue-400 to-blue-700',       style: { top: '14%',    right: '7%',  animationDelay: '1.5s' } },
  { n: 22, cls: 'from-emerald-400 to-emerald-700', style: { top: '62%',    left: '4%',   animationDelay: '3s'   } },
  { n: 35, cls: 'from-yellow-400 to-orange-500',   style: { top: '58%',    right: '5%',  animationDelay: '0.8s' } },
  { n: 9,  cls: 'from-purple-400 to-purple-700',   style: { bottom: '20%', left: '20%',  animationDelay: '2s'   } },
  { n: 42, cls: 'from-cyan-400 to-cyan-700',       style: { bottom: '18%', right: '18%', animationDelay: '1.2s' } },
];

const TRUST_ITEMS = [
  { icon: '🏛️', text: 'Govt. Licensed',   sub: 'Fully regulated'      },
  { icon: '⚡',  text: 'Instant Payout',   sub: 'Same-day winnings'    },
  { icon: '🔐', text: 'SSL Secured',       sub: 'Bank-grade safety'    },
  { icon: '🎯', text: 'Daily Draws',       sub: 'Win every single day' },
  { icon: '🏆', text: 'Verified Winners',  sub: 'Transparent results'  },
  { icon: '💳', text: 'Safe Payments',     sub: 'Razorpay powered'     },
  { icon: '🎁', text: 'Bonus Rewards',     sub: 'Refer & earn'         },
  { icon: '🌟', text: '24/7 Support',      sub: 'Always here to help'  },
];

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
  const gridGames    = !loading ? (featuredGame ? games.slice(1, 8) : games.slice(0, 8)) : [];

  return (
    <>
      {/* ── HERO ──────────────────────────────────────────────────────────── */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-hero-pattern">

        {/* Ambient orbs */}
        <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-600/10 rounded-full blur-[100px] animate-pulse-slow pointer-events-none"/>
        <div className="absolute bottom-1/4 right-1/4 w-72 h-72 bg-blue-600/10 rounded-full blur-[80px] animate-pulse-slow pointer-events-none" style={{ animationDelay: '1s' }}/>
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-brand-900/15 rounded-full blur-[140px] pointer-events-none"/>

        {/* Dot-grid overlay */}
        <div
          className="absolute inset-0 pointer-events-none opacity-30"
          style={{ backgroundImage: 'radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px)', backgroundSize: '32px 32px' }}
        />

        {/* Floating lottery balls — desktop only */}
        {HERO_BALLS.map(({ n, cls, style }) => (
          <div key={n} className="absolute hidden lg:flex items-center justify-center pointer-events-none animate-float" style={style}>
            <div className={`w-14 h-14 rounded-full bg-gradient-to-br ${cls} opacity-20 flex items-center justify-center shadow-xl`}>
              <span className="text-white font-black text-lg select-none leading-none"></span>
            </div>
          </div>
        ))}

        <div className="relative z-10 text-center px-4 max-w-4xl mx-auto pt-24">

          {/* Logo */}
          <div className="flex justify-center mb-6">
            <img src={logo} alt="ItanagarChoice" className="h-32 w-auto object-contain drop-shadow-2xl"/>
          </div>

          {/* Live badge */}
          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-semibold mb-5">
            <span className="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"/>
            Live Lottery Draws · Updated Daily
          </div>

          <p className="text-sm font-semibold text-gray-400 uppercase tracking-[0.3em] mb-3">Contest for your chance to</p>

          {/* Headline with glow */}
          <div className="relative mb-5">
            <h1 className="font-display font-black text-6xl sm:text-7xl lg:text-8xl leading-none tracking-tight">
              <span className="bg-gradient-to-r from-brand-400 via-brand-500 to-orange-400 bg-clip-text text-transparent">
                BIG WIN
              </span>
            </h1>
            <div className="absolute -bottom-3 left-1/2 -translate-x-1/2 w-2/3 h-10 bg-brand-500/15 blur-2xl rounded-full pointer-events-none"/>
          </div>

          <h2 className="font-display font-bold text-2xl sm:text-3xl text-white mb-6">with ItanagarChoice</h2>

          <p className="text-base text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            India's most trusted online lottery platform. Browse games, pick your lucky numbers, and claim your prize today.
          </p>

          {/* CTAs */}
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link to="/games" className="btn-primary text-base px-8 py-3.5 shadow-lg shadow-brand-500/25">
              Buy Now
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </Link>
            <Link to="/results" className="btn-secondary text-base px-8 py-3.5">View Results</Link>
          </div>

          {/* Stats */}
          {(Boolean(stats.games) || Boolean(stats.users) || Boolean(stats.paid)) && (
            <div className="mt-16 grid grid-cols-3 gap-4 max-w-lg mx-auto">
              {[
                { label: 'Active Games',   value: stats.games as string | number },
                { label: 'Happy Players',  value: stats.users ? `${Number(stats.users).toLocaleString()}+` : null },
                { label: 'Tickets Issued', value: stats.paid  ? `${Number(stats.paid).toLocaleString()}+`  : null },
              ].filter(s => s.value).map(s => (
                <div key={s.label} className="relative card p-4 text-center group overflow-hidden">
                  <div className="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-transparent via-brand-500/50 to-transparent"/>
                  <p className="text-2xl font-display font-bold bg-gradient-to-b from-white to-gray-400 bg-clip-text text-transparent">{s.value}</p>
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

      {/* ── MARQUEE TRUST STRIP ───────────────────────────────────────────── */}
      <div className="bg-dark-800 border-y border-white/5 overflow-hidden">
        <div className="flex animate-marquee whitespace-nowrap py-5">
          {[...TRUST_ITEMS, ...TRUST_ITEMS].map(({ icon, text, sub }, i) => (
            <div key={i} className="flex items-center gap-3 mx-8 shrink-0">
              <div className="w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-lg shrink-0">{icon}</div>
              <div>
                <p className="text-sm font-semibold text-white leading-tight">{text}</p>
                <p className="text-xs text-gray-500">{sub}</p>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* ── FEATURED GAME ─────────────────────────────────────────────────── */}
      {(loading || featuredGame) && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-6">
          <div className="flex items-center gap-3 mb-6">
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-500 text-white text-xs font-bold uppercase tracking-wider">
              <span className="w-1.5 h-1.5 bg-white rounded-full animate-pulse"/>
              Featured Draw
            </span>
          </div>

          {loading ? (
            <div className="h-56 rounded-2xl bg-dark-700/50 animate-pulse"/>
          ) : featuredGame && (
            <Link
              to={`/games/${featuredGame.id}`}
              className="group block rounded-2xl overflow-hidden border border-white/10 hover:border-brand-500/40 hover:shadow-2xl hover:shadow-brand-500/10 transition-all duration-300"
              style={{ background: 'linear-gradient(135deg, #1c1212 0%, #2e1a1a 100%)' }}
            >
              <div className="flex flex-col sm:flex-row items-stretch">
                {featuredGame.logo && (
                  <div className="sm:w-72 h-56 sm:h-auto relative overflow-hidden shrink-0">
                    <img
                      src={`${import.meta.env.VITE_PUBLIC_URL}/imglogo/${featuredGame.logo}`}
                      alt={featuredGame.name}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-[#1c1212] hidden sm:block"/>
                  </div>
                )}
                <div className="flex-1 p-6 sm:p-8 flex flex-col justify-center">
                  {featuredGame.heading && (
                    <p className="text-xs font-bold text-brand-400 uppercase tracking-widest mb-2">{featuredGame.heading}</p>
                  )}
                  <h3 className="font-display font-bold text-2xl sm:text-3xl text-white mb-3 group-hover:text-brand-100 transition-colors">
                    {featuredGame.name}
                  </h3>
                  {featuredGame.jackpot && (
                    <div className="mb-5">
                      <p className="text-xs text-gray-500 mb-0.5 uppercase tracking-wider">Jackpot Prize</p>
                      <p className="font-display font-black text-3xl sm:text-4xl bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent leading-tight">
                        {stripHtml(featuredGame.jackpot as string)}
                      </p>
                    </div>
                  )}
                  <div className="flex flex-wrap items-center gap-3">
                    <span className="px-3 py-1.5 rounded-lg bg-dark-600 border border-white/10 text-sm text-white font-semibold">
                      ₹{Number(featuredGame.price || 0).toLocaleString('en-IN')} / ticket
                    </span>
                    <span className="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-brand-500 text-white text-sm font-bold group-hover:bg-brand-400 transition-colors">
                      Buy Tickets
                      <svg className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </span>
                  </div>
                </div>
              </div>
            </Link>
          )}
        </section>
      )}

      {/* ── ACTIVE GAMES GRID ─────────────────────────────────────────────── */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="flex items-end justify-between mb-8">
          <div>
            <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Available Now</p>
            <h2 className="font-display font-bold text-3xl text-white">Active Games</h2>
          </div>
          <Link to="/games" className="hidden sm:inline-flex items-center gap-1 text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors">
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
          <Link to="/games" className="text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors">View all games →</Link>
        </div>
      </section>

      {/* ── HOW TO PLAY ───────────────────────────────────────────────────── */}
      <section className="relative py-24 overflow-hidden">
        <div className="absolute inset-0 bg-dark-800/60 pointer-events-none"/>
        <div
          className="absolute inset-0 pointer-events-none"
          style={{ backgroundImage: 'linear-gradient(rgba(225,29,38,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(225,29,38,0.04) 1px, transparent 1px)', backgroundSize: '48px 48px' }}
        />
        <div className="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-brand-500/20 to-transparent pointer-events-none"/>
        <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-brand-500/20 to-transparent pointer-events-none"/>

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-14">
            <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-2">Simple &amp; Secure</p>
            <h2 className="font-display font-bold text-3xl text-white">How to Play</h2>
            <p className="text-gray-500 text-sm mt-2 max-w-md mx-auto">Follow these 3 easy steps to start winning today</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div className="hidden md:block absolute top-12 left-1/3 right-1/3 h-px bg-gradient-to-r from-brand-500/20 via-brand-500/50 to-brand-500/20 pointer-events-none"/>
            {[
              { step: '01', icon: '🎰', title: 'Choose a Game',    desc: 'Register to ItanagarChoice & browse our selection of exciting lottery draws.' },
              { step: '02', icon: '🎫', title: 'Buy Your Tickets', desc: 'Pick your lucky tickets & complete your purchase securely in minutes.' },
              { step: '03', icon: '🏆', title: 'Win Prizes',       desc: 'Check results on draw day. Winners are notified instantly — prizes go to your wallet.' },
            ].map(({ step, icon, title, desc }) => (
              <div key={step} className="card p-8 text-center relative overflow-hidden group hover:border-brand-500/30 hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-300">
                <div className="absolute -top-6 -right-4 text-9xl font-display font-black text-white/[0.025] select-none pointer-events-none">{step}</div>
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

      {/* ── RECENT WINNERS ────────────────────────────────────────────────── */}
      {results.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="flex items-end justify-between mb-8">
            <div>
              <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Latest Wins</p>
              <h2 className="font-display font-bold text-3xl text-white">Recent Winners</h2>
            </div>
            <Link to="/results" className="text-sm text-brand-400 hover:text-brand-300 font-medium transition-colors hidden sm:block">All results →</Link>
          </div>

          {results[0] && (
            <div className="card mb-6 overflow-hidden relative border-yellow-500/20">
              <div className="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 pointer-events-none"/>
              <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-yellow-500/40 to-transparent pointer-events-none"/>
              <div className="relative flex flex-col sm:flex-row items-center gap-6 p-6">
                <div className="w-20 h-20 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-4xl shrink-0 shadow-lg shadow-yellow-500/30">🏆</div>
                <div className="flex-1 text-center sm:text-left">
                  <p className="text-xs text-yellow-400 font-bold uppercase tracking-widest mb-0.5">🎉 Top Winner</p>
                  <p className="font-display font-bold text-xl text-white">{String(results[0].name || results[0].game_name || 'Lucky Winner')}</p>
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
              <div key={String(r.id ?? i)} className="card p-4 flex items-center gap-4 hover:border-yellow-500/20 transition-colors">
                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400/20 to-orange-500/20 border border-yellow-500/20 flex items-center justify-center text-lg shrink-0">🏆</div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-white truncate">{String(r.name || r.game_name || '—')}</p>
                  <p className="text-xs text-gray-500">Prize: <span className="text-yellow-400 font-semibold">₹{Number(r.prize || 0).toLocaleString('en-IN')}</span></p>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* ── BOTTOM CTA ────────────────────────────────────────────────────── */}
      <section className="relative py-24 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-900/30 via-dark-900 to-dark-900 pointer-events-none"/>
        <div className="absolute top-1/4 left-1/4 w-80 h-80 bg-brand-600/10 rounded-full blur-[100px] pointer-events-none animate-pulse-slow"/>
        <div className="absolute bottom-1/4 right-1/4 w-64 h-64 bg-orange-600/10 rounded-full blur-[80px] pointer-events-none animate-pulse-slow" style={{ animationDelay: '1.5s' }}/>
        <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-500/20 to-transparent pointer-events-none"/>

        <div className="relative max-w-3xl mx-auto px-4 text-center">
          {/* Mini lottery balls row */}
          <div className="flex justify-center gap-3 mb-8">
            {([{ n: 7, cls: 'from-brand-400 to-brand-600' }, { n: 14, cls: 'from-blue-400 to-blue-600' }, { n: 22, cls: 'from-emerald-400 to-emerald-600' }, { n: 35, cls: 'from-yellow-400 to-orange-500' }, { n: 42, cls: 'from-purple-400 to-purple-600' }] as const).map(({ n, cls }) => (
              <div key={n} className={`w-12 h-12 rounded-full bg-gradient-to-br ${cls} flex items-center justify-center shadow-lg text-white font-black text-sm opacity-80`}>
                {n}
              </div>
            ))}
          </div>

          <h2 className="font-display font-black text-4xl sm:text-5xl text-white mb-4 leading-tight">
            Ready to Try Your{' '}
            <span className="bg-gradient-to-r from-brand-400 to-orange-400 bg-clip-text text-transparent">Luck?</span>
          </h2>
          <p className="text-gray-400 text-base mb-8 max-w-xl mx-auto leading-relaxed">
            Join thousands of winners on India's most trusted lottery platform. Your jackpot is just a ticket away.
          </p>
          <Link to="/games" className="inline-flex items-center gap-2 btn-primary text-base px-10 py-4 shadow-xl shadow-brand-500/25">
            Play Now — It's Free to Join
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </Link>
          <p className="text-xs text-gray-600 mt-5">No hidden fees · Secure payments · Instant tickets</p>
        </div>
      </section>
    </>
  );
}
