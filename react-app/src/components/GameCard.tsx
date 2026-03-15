import { Link } from 'react-router-dom';
import { useState, useEffect } from 'react';
import type { Game } from '../types';

function useCountdown(drawDate: string | null | undefined) {
  const getRemaining = () => {
    if (!drawDate) return null;
    const diff = new Date(drawDate).getTime() - Date.now();
    if (diff <= 0) return { days: 0, hours: 0, minutes: 0, ended: true };
    const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    return { days, hours, minutes, ended: false };
  };
  const [remaining, setRemaining] = useState(getRemaining);
  useEffect(() => {
    if (!drawDate) return;
    const id = setInterval(() => setRemaining(getRemaining()), 60_000);
    return () => clearInterval(id);
  }, [drawDate]);
  return remaining;
}

// Strip HTML tags from jackpot string e.g. "<p>1st Prize: Rs. 200000</p>" → "1st Prize: Rs. 200000"
function stripHtml(str: string): string {
  return str ? str.replace(/<[^>]*>/g, '').trim() : '';
}

export default function GameCard({ game }: { game: Game }) {
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
  const hot         = pct >= 70;
  const jackpotText = stripHtml(jackpot ?? '');
  // Prefer upcoming draw date; fall back to result_date
  const drawDate    = date || result_date;

  const countdown = useCountdown(drawDate);

  return (
    <Link to={`/games/${id}`} className="group block">
      <div className="card overflow-hidden hover:border-brand-500/30 hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-300 group-hover:-translate-y-1">
        {/* Image */}
        <div className="relative h-48 overflow-hidden bg-dark-800">
          {logo ? (
            <img src={`${import.meta.env.VITE_PUBLIC_URL}/imglogo/${logo}`} alt={name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-dark-600 to-dark-800">
              <svg className="w-16 h-16 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
              </svg>
            </div>
          )}
          {/* Overlay gradient */}
          <div className="absolute inset-0 bg-gradient-to-t from-dark-900/90 via-transparent to-transparent"/>

          {/* Countdown badge — top left */}
          {countdown && !countdown.ended && (
            <div className="absolute top-3 left-3 flex items-center gap-1.5 bg-dark-900/80 backdrop-blur-sm text-white px-2.5 py-1 rounded-lg text-[18px] font-bold">
              <svg className="w-3 h-3 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {countdown.days > 0 && <span><span className="text-brand-400">{countdown.days}</span>Days</span>}
              <span><span className="text-brand-400">{countdown.hours}</span>Hours</span>
              {countdown.days === 0 && <span><span className="text-brand-400">{countdown.minutes}</span>m</span>}
              <span className="text-gray-400 font-normal">left</span>
            </div>
          )}
          {countdown?.ended && (
            <div className="absolute top-3 left-3 bg-gray-700/80 backdrop-blur-sm text-gray-400 px-2.5 py-1 rounded-lg text-[11px] font-bold">
              Draw Ended
            </div>
          )}

          {/* Hot badge */}
          {hot && (
            <span className="absolute top-3 right-3 badge bg-red-500/20 text-red-400 border border-red-500/30 animate-pulse-slow">
              🔥 Hot
            </span>
          )}
          {/* Jackpot */}
          {jackpotText && (
            <div className="absolute bottom-3 left-3 right-3">
              <p className="text-xs text-gray-400 uppercase tracking-wider">Jackpot</p>
              <p className="text-sm font-display font-bold text-brand-400 line-clamp-1">{jackpotText}</p>
            </div>
          )}
        </div>

        {/* Content */}
        <div className="p-4">
          <h3 className="font-display font-bold text-white text-base mb-0.5">{name}</h3>
          {heading && <p className="text-xs text-gray-500 mb-3 line-clamp-1">{heading}</p>}

          {/* Progress */}
          {remaining !== null && (
            <div className="mb-3">
              <div className="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>{soldTickets?.toLocaleString()} sold</span>
                <span>{remaining?.toLocaleString()} left</span>
              </div>
              <div className="h-1.5 bg-dark-600 rounded-full overflow-hidden">
                <div
                  className={`h-full rounded-full transition-all ${pct >= 80 ? 'bg-red-500' : pct >= 50 ? 'bg-brand-500' : 'bg-emerald-500'}`}
                  style={{ width: `${pct}%` }}
                />
              </div>
            </div>
          )}

          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs text-gray-500">Price per ticket</p>
              <p className="text-base font-bold text-white">₹{Number(price).toLocaleString('en-IN')}</p>
            </div>
            {countdown && !countdown.ended && drawDate && (
              <div className="text-right">
                <p className="text-xs text-gray-500">Draw date</p>
                <p className="text-xs font-semibold text-brand-400">{new Date(drawDate).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}</p>
              </div>
            )}
            {countdown?.ended && (
              <span className="text-xs font-semibold text-gray-500 bg-gray-700/50 px-2 py-0.5 rounded-full">Ended</span>
            )}
          </div>
        </div>
      </div>
    </Link>
  );
}
