// Shared domain types

export interface User {
  id: number;
  name: string;
  email: string;
  mobile?: string;
  role?: number;
}

export interface Game {
  id: number;
  name: string;
  logo?: string;
  logo2?: string;
  heading?: string;
  price: string | number;
  jackpot?: string;
  date?: string;
  result_date?: string;
  totalTickets?: number;
  soldTickets?: number;
  rangeStart?: string;
}

export interface CartItem {
  id: number;
  name: string;
  ticket_no: string | number;
  total_price: string | number;
  [key: string]: unknown;
}

export interface ApiResponse<T = unknown> {
  status: boolean;
  message?: string;
  data: T;
}
