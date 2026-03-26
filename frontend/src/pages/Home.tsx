import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getHome } from '../api';
import heroBg from '../assets/background.png';
import browseEventsIcon from '../assets/browse_events.png';
import selectTicketIcon from '../assets/select_ticket.png';
import makePaymentIcon from '../assets/make_payment.png';
import getConfirmationIcon from '../assets/get_confirmations.png';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';
import type { Game } from '../types';

export default function Home() {
  const [games, setGames]     = useState<Game[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getHome()
      .then(r => setGames(r.data.data?.games ?? []))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  const activeGames = games.filter(g => {
    const drawDate = g.date || g.result_date;
    return !drawDate || new Date(drawDate).getTime() > Date.now();
  });

  return (
    <><section className="px-4 sm:px-6 lg:px-8 py-5 bg-gray-50">
        <div className="max-w-7xl mx-auto">
        <div
          className="w-full text-white px-3 py-2 sm:px-10 sm:py-10 lg:px-16 lg:py-14 rounded-2xl shadow-2xl overflow-hidden relative"
          style={{ backgroundImage: `url(${heroBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }}
        >
          <div className="absolute inset-0 from-blue-600/70 via-indigo-600/70 to-purple-600/70 rounded-2xl" />
          <div className="relative z-10 w-[80%] sm:w-[60%] lg:w-[50%] text-left">
          <h2 className="text-lg sm:text-3xl lg:text-4xl font-bold tracking-tight text-left leading-snug">
            Book Tickets & <span className="text-yellow-400 font-extrabold">Coupons</span><br />for Verified Events
          </h2>
          <div className="flex justify-start space-x-6 mt-2 sm:mt-4 text-sm font-medium">
            <span className="flex gap-1 items-center sm:text-base">
              <svg className="w-5 h-5 sm:w-6 sm:h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4 5v6c0 5.25 3.5 10.15 8 11.35C16.5 21.15 20 16.25 20 11V5l-8-3z"/></svg>
              Safe . Secure . Instant Confirmation
            </span>
          </div>
          <div className="flex justify-start space-x-4 mt-7 sm:mt-8">
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
      <section className=" bg-gray-50 px-4 py-2">
        <h3 className="text-2xl font-bold text-gray-800 mb-8 text-center">How It Works</h3>
        <div className="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-4">
          {[
            { step: "Browse Events", desc: "Explore upcoming events and draws.", icon: browseEventsIcon, link: '/games' },
            { step: "Select Ticket", desc: "Choose your option easily.", icon: selectTicketIcon, link: '/games' },
            { step: "Make Payment", desc: "Quickly pay securely online.", icon: makePaymentIcon, link: '/games' },
            { step: "Get Confirmation", desc: "Receive your e-tickets instantly.", icon: getConfirmationIcon, link: '/games' },
          ].map((item, idx) => (
            <div
              key={idx}
              className="bg-gray-100 rounded-2xl shadow-lg hover:shadow-xl transition-shadow p-2 flex flex-row items-start gap-1 border border-gray-200"
            >
              {item.link ? (
                <Link to={item.link} className="flex flex-row items-start gap-1 w-full">
                  <div className="flex flex-col items-center gap-1 shrink-0">
                    <img src={item.icon} alt={item.step} className="w-10 h-10 object-contain" />
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-800 text-xs">{item.step}</h4>
                    <p className="text-[10px] text-gray-500 mt-0.5">{item.desc}</p>
                  </div>
                </Link>
              ) : (
                <>
                  <div className="flex flex-col items-center gap-1 shrink-0">
                    <img src={item.icon} alt={item.step} className="w-10 h-10 object-contain" />
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-800 text-xs">{item.step}</h4>
                    <p className="text-[10px] text-gray-500 mt-0.5">{item.desc}</p>
                  </div>
                </>
              )}
            </div>
          ))}
        </div>
      </section>
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="flex items-center justify-center mb-10 pt-10 ">
        <div>

          <h2 className="font-display font-bold text-3xl sm:text-4xl text-gray-900 text-center">Upcomming Events</h2>
        </div>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {loading
          ? Array.from({ length: 3 }).map((_, i) => <SkeletonCard key={i}/>)
          : activeGames.map((g, i) => <GameCard key={g.id} game={g} index={i}/>)
        }
      </div>
      <div className="mt-8 text-center sm:hidden">
        <Link to="/games" className="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-semibold bg-brand-50 hover:bg-brand-100 px-5 py-2.5 rounded-full border border-brand-200 transition-all">View all Events→</Link>
      </div>
      </section>
      </>
  );
}
