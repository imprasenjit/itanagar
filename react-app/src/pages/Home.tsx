import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getHome } from '../api';
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
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
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
        <Link to="/games" className="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-semibold bg-brand-50 hover:bg-brand-100 px-5 py-2.5 rounded-full border border-brand-200 transition-all">View all games →</Link>
      </div>
    </section>
  );
}
