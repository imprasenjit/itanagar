import { useEffect, useRef, useState } from 'react';
import { getGames } from '../api';
import GameCard from '../components/GameCard';
import { SkeletonCard } from '../components/LoadingSpinner';

import type { Game } from '../types';

const PAGE_SIZE = 10;

export default function Games() {
  const [games, setGames]       = useState<Game[]>([]);
  const [loading, setLoading]   = useState(true);
  const [search, setSearch]     = useState('');
  const [visibleCount, setVisible] = useState(PAGE_SIZE);
  const sentinelRef             = useRef<HTMLDivElement>(null);

  useEffect(() => {
    getGames()
      .then(r => setGames(r.data.data?.games || []))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  // Reset visible count when search changes
  useEffect(() => { setVisible(PAGE_SIZE); }, [search]);

  const sorted = games
    .sort((a, b) => {
      const dateA = new Date(a.date || a.result_date || '9999-12-31').getTime();
      const dateB = new Date(b.date || b.result_date || '9999-12-31').getTime();
      return dateA - dateB;
    });

  const filtered = sorted.filter(g =>
    g.name?.toLowerCase().includes(search.toLowerCase())
  );

  const visible = filtered.slice(0, visibleCount);
  const hasMore = visibleCount < filtered.length;

  // Infinite scroll
  useEffect(() => {
    if (loading || !hasMore) return;
    const el = sentinelRef.current;
    if (!el) return;
    const observer = new IntersectionObserver(
      ([entry]) => { if (entry.isIntersecting) setVisible(v => v + PAGE_SIZE); },
      { rootMargin: '200px' }
    );
    observer.observe(el);
    return () => observer.disconnect();
  }, [loading, hasMore, visibleCount]);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-20">
      {/* Header */}
      <div className="mb-8 text-center">
        <p className="text-xs text-brand-600 font-semibold uppercase tracking-widest mb-1">All Events</p>
        <h1 className="font-display font-bold text-3xl text-gray-900 mb-4">Search</h1>
        <div className="flex justify-center">
          <input
            type="search"
            placeholder="Search games…"
            value={search}
            onChange={e => setSearch(e.target.value)}
            className="input w-full max-w-sm"
          />
        </div>
      </div>

      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {Array.from({ length: 6 }).map((_, i) => <SkeletonCard key={i}/>)}
        </div>
      ) : filtered.length === 0 ? (
        <div className="text-center py-24">
          <p className="text-4xl mb-3">🎰</p>
          <p className="text-gray-500 text-sm">No games found{search ? ` for "${search}"` : ''}.</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {visible.map((g, i) => <GameCard key={g.id} game={g} index={i}/>)}
          </div>

          {/* Infinite scroll sentinel */}
          {hasMore && (
            <>
              <div ref={sentinelRef} className="h-1" />
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                {Array.from({ length: 3 }).map((_, i) => <SkeletonCard key={i}/>)}
              </div>
            </>
          )}

          {!hasMore && filtered.length > PAGE_SIZE && (
            <p className="mt-8 text-center text-sm text-gray-400">You've seen all events.</p>
          )}
        </>
      )}
    </div>
  );
}
