import { createSlice } from "@reduxjs/toolkit";
import { toast } from "react-toastify";

const initialState = {
  cartItems: [],
};

export const cartSlice = createSlice({
  name: "cart",
  initialState,
  reducers: {
    addToCart(state, action) {
      const item = action.payload;
      const exists = state.cartItems.find((picture) => picture.id == item.id);
      if (exists) {
        toast.info("Picture already your cart.", {
          position: "top-right",
        });
      } else {
        state.cartItems = [item, ...state.cartItems];
        toast.success("Picture added to the cart.", {
          position: "top-right",
        });
      }
    },
    removeFromCart(state, action) {
      const item = action.payload;
      state.cartItems = state.cartItems.filter(
        (picture) => picture.id != item.id
      );
      toast.success("Picture removed from the cart.", {
        position: "top-right",
      });
    },
    clearCartItems(state, action) {
      state.cartItems = [];
    },
  },
});

const cartReducer = cartSlice.reducer;

export const { addToCart, removeFromCart, clearCartItems } = cartSlice.actions;

export default cartReducer;
