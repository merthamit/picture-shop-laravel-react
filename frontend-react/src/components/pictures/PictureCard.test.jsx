import React from "react";
import { describe, expect, it, vi } from "vitest";
import { fireEvent, screen } from "@testing-library/react";
import PictureCard from "./PictureCard";
import { renderWithStore } from "../../test/renderWithStore";

vi.mock("react-toastify", () => ({ toast: { success: vi.fn(), info: vi.fn(), error: vi.fn() } }));

const picture = {
  id: 7,
  title: "Ocean Breeze",
  price: 22,
  thumbnail_path: "https://cdn.example/thumb.jpg",
  category: { id: 1, name: "Nature" },
  user: { id: 3, name: "Alex Morgan" },
  rating: 4.5,
  reviews_count: 2,
  sales_count: 5,
};

describe("PictureCard", () => {
  it("shows the picture with its thumbnail, price and rating", () => {
    renderWithStore(<PictureCard picture={picture} />);

    expect(screen.getByText("Ocean Breeze")).toBeInTheDocument();
    expect(screen.getByText("$22")).toBeInTheDocument();
    expect(screen.getByAltText("Ocean Breeze")).toHaveAttribute("src", picture.thumbnail_path);
    expect(screen.getByText("5 sold")).toBeInTheDocument();
    expect(screen.getByRole("link")).toHaveAttribute("href", "/picture/7");
  });

  it("is translated to Turkish", () => {
    renderWithStore(<PictureCard picture={picture} />, { state: { ui: { lang: "tr" } } });

    expect(screen.getByText("Doğa")).toBeInTheDocument();
    expect(screen.getByText("5 satış")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Sepete ekle" })).toBeInTheDocument();
  });

  it("adds the picture to the cart with the quick add button", () => {
    const { store } = renderWithStore(<PictureCard picture={picture} />);

    fireEvent.click(screen.getByRole("button", { name: "Add to cart" }));

    expect(store.getState().cart.cartItems).toEqual([
      { id: 7, title: "Ocean Breeze", price: 22, image: picture.thumbnail_path },
    ]);
  });

  it("marks pictures the user already owns and hides quick add", () => {
    renderWithStore(<PictureCard picture={picture} />, {
      state: { user: { isLoggedIn: true, token: "x", user: { id: 3, orders: [], favorite_ids: [7] } } },
    });

    expect(screen.getByText("Owned")).toBeInTheDocument();
    expect(screen.queryByRole("button", { name: "Add to cart" })).not.toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Remove from favorites" })).toHaveAttribute("aria-pressed", "true");
  });
});
