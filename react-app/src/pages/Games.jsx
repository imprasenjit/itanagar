import { useEffect, useState } from 'react';
import { getGames } from '../api';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';

export default function Games() {
  const [games, setGames]   = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch]   = useState('');

  useEffect(() => {
    getGames()
      // games response: { status, data: { games: [...] } }
      .then(r => setGames(r.data.data?.games || []))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  const filtered = games.filter(g =>
    g.name?.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      {/* Header */}
      <div className="mb-8">
        <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">All Lotteries</p>
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <h1 className="font-display font-bold text-3xl text-white">Browse Games</h1>
          <input
            type="search"
            placeholder="Search games…"
            value={search}
            onChange={e => setSearch(e.target.value)}
            className="input max-w-xs"
          />
        </div>
      </div>

      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          {Array.from({ length: 8 }).map((_, i) => <SkeletonCard key={i}/>)}
        </div>
      ) : filtered.length === 0 ? (
        <div className="text-center py-24">
          <p className="text-4xl mb-3">🎰</p>
          <p className="text-gray-500 text-sm">No games found{search ? ` for "${search}"` : ''}.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          {filtered.map(g => <GameCard key={g.id} game={g}/>)}
        </div>
      )}
    </div>
  );
}
