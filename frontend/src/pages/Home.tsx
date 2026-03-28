import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { getHome, getUpcomingGames } from '../api';
import heroBg from '../assets/background.png';
import browseEventsIcon from '../assets/browse_events.png';
import selectTicketIcon from '../assets/select_ticket.png';
import makePaymentIcon from '../assets/make_payment.png';
import getConfirmationIcon from '../assets/get_confirmations.png';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';
import type { Game } from '../types';

const PAGE_SIZE = 10; // matches backend default page size

export default function Home() {
  const [games, setGames]         = useState<Game[]>([]);
  const [total, setTotal]         = useState(0);
  const [loading, setLoading]     = useState(true);
  const [loadingMore, setMore]    = useState(false);
  const sentinelRef               = useRef<HTMLDivElement>(null);

  useEffect(() => {
    getHome()
      .then(r => {
        setGames(r.data.data?.games ?? []);
        setTotal(r.data.data?.total ?? 0);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  const hasMore = games.length < total;

  // Fetch next page and append
  const loadMore = () => {
    if (loadingMore || !hasMore) return;
    setMore(true);
    getUpcomingGames(games.length)
      .then(r => setGames(prev => [...prev, ...(r.data.data?.games ?? [])]))
      .catch(() => {})
      .finally(() => setMore(false));
  };

  // Infinite scroll via IntersectionObserver
  useEffect(() => {
    if (loading || !hasMore) return;
    const el = sentinelRef.current;
    if (!el) return;
    const observer = new IntersectionObserver(
      ([entry]) => { if (entry.isIntersecting) loadMore(); },
      { rootMargin: '200px' }
    );
    observer.observe(el);
    return () => observer.disconnect();
  }, [loading, hasMore, loadingMore, games.length]);

  const howItWorks = [
    { step: 'Browse Events',    desc: 'Explore upcoming events and draws.',  icon: browseEventsIcon,    link: '/games' },
    { step: 'Select Ticket',    desc: 'Choose your option easily.',           icon: selectTicketIcon,    link: '/games' },
    { step: 'Make Payment',     desc: 'Quickly pay securely online.',         icon: makePaymentIcon,     link: '/games' },
    { step: 'Get Confirmation', desc: 'Receive your e-tickets instantly.',    icon: getConfirmationIcon, link: '/games' },
  ];

  return (
    <>
      {/* ── Hero ─────────────────────────────────────────────────────────── */}
      <section className="bg-gray-50 px-4 sm:px-6 lg:px-8 py-6">
        <div className="max-w-7xl mx-auto">
          <div
            className="w-full text-white px-6 py-8 sm:px-10 sm:py-10 lg:px-16 lg:py-14 rounded-2xl shadow-2xl overflow-hidden relative"
            style={{ backgroundImage: `url(${heroBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }}
          >
            <div className="absolute inset-0 from-blue-600/70 via-indigo-600/70 to-purple-600/70 rounded-2xl" />
            <div className="relative z-10 w-[80%] sm:w-[60%] lg:w-[50%] text-left">
              <h2 className="text-lg sm:text-3xl lg:text-4xl font-bold tracking-tight leading-snug">
                Book Tickets & <span className="text-yellow-400 font-extrabold">Coupons</span><br />for Verified Events
              </h2>
              <div className="flex justify-start mt-2 sm:mt-4 text-sm font-medium">
                <span className="flex gap-1 items-center sm:text-base">
                  <svg className="w-5 h-5 sm:w-6 sm:h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4 5v6c0 5.25 3.5 10.15 8 11.35C16.5 21.15 20 16.25 20 11V5l-8-3z"/></svg>
                  Safe . Secure . Instant Confirmation
                </span>
              </div>
              <div className="flex gap-3 mt-7 sm:mt-8">
                <Link to="/games" className="bg-yellow-400 text-gray-900 px-5 py-2 sm:px-7 sm:py-3 rounded-full text-xs sm:text-sm font-semibold shadow-md hover:shadow-lg hover:bg-yellow-300 transition-all">
                  Explore Events
                </Link>
                <Link to="/results" className="bg-yellow-400 text-gray-900 px-5 py-2 sm:px-7 sm:py-3 rounded-full text-xs sm:text-sm font-semibold shadow-md hover:shadow-lg hover:bg-yellow-300 transition-all">
                  Results
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── How It Works ─────────────────────────────────────────────────── */}
      <section className="bg-gray-50 px-4 sm:px-6 lg:px-8 py-4">
        <div className="max-w-7xl mx-auto">
          <h3 className="text-2xl font-bold text-gray-800 mb-6 text-center">How It Works</h3>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {howItWorks.map((item, idx) => (
              <Link key={idx} to={item.link} className="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-3 flex flex-row items-center gap-3 border border-gray-200">
                <img src={item.icon} alt={item.step} className="w-10 h-10 object-contain shrink-0" />
                <div>
                  <h4 className="font-semibold text-gray-800 text-xs">{item.step}</h4>
                  <p className="text-[10px] text-gray-500 mt-0.5">{item.desc}</p>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* ── Upcoming Events ──────────────────────────────────────────────── */}
      <section className="bg-gray-50 px-4 sm:px-6 lg:px-8 py-4">
        <div className="max-w-7xl mx-auto">
          <h2 className="font-display font-bold text-3xl sm:text-4xl text-gray-900 text-center mb-10">Upcoming Events</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {loading
              ? Array.from({ length: 3 }).map((_, i) => <SkeletonCard key={i} />)
              : games.map((g, i) => <GameCard key={g.id} game={g} index={i} />)
            }
          </div>

          {/* Infinite scroll sentinel */}
          {!loading && hasMore && (
            <>
              <div ref={sentinelRef} className="h-1" />
              {loadingMore && (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                  {Array.from({ length: 3 }).map((_, i) => <SkeletonCard key={i} />)}
                </div>
              )}
            </>
          )}

          {/* End of list indicator */}
          {!loading && !hasMore && total > PAGE_SIZE && (
            <p className="mt-8 text-center text-sm text-gray-400">You've seen all upcoming events.</p>
          )}
        </div>
      </section>
    </>
  );
}
