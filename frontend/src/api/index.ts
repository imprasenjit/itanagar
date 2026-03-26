import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE ?? '/api',
    withCredentials: true,
    headers: { 'Content-Type': 'application/json' },
});

// ── Public ────────────────────────────────────────────────────────────────────
export const getHome = () => api.get('/home');
export const getGames = () => api.get('/games');
export const getGameDetail = (id: string | number) => api.get(`/games/${id}`);
export const getGameTickets = (webId: string | number, s: number, e: number) => api.get(`/games/${webId}/tickets/${s}/${e}`);
export const searchTickets = (webId: string | number, data: Record<string, unknown>) => api.post(`/games/${webId}/tickets/search`, data);
export const getFaq = () => api.get('/faq');
export const getPage = (type: string) => api.get(`/page/${type}`);
export const getResults = (params: Record<string, string>) => api.get('/results', { params });
export const postContact = (data: Record<string, string>) => api.post('/contact', data);

// ── Auth ─────────────────────────────────────────────────────────────────────
export const getMe = () => api.get('/auth/me');
export const login = (data: { email: string; password: string }) => api.post('/auth/login', data);
export const logout = () => api.post('/auth/logout');
export const register = (data: Record<string, string>) => api.post('/auth/register', data);
export const forgotPassword = (data: { email: string }) => api.post('/auth/forgot-password', data);
export const resetPassword  = (data: { email: string; activation_code: string; password: string; password_confirmation: string }) => api.post('/auth/reset-password', data);

// ── Cart ─────────────────────────────────────────────────────────────────────
export const getCart = () => api.get('/cart');
export const addToCart = (data: Record<string, unknown>) => api.post('/cart/add', data);
export const removeCartItem = (id: number) => api.delete(`/cart/${id}`);

// ── Payment ──────────────────────────────────────────────────────────────────
export const getOrderConfirm = () => api.get('/order/confirm');
interface GuestInfo { fname?: string; address?: string; mobile?: string; email?: string; }
export const createPayment = (guestData?: GuestInfo) => api.post('/payment/create', guestData ?? {});
export const confirmPayment = (data: Record<string, unknown>) => api.post('/payment/confirm', data);
export const cancelPayment = (data: Record<string, unknown>) => api.post('/payment/cancel', data);

// ── Account ──────────────────────────────────────────────────────────────────
export const getProfile = () => api.get('/account/profile');
export const updateProfile = (data: Record<string, string>) => api.post('/account/profile', data);
export const updatePassword = (data: Record<string, string>) => api.post('/account/password', data);
export const getWallet = () => api.get('/account/wallet');
export const walletTopup = (data: Record<string, unknown>) => api.post('/account/wallet/topup', data);
export const getOrders = () => api.get('/account/orders');
export const getRefunds = () => api.get('/account/refunds');
export const createRefund = (data: Record<string, string>) => api.post('/account/refunds', data);
export const getWithdrawals = () => api.get('/account/withdrawals');
export const createWithdrawal = (data: Record<string, string>) => api.post('/account/withdrawals', data);
export const getTransfers = () => api.get('/account/transfers');
export const createTransfer = (data: Record<string, string>) => api.post('/account/transfers', data);
export const getWinners = () => api.get('/account/winners');

export default api;
