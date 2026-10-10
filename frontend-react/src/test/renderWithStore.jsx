import React from "react";
import { render } from "@testing-library/react";
import { Provider } from "react-redux";
import { MemoryRouter } from "react-router-dom";
import { combineReducers, configureStore } from "@reduxjs/toolkit";
import userReducer from "../components/redux/slices/userSlice";
import cartReducer from "../components/redux/slices/cartSlice";
import uiReducer from "../components/redux/slices/uiSlice";

export function makeStore(preloadedState = {}) {
  return configureStore({
    reducer: combineReducers({ user: userReducer, cart: cartReducer, ui: uiReducer }),
    preloadedState: {
      user: { isLoggedIn: false, token: "", user: null },
      cart: { cartItems: [] },
      ui: { lang: "en" },
      ...preloadedState,
    },
  });
}

export function renderWithStore(ui, { state, store = makeStore(state) } = {}) {
  return {
    store,
    ...render(
      <Provider store={store}>
        <MemoryRouter>{ui}</MemoryRouter>
      </Provider>
    ),
  };
}
