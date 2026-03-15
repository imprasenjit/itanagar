import { useEffect, useState } from 'react';
import { getResults, getGames } from '../api';
import LoadingSpinner from '../components/LoadingSpinner';

import type { Game } from '../types';

export default function Results() {
  const [results, setResults] = useState<Record<string, any>[]>([]);
  const [games, setGames]     = useState<Game[]>([]);
  const [loading, setLoading] = useState(true);
  const [webId, setWebId]     = useState('');
  const [date, setDate]       = useState('');

  const fetchResults = async () => {
    setLoading(true);
    const params: Record<string, string> = {};
    if (webId) params.web_id = webId;
    if (date)  params.date   = date;
    try {
      const r = await getResults(params);
      setResults(r.data.data?.results || []);
    } catch {
      setResults([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    getGames().then(r => setGames(r.data.data?.games || [])).catch(() => {});
    fetchResults();
  }, []);

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <div className="mb-8">
        <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Draw History</p>
        <h1 className="font-display font-bold text-3xl text-white">Results</h1>
      </div>

      {/* Filters */}
      <div className="card p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <select value={webId} onChange={e => setWebId(e.target.value)} className="input flex-1">
          <option value="">All Games</option>
          {games.map(g => <option key={g.id} value={g.id}>{g.name}</option>)}
        </select>
        <input type="date" value={date} onChange={e => setDate(e.target.value)} className="input flex-1"/>
        <button onClick={fetchResults} className="btn-primary px-6">Filter</button>
      </div>

      {loading ? (
        <LoadingSpinner size="lg" text="Loading results…"/>
      ) : results.length === 0 ? (
        <div className="text-center py-24 card">
          <p className="text-4xl mb-3">🏆</p>
          <p className="text-gray-500">No results found.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {results.map(r => (
            <div key={r.id} className="card p-4 flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400/20 to-orange-500/20 border border-yellow-500/20 flex items-center justify-center text-2xl shrink-0">🏆</div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-white">{r.game_name || r.name}</p>
                <p className="text-xs text-gray-500">
                  Tickets: <span className="text-gray-300">{r.tickets || '—'}</span>
                  {r.createdAt && <> · <span>{new Date(r.createdAt).toLocaleDateString('en-IN')}</span></>}
                </p>
              </div>
              <div className="text-right shrink-0">
                <p className="text-xs text-gray-500">Prize</p>
                <p className="text-sm font-bold text-brand-400">₹{Number(r.prize || 0).toLocaleString('en-IN')}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
