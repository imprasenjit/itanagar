import { useEffect, useState, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { getGameDetail, getGameTickets, searchTickets } from '../api';
import { useCart } from '../context/CartContext';
import { useToast } from '../components/Toast';
import LoadingSpinner from '../components/LoadingSpinner';

const PAGE_SIZE = 60;

export default function GameDetail() {
  const { id }    = useParams();
  const { addToCart } = useCart();
  const addToast  = useToast();

  const [game, setGame]             = useState(null);
  const [range, setRange]           = useState(null);
  // ticketRange: flat array [start1, end1, start2, end2, ...] parsed from rangeStart DB column
  const [ticketRange, setTicketRange] = useState([]);
  const [tickets, setTickets]       = useState([]);
  const [loading, setLoading]       = useState(true);
  const [page, setPage]             = useState(0);
  const [selected, setSelected]     = useState([]);
  const [searchNum, setSearchNum]   = useState('');
  const [searchResult, setSearchResult] = useState(null);
  const [adding, setAdding]         = useState(false);

  const fetchTickets = useCallback(async (p = 0) => {
    if (ticketRange.length < 2) return;
    const rangeStart = ticketRange[0];
    const rangeEnd   = ticketRange[1];
    const start = rangeStart + p * PAGE_SIZE;
    const end   = Math.min(start + PAGE_SIZE - 1, rangeEnd);
    // game_tickets response: { status, data: { tickets: [...available numbers] } }
    const r = await getGameTickets(id, start, end);
    setTickets(r.data.data?.tickets || []);
    setPage(p);
    setSelected([]);
  }, [id, ticketRange]);

  useEffect(() => {
    getGameDetail(id)
      .then(r => {
        // game_detail response: { status, data: { website: {...}, range: {...}, ticketRange: [...] } }
        setGame(r.data.data?.website || null);
        setRange(r.data.data?.range || null);
        setTicketRange(r.data.data?.ticketRange || []);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [id]);

  useEffect(() => { if (ticketRange.length >= 2) fetchTickets(0); }, [ticketRange, fetchTickets]);

  const totalPages = ticketRange.length >= 2
    ? Math.ceil((ticketRange[1] - ticketRange[0] + 1) / PAGE_SIZE)
    : 0;

  const toggleTicket = (num) => {
    setSelected(prev =>
      prev.includes(num) ? prev.filter(n => n !== num) : [...prev, num]
    );
  };

  const handleSearch = async () => {
    if (!searchNum.trim()) return;
    try {
      const r = await searchTickets(id, { ticket: searchNum.trim() });
      setSearchResult(r.data.data);
    } catch {
      setSearchResult('error');
    }
  };

  const handleAddToCart = async () => {
    if (selected.length === 0) { addToast('Select at least one ticket', 'error'); return; }
    setAdding(true);
    try {
      await addToCart({ web_id: id, range_id: range?.id, tickets: selected });
      addToast(`${selected.length} ticket(s) added to cart!`, 'success');
      setSelected([]);
    } catch (e) {
      addToast(e.message || 'Failed to add tickets', 'error');
    } finally {
      setAdding(false);
    }
  };

  if (loading) return <LoadingSpinner size="lg" text="Loading game…"/>;
  if (!game)   return (
    <div className="text-center pt-40 pb-20">
      <p className="text-5xl mb-4">🎰</p>
      <p className="text-gray-400 mb-4">Game not found.</p>
      <Link to="/games" className="btn-secondary inline-flex">← Back to Games</Link>
    </div>
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-20">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-2 text-xs text-gray-500 mb-6">
        <Link to="/" className="hover:text-white transition-colors">Home</Link>
        <span>/</span>
        <Link to="/games" className="hover:text-white transition-colors">Games</Link>
        <span>/</span>
        <span className="text-gray-300">{game.name}</span>
      </nav>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left: Game info */}
        <div className="lg:col-span-1">
          <div className="card overflow-hidden sticky top-24">
            {range?.logo && (
              <img src={`/itanagar/assets/imglogo/${range.logo}`} alt={game.name}
                className="w-full h-52 object-cover"/>
            )}
            <div className="p-5">
              <h1 className="font-display font-bold text-xl text-white mb-1">{game.name}</h1>
              {range?.heading && <p className="text-sm text-gray-400 mb-4">{range.heading}</p>}

              <div className="space-y-3 mb-5">
                {range?.price && (
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Price/ticket</span>
                    <span className="text-sm font-semibold text-white">₹{Number(range.price).toLocaleString('en-IN')}</span>
                  </div>
                )}
                {range?.jackpot && (
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Jackpot</span>
                    <span className="text-sm font-bold text-brand-400">₹{Number(range.jackpot).toLocaleString('en-IN')}</span>
                  </div>
                )}
                {range?.result_date && (
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Draw date</span>
                    <span className="text-sm text-white">{new Date(range.result_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                  </div>
                )}
              </div>

              {/* Selection summary */}
              {selected.length > 0 && (
                <div className="bg-brand-500/10 border border-brand-500/20 rounded-xl p-3 mb-4">
                  <p className="text-xs text-brand-400 font-semibold mb-1">{selected.length} ticket(s) selected</p>
                  <p className="text-sm font-bold text-white">
                    Total: ₹{(selected.length * Number(range?.price || 0)).toLocaleString('en-IN')}
                  </p>
                </div>
              )}

              <button onClick={handleAddToCart} disabled={selected.length === 0 || adding}
                className="btn-primary w-full disabled:opacity-40 disabled:cursor-not-allowed">
                {adding ? 'Adding…' : '🛒 Add to Cart'}
              </button>
            </div>
          </div>
        </div>

        {/* Right: Ticket grid */}
        <div className="lg:col-span-2">
          {/* Search */}
          <div className="card p-4 mb-5">
            <p className="text-sm font-semibold text-white mb-3">Search Ticket Number</p>
            <div className="flex gap-2">
              <input
                value={searchNum}
                onChange={e => setSearchNum(e.target.value)}
                onKeyDown={e => e.key === 'Enter' && handleSearch()}
                placeholder="Enter ticket number…"
                className="input flex-1"
                type="number"
              />
              <button onClick={handleSearch} className="btn-secondary px-4">Search</button>
            </div>
            {searchResult != null && (
              <div className={`mt-3 p-3 rounded-xl text-sm ${searchResult === 'error' ? 'bg-red-500/10 text-red-400' : searchResult?.status === 'available' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-yellow-500/10 text-yellow-400'}`}>
                {searchResult === 'error' ? 'Error searching ticket.' :
                  searchResult?.status === 'available' ? `✅ Ticket #${searchNum} is available!` : `❌ Ticket #${searchNum} is already sold.`
                }
              </div>
            )}
          </div>

          {/* Grid header */}
          <div className="flex items-center justify-between mb-4">
            <p className="text-sm text-gray-400">
              Showing tickets <span className="text-white font-semibold">{range?.start_from + page * PAGE_SIZE}</span>–<span className="text-white font-semibold">{Math.min(range?.start_from + (page + 1) * PAGE_SIZE - 1, range?.end_to)}</span>
            </p>
            <div className="flex items-center gap-1.5">
              <span className="w-3 h-3 rounded-sm bg-dark-600 border border-white/10 inline-block"/>
              <span className="text-xs text-gray-500 mr-3">Available</span>
              <span className="w-3 h-3 rounded-sm bg-red-900/50 border border-red-500/30 inline-block"/>
              <span className="text-xs text-gray-500 mr-3">Sold</span>
              <span className="w-3 h-3 rounded-sm bg-brand-500/30 border border-brand-500 inline-block"/>
              <span className="text-xs text-gray-500">Selected</span>
            </div>
          </div>

          {/* Ticket grid */}
          <div className="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-1.5">
            {tickets.map(t => {
              const num  = t.ticket_number || t.number;
              const sold = t.status === 'sold' || t.paid_status === 1;
              const sel  = selected.includes(num);
              return (
                <button
                  key={num}
                  disabled={sold}
                  onClick={() => toggleTicket(num)}
                  className={`h-9 rounded-lg text-xs font-semibold transition-all active:scale-95 ${
                    sold ? 'bg-red-900/30 border border-red-500/20 text-red-900 cursor-not-allowed' :
                    sel  ? 'bg-brand-500/30 border border-brand-500 text-brand-300 shadow-sm shadow-brand-500/30' :
                           'bg-dark-600 border border-white/5 text-gray-400 hover:border-brand-500/50 hover:text-white'
                  }`}>
                  {num}
                </button>
              );
            })}
          </div>

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="flex items-center justify-center gap-2 mt-6">
              <button
                onClick={() => fetchTickets(page - 1)}
                disabled={page === 0}
                className="btn-secondary px-3 py-2 text-xs disabled:opacity-30">
                ← Prev
              </button>
              <span className="text-sm text-gray-500">
                Page <span className="text-white font-semibold">{page + 1}</span> / {totalPages}
              </span>
              <button
                onClick={() => fetchTickets(page + 1)}
                disabled={page >= totalPages - 1}
                className="btn-secondary px-3 py-2 text-xs disabled:opacity-30">
                Next →
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
