import { useEffect, useState, useCallback, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';

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
    const t = setInterval(() => setRemaining(getRemaining()), 1000);
    return () => clearInterval(t);
  }, [drawDate]);
  return remaining;
}
import { getGameDetail, getGameTickets, searchTickets } from '../api';
import { useCart } from '../context/CartContext';
import { useToast } from '../components/Toast';
import LoadingSpinner from '../components/LoadingSpinner';
import type { Game } from '../types';

const PAGE_SIZE = 50;

// Strip HTML tags — jackpot is stored as "<p>...</p>" in DB
function stripHtml(str: string): string {
  return str ? str.replace(/<[^>]*>/g, '').trim() : '';
}

export default function GameDetail() {
  const { id = '' } = useParams<{ id: string }>();
  const { addToCart, count: cartCount, items: cartItems } = useCart();
  const addToast      = useToast();
  const cartTotal = cartItems.reduce((sum, item) => sum + Number(item.total_price || 0), 0);

  const [game, setGame]               = useState<Game | null>(null);
  const [range, setRange]             = useState<Record<string, any> | null>(null);
  // ticketRange: flat array [s1,e1,s2,e2,...] from API (parsed from rangeStart DB column)
  const [ticketRange, setTicketRange] = useState<number[]>([]);
  // tickets: array of { num, available } for current page within selected segment
  const [tickets, setTickets]         = useState<{ num: number; available: boolean }[]>([]);
  const [loading, setLoading]         = useState(true);
  const [segIdx, setSegIdx]           = useState(0);  // selected segment index
  const [page, setPage]               = useState(0);
  const [selected, setSelected]       = useState<number[]>([]);
  const [searchNum, setSearchNum]     = useState('');
  const [searchResult, setSearchResult] = useState<Record<string, any> | 'error' | null>(null);
  const [adding, setAdding]           = useState(false);
  const [addSuccess, setAddSuccess]   = useState(false);
  const [ticketsLoading, setTicketsLoading] = useState(false);
  const countdown = useCountdown(range?.result_date);
  const [slideIdx, setSlideIdx]       = useState(0);
  const [lightbox, setLightbox]       = useState(false);

  // Parse flat [s1,e1,s2,e2,...] into [[s1,e1],[s2,e2],...] — matches PHP $i+=2 logic
  const segments = useMemo<[number, number][]>(() => {
    const segs = [];
    for (let i = 0; i + 1 < ticketRange.length; i += 2) {
      segs.push([ticketRange[i], ticketRange[i + 1]] as [number, number]);
    }
    return segs;
  }, [ticketRange]);

  const currentSeg = (segments[segIdx] ?? null) as [number, number] | null;

  const fetchTickets = useCallback(async (p = 0, seg: [number, number] | null = null) => {
    const activeSeg = seg || currentSeg;
    if (!activeSeg) return;
    const [segStart, segEnd] = activeSeg;
    const start = segStart + p * PAGE_SIZE;
    const end   = Math.min(start + PAGE_SIZE - 1, segEnd);

    setTicketsLoading(true);
    setTickets([]);
    try {
      // API returns only AVAILABLE ticket numbers in [start..end]
      const r = await getGameTickets(id, start, end);
      const availableSet = new Set(r.data.data?.tickets || []);

      // Generate full range start..end; mark each as available or sold
      const all = Array.from({ length: end - start + 1 }, (_, i) => ({
        num:       start + i,
        available: availableSet.has(start + i),
      }));
      setTickets(all);
    } catch {
      setTickets([]);
    } finally {
      setTicketsLoading(false);
    }
    setPage(p);
    setSelected([]);
  }, [id, currentSeg]);

  useEffect(() => {
    getGameDetail(id)
      .then(r => {
        setGame(r.data.data?.website || null);
        setRange(r.data.data?.range   || null);
        setTicketRange(r.data.data?.ticketRange || []);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [id]);

  // When segments load (or selected segment changes), fetch first page
  useEffect(() => {
    if (segments.length > 0) {
      fetchTickets(0, segments[segIdx]);
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [segments, segIdx]);

  const handleSegmentSelect = (idx: number) => {
    setSegIdx(idx);
    setSelected([]);
    setPage(0);
  };

  const totalPages = currentSeg
    ? Math.ceil((currentSeg[1] - currentSeg[0] + 1) / PAGE_SIZE)
    : 0;

  const toggleTicket = (num: number) => {
    setSelected(prev =>
      prev.includes(num) ? prev.filter(n => n !== num) : [...prev, num]
    );
  };

  const handleSearch = async () => {
    if (!searchNum.trim()) return;
    try {
      const r = await searchTickets(id, { ticket: searchNum.trim() });
      setSearchResult(r.data.data);
    } catch { setSearchResult('error'); }
  };

  // Auto-advance slider every 6 seconds (must be before early returns)
  const slideImgCount = range ? [range.logo, range.logo2].filter(Boolean).length : 0;
  useEffect(() => {
    if (slideImgCount < 2 || lightbox) return;
    const id = setInterval(() => setSlideIdx(prev => (prev + 1) % slideImgCount), 6000);
    return () => clearInterval(id);
  }, [slideImgCount, lightbox]);

  const handleAddToCart = async () => {
    if (selected.length === 0) { addToast('Select at least one ticket', 'error'); return; }
    setAdding(true);
    try {
      await addToCart({ web_id: id, range_id: range?.id, tickets: selected });
      addToast(`${selected.length} ticket(s) added to cart!`, 'success');
      setSelected([]);
      setAddSuccess(true);
      setTimeout(() => setAddSuccess(false), 2000);
    } catch (e: unknown) {
      addToast((e as Error).message || 'Failed to add tickets', 'error');
    } finally {
      setAdding(false);
    }
  };

  if (loading) return <div className="flex items-center justify-center min-h-screen"><LoadingSpinner size="lg" text="Loading game…"/></div>;
  if (!game)   return (
    <div className="text-center pt-40 pb-20">
      <p className="text-5xl mb-4">🎰</p>
      <p className="text-gray-400 mb-4">Game not found.</p>
      <Link to="/games" className="btn-secondary inline-flex">← Back to Games</Link>
    </div>
  );

  const slideImgs = (range ? [range.logo, range.logo2].filter(Boolean) : []) as string[];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-28 lg:pb-20">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-2 text-xs text-gray-500 mb-6">
        <Link to="/" className="hover:text-gray-900 transition-colors">Home</Link>
        <span>/</span>
        <Link to="/games" className="hover:text-gray-900 transition-colors">Games</Link>
        <span>/</span>
        <span className="text-gray-700">{game.name}</span>
      </nav>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left: Game info */}
        <div className="lg:col-span-1">
          <div className="card overflow-hidden sticky top-24">
            {/* Image slider */}
            {slideImgs.length > 0 && (
              <div className="relative h-52 overflow-hidden bg-gray-100 cursor-zoom-in" onClick={() => setLightbox(true)}>
                {slideImgs.map((src, i) => (
                  <img
                    key={src}
                    src={`${import.meta.env.VITE_PUBLIC_URL}/imglogo/${src}`}
                    alt={`${game.name} ${i + 1}`}
                    className={`absolute inset-0 w-full h-full object-cover transition-opacity duration-500 ${i === slideIdx ? 'opacity-100' : 'opacity-0'}`}
                  />
                ))}
                {slideImgs.length > 1 && (
                  <>
                    <button
                      onClick={e => { e.stopPropagation(); setSlideIdx(prev => (prev - 1 + slideImgs.length) % slideImgs.length); }}
                      className="absolute left-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-dark-900/70 backdrop-blur-sm flex items-center justify-center text-white text-lg hover:bg-dark-900 transition-colors z-10">
                      ‹
                    </button>
                    <button
                      onClick={e => { e.stopPropagation(); setSlideIdx(prev => (prev + 1) % slideImgs.length); }}
                      className="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-dark-900/70 backdrop-blur-sm flex items-center justify-center text-white text-lg hover:bg-dark-900 transition-colors z-10">
                      ›
                    </button>
                    <div className="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                      {slideImgs.map((_, i) => (
                        <button
                          key={i}
                          onClick={e => { e.stopPropagation(); setSlideIdx(i); }}
                          className={`w-1.5 h-1.5 rounded-full transition-colors ${i === slideIdx ? 'bg-white' : 'bg-white/40'}`}
                        />
                      ))}
                    </div>
                  </>
                )}
                <div className="absolute top-2 right-2 z-10 bg-dark-900/60 rounded-lg p-1 pointer-events-none">
                  <svg className="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </div>
              </div>
            )}

            <div className="p-5">
              <h1 className="font-display font-bold text-xl text-gray-900 mb-1">{game.name}</h1>
              {range?.heading && <p className="text-sm text-gray-500 mb-4">{range.heading}</p>}

              <div className="space-y-3 mb-5">
                {range?.price && (
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Price/ticket</span>
                    <span className="text-sm font-semibold text-gray-900">₹{Number(range.price).toLocaleString('en-IN')}</span>
                  </div>
                )}
                {range?.jackpot && (
                  <div className="flex flex-col gap-0.5">
                    <span className="text-xs text-gray-500 uppercase tracking-wider">Jackpot</span>
                    <span className="text-sm font-bold text-brand-600">{stripHtml(range.jackpot)}</span>
                  </div>
                )}
                {range?.result_date && (
                  <div className="space-y-2">
                    <div className="flex justify-between">
                      <span className="text-sm text-gray-500">Draw date</span>
                      <span className="text-sm text-gray-900">{new Date(range.result_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                    </div>
                    {countdown && !countdown.ended && (
                      <div className="bg-gray-100 border border-gray-200 rounded-xl p-3">
                        <p className="text-[10px] text-gray-500 uppercase tracking-widest mb-2 text-center">Draw closes in</p>
                        <div className="grid grid-cols-4 gap-1.5">
                          {[{ v: countdown.days, l: 'Days' }, { v: countdown.hours, l: 'Hrs' }, { v: countdown.minutes, l: 'Min' }, { v: countdown.seconds, l: 'Sec' }].map(({ v, l }) => (
                            <div key={l} className="flex flex-col items-center bg-white border border-gray-200 rounded-lg py-2">
                              <span className="text-lg font-bold text-brand-600 tabular-nums leading-none">{String(v).padStart(2, '0')}</span>
                              <span className="text-[9px] text-gray-500 mt-0.5 uppercase tracking-wider">{l}</span>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}
                    {countdown?.ended && (
                      <p className="text-xs text-center text-yellow-400/80 bg-yellow-400/10 rounded-lg py-1.5 px-2">Draw has ended</p>
                    )}
                  </div>
                )}
                {range?.play_description && (
                  <div className="pt-2 border-t border-gray-200 text-xs text-gray-600 leading-relaxed [&_p]:mb-2 [&_p:last-child]:mb-0 [&_strong]:text-gray-700 [&_ul]:list-disc [&_ul]:pl-4 [&_ul]:space-y-1 [&_ol]:list-decimal [&_ol]:pl-4 [&_ol]:space-y-1"
                    dangerouslySetInnerHTML={{ __html: range.play_description }}/>
                )}
              </div>

              {/* Selection summary */}
              {selected.length > 0 && (
                <div className="bg-brand-50 border border-brand-200 rounded-xl p-3 mb-4">
                  <p className="text-xs text-brand-600 font-semibold mb-1">{selected.length} ticket(s) selected</p>
                  <p className="text-sm font-bold text-gray-900">
                    Total: ₹{(selected.length * Number(range?.price || 0)).toLocaleString('en-IN')}
                  </p>
                </div>
              )}

              <button onClick={handleAddToCart} disabled={selected.length === 0 || adding}
                className={`hidden lg:flex items-center justify-center gap-2 w-full transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed ${
                  addSuccess ? 'bg-emerald-500 text-white px-6 py-4 rounded-xl font-bold text-base scale-[1.02]' : 'btn-primary !py-4 !text-base'
                }`}>
                {adding ? (
                  <><svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Adding…</>
                ) : addSuccess ? (
                  <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7"/></svg>Added to Cart!</>
                ) : (
                  <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-9H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</>
                )}
              </button>
              {cartCount > 0 && (
                <Link to="/cart" className="hidden lg:flex items-center justify-center gap-2 w-full mt-2 py-3 rounded-xl font-semibold text-sm border border-brand-500 text-brand-600 hover:bg-brand-50 transition-colors">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-9H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                  View Cart · {cartCount} ticket{cartCount !== 1 ? 's' : ''} · ₹{cartTotal.toLocaleString('en-IN')}
                </Link>
              )}
            </div>
          </div>
        </div>

        {/* Right: Ticket series + grid */}
        <div className="lg:col-span-2">
          {/* Search */}
          <div className="card p-4 mb-5">
            <p className="text-sm font-semibold text-gray-900 mb-3">Search Ticket Number</p>
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
              <div className="mt-3">
                {searchResult === 'error' ? (
                  <div className="p-3 rounded-xl text-sm bg-red-500/10 text-red-400">Error searching ticket.</div>
                ) : searchResult?.available ? (
                  <div className="flex items-center gap-3">
                    <button
                      onClick={() => {
                        const num = Number(searchNum.trim());
                        if (!isNaN(num)) toggleTicket(num);
                      }}
                      className={`h-11 px-5 rounded-lg text-sm font-semibold transition-all active:scale-95 ${
                        selected.includes(Number(searchNum.trim()))
                          ? 'bg-brand-500/30 border-2 border-brand-500 text-brand-600 shadow-sm shadow-brand-500/30'
                          : 'bg-gray-50 border border-green-300 text-gray-700 hover:border-brand-400 hover:text-gray-900'
                      }`}>
                      {searchNum.trim()}
                    </button>
                    <span className="text-sm text-emerald-500 font-medium">
                      {selected.includes(Number(searchNum.trim())) ? '✅ Selected' : 'Available — click to select'}
                    </span>
                  </div>
                ) : (
                  <div className="p-3 rounded-xl text-sm bg-yellow-500/10 ">❌ Ticket #{searchNum} is not available.</div>
                )}
              </div>
            )}
          </div>
          {/* Segment selector — mirrors PHP's "Event Ticket Series" range cards */}
          {segments.length > 0 && (
            <div className="card p-4 mb-5">
              <p className="text-sm font-semibold text-gray-900 mb-3">Event Ticket Series</p>
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                {segments.map(([s, e], idx) => (
                  <button
                    key={idx}
                    onClick={() => handleSegmentSelect(idx)}
                    className={`py-2.5 px-3 rounded-xl border text-sm font-semibold transition-all ${
                      segIdx === idx
                        ? 'bg-brand-500/20 border-brand-500 text-brand-600'
                        : 'bg-gray-100 border-gray-200 text-gray-600 hover:border-brand-400 hover:text-gray-900'
                    }`}>
                    {s}–{e}
                  </button>
                ))}
              </div>
            </div>
          )}



          {/* Grid header */}
          {currentSeg && (
            <div className="flex items-center justify-between mb-4">
              <p className="text-sm text-gray-500">
                Showing <span className="text-gray-900 font-semibold">{currentSeg[0] + page * PAGE_SIZE}</span>–
                <span className="text-gray-900 font-semibold">{Math.min(currentSeg[0] + (page + 1) * PAGE_SIZE - 1, currentSeg[1])}</span>
              </p>
              <div className="flex items-end gap-3">
                <div className="flex flex-col items-center gap-1">
                  <span className="w-3 h-3 rounded-sm bg-green-300 border border-green-600 inline-block"/>
                  <span className="text-[10px] text-gray-500">Available</span>
                </div>
                <div className="flex flex-col items-center gap-1">
                  <span className="w-3 h-3 rounded-sm bg-red-400 border border-red-600 inline-block"/>
                  <span className="text-[10px] text-gray-500">Sold</span>
                </div>
                <div className="flex flex-col items-center gap-1">
                  <span className="w-3 h-3 rounded-sm bg-brand-500 border border-brand-700 inline-block"/>
                  <span className="text-[10px] text-gray-500">Selected</span>
                </div>
              </div>
            </div>
          )}

          {/* Ticket grid — each item is { num, available } */}
          {ticketsLoading ? (
            <div className="flex flex-col items-center justify-center py-16">
              <svg className="w-8 h-8 animate-spin text-brand-500 mb-3" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              <p className="text-sm text-gray-400">Loading tickets…</p>
            </div>
          ) : (
          <div className="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 gap-2">
            {tickets.map(({ num, available }) => {
              const sel = selected.includes(num);
              return (
                <button
                  key={num}
                  disabled={!available}
                  onClick={() => toggleTicket(num)}
                  className={`h-11 rounded-lg text-sm font-semibold transition-all active:scale-95 ${
                    !available ? 'bg-red-50 border border-red-200 text-red-400 cursor-not-allowed' :
                    sel        ? 'bg-brand-500/30 border-2 border-brand-500 text-brand-600 shadow-sm shadow-brand-500/30' :
                                 'bg-gray-50 border border-green-300 text-gray-700 hover:border-brand-400 hover:text-gray-900'
                  }`}>
                  {num}
                </button>
              );
            })}
          </div>
          )}

          {/* Pagination within segment */}
          {totalPages > 1 && (
            <div className="flex items-center justify-center gap-2 mt-6">
              <button
                onClick={() => fetchTickets(page - 1)}
                disabled={page === 0}
                className="btn-secondary px-3 py-2 text-xs disabled:opacity-30">
                ← Prev
              </button>
              <span className="text-sm text-gray-500">
                Page <span className="text-gray-900 font-semibold">{page + 1}</span> / {totalPages}
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

      {/* Fixed bottom bar — mobile only */}
      <div className={`fixed bottom-0 left-0 right-0 z-40 lg:hidden transition-transform duration-300 ${
        selected.length > 0 || cartCount > 0 ? 'translate-y-0' : 'translate-y-full'
      }`}>
        <div className="bg-white/95 backdrop-blur-md border-t border-gray-200 px-4 py-3">
          {selected.length > 0 ? (
          <div className="flex items-center gap-3 max-w-lg mx-auto">
            <div className="flex-1 min-w-0">
              <p className="text-xs text-gray-500">{selected.length} ticket{selected.length !== 1 ? 's' : ''} selected</p>
              <p className="text-base font-bold text-gray-900">₹{(selected.length * Number(range?.price || 0)).toLocaleString('en-IN')}</p>
            </div>
            <button
              onClick={handleAddToCart}
              disabled={adding}
              className={`flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300 active:scale-95 disabled:opacity-60 ${
                addSuccess
                  ? 'bg-emerald-500 text-white scale-105'
                  : 'bg-brand-500 hover:bg-brand-600 text-white'
              }`}>
              {adding ? (
                <><svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Adding…</>
              ) : addSuccess ? (
                <><svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7"/></svg>Added!</>
              ) : (
                <><svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-9H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</>
              )}
            </button>
          </div>
          ) : (
          <Link to="/cart" className="flex items-center justify-center gap-2 max-w-lg mx-auto py-3 rounded-xl font-bold text-sm bg-brand-500 hover:bg-brand-600 text-white transition-colors active:scale-95">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-9H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            View Cart · {cartCount} ticket{cartCount !== 1 ? 's' : ''} · ₹{cartTotal.toLocaleString('en-IN')}
          </Link>
          )}
        </div>
      </div>

      {/* Lightbox */}
      {lightbox && slideImgs.length > 0 && (
        <div
          className="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
          onClick={() => setLightbox(false)}>
          <button
            onClick={() => setLightbox(false)}
            className="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white text-xl transition-colors">
            ✕
          </button>
          {slideImgs.length > 1 && (
            <button
              onClick={e => { e.stopPropagation(); setSlideIdx(prev => (prev - 1 + slideImgs.length) % slideImgs.length); }}
              className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white text-3xl transition-colors">
              ‹
            </button>
          )}
          <img
            src={`${import.meta.env.VITE_PUBLIC_URL}/imglogo/${slideImgs[slideIdx]}`}
            alt={game.name}
            className="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl"
            onClick={e => e.stopPropagation()}
          />
          {slideImgs.length > 1 && (
            <button
              onClick={e => { e.stopPropagation(); setSlideIdx(prev => (prev + 1) % slideImgs.length); }}
              className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white text-3xl transition-colors">
              ›
            </button>
          )}
          {slideImgs.length > 1 && (
            <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
              {slideImgs.map((_, i) => (
                <button
                  key={i}
                  onClick={e => { e.stopPropagation(); setSlideIdx(i); }}
                  className={`w-2 h-2 rounded-full transition-colors ${i === slideIdx ? 'bg-white' : 'bg-white/30'}`}
                />
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
