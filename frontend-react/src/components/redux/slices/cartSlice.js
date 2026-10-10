import { createSlice } from "@reduxjs/toolkit";
import axios from "axios";
import { toast } from "react-toastify";
import { BASE_URL, getConfig } from "../../../helpers/config";
import { translate } from "../../../i18n";

const initialState = {
  cartItems: [],
};

export const cartSlice = createSlice({
  name: "cart",
  initialState,
  reducers: {
    addToCart(state, action) {
      const item = action.payload;
      if (!state.cartItems.some((picture) => picture.id == item.id)) {
        state.cartItems = [item, ...state.cartItems];
      }
    },
    removeFromCart(state, action) {
      const item = action.payload;
      state.cartItems = state.cartItems.filter(
        (picture) => picture.id != item.id
      );
    },
    clearCartItems(state, action) {
      state.cartItems = [];
    },
    setCartItems(state, action) {
      state.cartItems = action.payload;
    },
  },
});

const cartReducer = cartSlice.reducer;

export const { addToCart, removeFromCart, clearCartItems, setCartItems } =
  cartSlice.actions;

/*
 * Cart session thunks. Logged-in users' carts live in Redis on the API so
 * they survive across devices; guests keep using the local (persisted) cart.
 */
export const addItemToCart = (item) => async (dispatch, getState) => {
  const { token } = getState().user;
  const t = (key) => translate(getState().ui.lang, key);

  if (getState().cart.cartItems.some((picture) => picture.id == item.id)) {
    return toast.info(t("cart.already"));
  }
  if (!token) {
    dispatch(addToCart(item));
    return toast.success(t("cart.added"));
  }
  try {
    const response = await axios.post(
      `${BASE_URL}/cart`,
      { picture_id: item.id },
      getConfig(token)
    );
    dispatch(setCartItems(response.data.cartItems));
    toast.success(t("cart.added"));
  } catch (error) {
    console.log(error);
  }
};

export const removeItemFromCart = (item) => async (dispatch, getState) => {
  const { token } = getState().user;
  const t = (key) => translate(getState().ui.lang, key);
  if (!token) {
    dispatch(removeFromCart(item));
    return toast.success(t("cart.removed"));
  }

  try {
    const response = await axios.delete(
      `${BASE_URL}/cart/${item.id}`,
      getConfig(token)
    );
    dispatch(setCartItems(response.data.cartItems));
    toast.success(t("cart.removed"));
  } catch (error) {
    console.log(error);
  }
};

// Drop pictures the user already owns or bought (e.g. paid on another device)
export const pruneOwnedItems = (user) => async (dispatch, getState) => {
  if (!user) return;
  const owned = new Set([
    ...(user.orders || []).map((order) => order.picture.id),
    ...(user.pictures || []).map((picture) => picture.id),
  ]);
  const stale = getState().cart.cartItems.filter((item) => owned.has(item.id));
  for (const item of stale) {
    const { token } = getState().user;
    if (token) {
      await axios.delete(`${BASE_URL}/cart/${item.id}`, getConfig(token)).catch(() => {});
    }
    dispatch(removeFromCart(item));
  }
};

// After login: push any guest items to the server cart, then load it.
export const syncCart = () => async (dispatch, getState) => {
  const { token } = getState().user;
  if (!token) return;

  try {
    const guestItems = getState().cart.cartItems;
    for (const item of guestItems) {
      await axios.post(
        `${BASE_URL}/cart`,
        { picture_id: item.id },
        getConfig(token)
      );
    }
    const response = await axios.get(`${BASE_URL}/cart`, getConfig(token));
    dispatch(setCartItems(response.data.cartItems));
  } catch (error) {
    console.log(error);
  }
};

export default cartReducer;
