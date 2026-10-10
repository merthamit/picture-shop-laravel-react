import { describe, expect, it, vi } from "vitest";
import cartReducer, { addItemToCart, addToCart, removeFromCart, removeItemFromCart, setCartItems } from "./cartSlice";
import { makeStore } from "../../../test/renderWithStore";

vi.mock("react-toastify", () => ({ toast: { success: vi.fn(), info: vi.fn(), error: vi.fn() } }));

const sunset = { id: 1, title: "Sunset", price: 15, image: "a.jpg" };
const forest = { id: 2, title: "Forest", price: 20, image: "b.jpg" };

describe("cart reducer", () => {
  it("adds items once", () => {
    let state = cartReducer(undefined, addToCart(sunset));
    state = cartReducer(state, addToCart(sunset));
    expect(state.cartItems).toEqual([sunset]);
  });

  it("removes and replaces items", () => {
    let state = cartReducer({ cartItems: [sunset, forest] }, removeFromCart(sunset));
    expect(state.cartItems).toEqual([forest]);
    state = cartReducer(state, setCartItems([sunset]));
    expect(state.cartItems).toEqual([sunset]);
  });
});

describe("cart thunks for guests", () => {
  it("keeps the cart locally when nobody is logged in", async () => {
    const store = makeStore();
    await store.dispatch(addItemToCart(sunset));
    await store.dispatch(addItemToCart(forest));
    expect(store.getState().cart.cartItems.map((item) => item.id)).toEqual([2, 1]);

    await store.dispatch(removeItemFromCart(sunset));
    expect(store.getState().cart.cartItems).toEqual([forest]);
  });
});
