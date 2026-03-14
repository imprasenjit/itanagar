import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { getCart, addToCart as apiAdd, removeCartItem as apiRemove } from '../api';
import { useAuth } from './AuthContext';

const CartContext = createContext(null);

export function CartProvider({ children }) {
  const { user, loading: authLoading } = useAuth();
  const [items, setItems]              = useState([]);
  const [cartLoading, setCartLoading]  = useState(false);

  const fetchCart = useCallback(async () => {
    setCartLoading(true);
    try {
      const { data } = await getCart();
      // cart response: { status, data: { items: [...], total, count } }
      setItems(data.status ? (data.data?.items || []) : []);
    } catch {
      setItems([]);
    } finally {
      setCartLoading(false);
    }
  }, []);

  // Wait for auth to resolve before fetching so we get the right cart
  // (guest cart vs logged-in user cart)
  useEffect(() => {
    if (!authLoading) fetchCart();
  }, [fetchCart, user, authLoading]);

  const addToCart = async (payload) => {
    const { data } = await apiAdd(payload);
    if (data.status) { await fetchCart(); return data; }
    throw new Error(data.message || 'Could not add to cart');
  };

  const removeItem = async (id) => {
    await apiRemove(id);
    await fetchCart();
  };

  const count = items.length;

  return (
    <CartContext.Provider value={{ items, count, cartLoading, addToCart, removeItem, fetchCart }}>
      {children}
    </CartContext.Provider>
  );
}

export const useCart = () => useContext(CartContext);
