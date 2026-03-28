import { Link } from 'react-router-dom';
import { useState, useEffect, useRef } from 'react';
import type { Game } from '../types';
import FlipClock from './FlipClock';

function useCountdown(drawDate: string | null | undefined) {
  const getRemaining = () => {
    if (!drawDate) return null;
    const diff = new Date(drawDate).getTime() - Date.now();
    if (diff <= 0) return { days: 0, hours: 0, minutes: 0, seconds: 0, ended: true };
    const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    return { days, hours, minutes, seconds, ended: false };
  };
  const [remaining, setRemaining] = useState(getRemaining);
  useEffect(() => {
    if (!drawDate) return;
    const id = setInterval(() => setRemaining(getRemaining()), 1_000);
    return () => clearInterval(id);
  }, [drawDate]);
  return remaining;
}

// Strip HTML tags from jackpot string e.g. "<p>1st Prize: Rs. 200000</p>" → "1st Prize: Rs. 200000"
function stripHtml(str: string): string {
  return str ? str.replace(/<[^>]*>/g, '').trim() : '';
}

export default function GameCard({ game, index = 0 }: { game: Game; index?: number }) {
  const {
    id,
    name,
    logo,
    logo2,
    heading,
    price,
    jackpot,
    date,         // next upcoming draw date from subquery
    result_date,
    totalTickets,
    soldTickets,
  } = game;

  const remaining   = totalTickets != null && soldTickets != null ? totalTickets - soldTickets : null;
  const pct         = totalTickets && totalTickets > 0 ? Math.round(((soldTickets ?? 0) / totalTickets) * 100) : 0;
  const isSoldOut   = totalTickets != null && totalTickets > 0 && remaining !== null && remaining <= 0;
  const hot         = !isSoldOut && pct >= 70;
  const jackpotText = stripHtml(jackpot ?? '');
  // Prefer upcoming draw date; fall back to result_date
  const drawDate    = date || result_date;

  const countdown = useCountdown(drawDate);

  const ref = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);
  const [imgError, setImgError] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const observer = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) { setVisible(true); observer.disconnect(); }
    }, { threshold: 0.1 });
    observer.observe(el);
    return () => observer.disconnect();
  }, []);

  return (
    <div ref={ref} className={visible ? 'animate-fade-in-up' : 'opacity-0'} style={visible ? { animationDelay: `${index * 120}ms` } : undefined}>
    <Link to={`/games/${id}`} className="group block h-full">
      <div className={`card h-full flex flex-col overflow-hidden transition-all duration-300 ${isSoldOut ? 'opacity-80 grayscale-[30%]' : 'hover:border-brand-500/30 hover:shadow-2xl hover:shadow-brand-500/10 group-hover:-translate-y-1.5'}`}>
        {/* Image */}
        <div className="relative h-52 overflow-hidden bg-gray-100">
          {logo && !imgError ? (
            <img
              src={`${import.meta.env.VITE_PUBLIC_URL}/imglogo/${logo}`}
              alt={name}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              onError={() => setImgError(true)}
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
              <svg className="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
              </svg>
            </div>
          )}
          <div className="absolute inset-0 bg-gradient-to-t from-dark-900/90 via-dark-900/20 to-transparent"/>

          {/* Sold Out overlay */}
          {isSoldOut && (
            <div className="absolute inset-0 flex items-center justify-center bg-black/40">
              <span className="bg-red-600 text-white text-sm font-bold uppercase tracking-widest px-5 py-2 rounded-full shadow-lg rotate-[-6deg]">
                Sold Out
              </span>
            </div>
          )}

          {/* Hot badge */}
          {hot && (
            <span className="absolute top-3 right-3 badge bg-red-500/20 text-red-400 border border-red-500/30 animate-pulse-slow">
              🔥 Hot
            </span>
          )}

          {/* Jackpot overlay */}
          {jackpotText && (
            <div className="absolute bottom-3 left-3 right-3">
              <p className="text-[10px] text-gray-300 uppercase tracking-widest font-semibold">Jackpot</p>
              <p className="text-base font-display font-bold text-white line-clamp-1 drop-shadow-lg">{jackpotText}</p>
            </div>
          )}
        </div>

        {/* Content */}
        <div className="p-5 flex-1 flex flex-col">
          <h3 className="font-display font-bold text-gray-900 text-lg leading-snug mb-1">{name}</h3>
          {heading && <p className="text-sm text-gray-500 mb-4 line-clamp-1">{heading}</p>}

          {/* Price + Draw date row */}
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center gap-2">
              <div className="w-9 h-9 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-center">
                <span className="text-brand-600 text-sm font-bold">₹</span>
              </div>
              <div>
                <p className="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Ticket Price</p>
                <p className="text-base font-bold text-gray-900">₹{Number(price).toLocaleString('en-IN')}</p>
              </div>
            </div>
            {countdown && !countdown.ended && drawDate && (
              <div className="text-right">
                <p className="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Draw Date</p>
                <p className="text-sm font-semibold text-brand-600">{new Date(drawDate).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}</p>
              </div>
            )}
            {countdown?.ended && (
              <span className="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Ended</span>
            )}
          </div>

          {/* Progress */}
          {remaining !== null && (
            <div className="mb-4">
              <div className="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>{soldTickets?.toLocaleString()} sold</span>
                {isSoldOut
                  ? <span className="font-semibold text-red-500">Sold Out</span>
                  : <span className="font-medium">{remaining?.toLocaleString()} left</span>
                }
              </div>
              <div className="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div
                  className={`h-full rounded-full transition-all ${isSoldOut ? 'bg-red-500' : pct >= 80 ? 'bg-red-500' : pct >= 50 ? 'bg-brand-500' : 'bg-emerald-500'}`}
                  style={{ width: `${pct}%` }}
                />
              </div>
            </div>
          )}

          {/* Spacer pushes footer down */}
          <div className="flex-1"/>

          {/* Footer — Countdown + Buy Now / Sold Out */}
          {isSoldOut ? (
            <div className="border-t border-gray-100 pt-3 mt-1">
              <p className="text-center text-sm font-semibold text-red-400">All tickets sold out</p>
            </div>
          ) : countdown && !countdown.ended ? (
            <div className="border-t border-gray-100 pt-3 mt-1 flex items-center justify-between">
              <FlipClock countdown={countdown} />
              <span className="inline-flex items-center gap-1 text-xs font-bold text-white bg-brand-600 group-hover:bg-brand-700 px-4 py-2 rounded-full transition-colors">
                Buy Now
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7"/></svg>
              </span>
            </div>
          ) : countdown?.ended ? (
            <div className="border-t border-gray-100 pt-3 mt-1">
              <p className="text-center text-sm font-semibold text-gray-400">Draw Ended</p>
            </div>
          ) : null}
        </div>
      </div>
    </Link>
    </div>
  );
}
