import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: { 'Content-Type': 'application/json' },
});

// ── Public ────────────────────────────────────────────────────────────────────
export const getHome = () => api.get('/home');
export const getGames = () => api.get('/games');
export const getGameDetail = (id) => api.get(`/games/${id}`);
export const getGameTickets = (webId, s, e) => api.get(`/games/${webId}/tickets/${s}/${e}`);
export const searchTickets = (webId, data) => api.post(`/games/${webId}/tickets/search`, data);
export const getFaq = () => api.get('/faq');
export const getPage = (type) => api.get(`/page/${type}`);
export const getResults = (params) => api.get('/results', { params });
export const postContact = (data) => api.post('/contact', data);

// ── Auth ─────────────────────────────────────────────────────────────────────
export const getMe = () => api.get('/auth/me');
export const login = (data) => api.post('/auth/login', data);
export const logout = () => api.post('/auth/logout');
export const register = (data) => api.post('/auth/register', data);
export const forgotPassword = (data) => api.post('/auth/forgot-password', data);

// ── Cart ─────────────────────────────────────────────────────────────────────
export const getCart = () => api.get('/cart');
export const addToCart = (data) => api.post('/cart/add', data);
export const removeCartItem = (id) => api.delete(`/cart/${id}`);

// ── Payment ──────────────────────────────────────────────────────────────────
export const getOrderConfirm = () => api.get('/order/confirm');
export const createPayment = () => api.post('/payment/create');
export const confirmPayment = (data) => api.post('/payment/confirm', data);
export const cancelPayment = (data) => api.post('/payment/cancel', data);

// ── Account ──────────────────────────────────────────────────────────────────
export const getProfile = () => api.get('/account/profile');
export const updateProfile = (data) => api.post('/account/profile', data);
export const updatePassword = (data) => api.post('/account/password', data);
export const getWallet = () => api.get('/account/wallet');
export const walletTopup = (data) => api.post('/account/wallet/topup', data);
export const getOrders = () => api.get('/account/orders');
export const getRefunds = () => api.get('/account/refunds');
export const createRefund = (data) => api.post('/account/refunds', data);
export const getWithdrawals = () => api.get('/account/withdrawals');
export const createWithdrawal = (data) => api.post('/account/withdrawals', data);
export const getTransfers = () => api.get('/account/transfers');
export const createTransfer = (data) => api.post('/account/transfers', data);
export const getWinners = () => api.get('/account/winners');

export default api;
