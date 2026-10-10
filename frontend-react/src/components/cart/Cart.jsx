import React from "react";
import { useDispatch, useSelector } from "react-redux";
import { Link } from "react-router-dom";
import { removeItemFromCart } from "../redux/slices/cartSlice";
import useTitle from "../custom/useTitle";
import EmptyState from "../layouts/EmptyState";
import { useT } from "../../i18n";

export function OrderSummary({ cartItems, children }) {
  const total = cartItems.reduce((acc, item) => acc + Number(item.price), 0);
  const t = useT();

  return (
    <div className="surface surface-pad">
      <h5 className="mb-3">{t("cart.summary")}</h5>
      <div className="summary-row">
        <span>
          {cartItems.length === 1 ? t("cart.itemsOne") : t("cart.items", { count: cartItems.length })}
        </span>
        <span>${total}</span>
      </div>
      <div className="summary-row">
        <span>{t("cart.license")}</span>
        <span>{t("cart.standard")}</span>
      </div>
      <div className="summary-row summary-total">
        <span>{t("cart.total")}</span>
        <span>${total}</span>
      </div>
      {children}
    </div>
  );
}

const Cart = () => {
  const { cartItems } = useSelector((state) => state.cart);
  const { isLoggedIn } = useSelector((state) => state.user);
  const dispatch = useDispatch();
  const t = useT();
  useTitle(t("cart.title"));

  if (!cartItems.length) {
    return (
      <div className="container page">
        <div className="surface">
          <EmptyState
            icon="bi-bag"
            title={t("cart.emptyTitle")}
            text={t("cart.emptyText")}
          >
            <Link to="/" className="btn btn-primary">
              <i className="bi bi-compass"></i> {t("cart.explore")}
            </Link>
          </EmptyState>
        </div>
      </div>
    );
  }

  return (
    <div className="container page">
      <h1 className="page-title">{t("cart.title")}</h1>
      <p className="page-subtitle">{t("cart.subtitle")}</p>

      <div className="row g-4">
        <div className="col-lg-8">
          <div className="surface px-4 py-2">
            {cartItems.map((item) => (
              <div className="cart-item" key={item.id}>
                <Link to={`/picture/${item.id}`}>
                  <img src={item.image} alt={item.title} />
                </Link>
                <div className="flex-grow-1 min-w-0">
                  <Link to={`/picture/${item.id}`} className="fw-bold text-ink d-block text-truncate">
                    {item.title}
                  </Link>
                  <span className="small text-muted">{t("cart.lineInfo")}</span>
                </div>
                <span className="price-tag">${item.price}</span>
                <button
                  className="btn btn-sm btn-icon btn-ghost border-0 text-danger"
                  onClick={() => dispatch(removeItemFromCart(item))}
                  aria-label={t("cart.remove", { title: item.title })}
                >
                  <i className="bi bi-trash3"></i>
                </button>
              </div>
            ))}
          </div>
          <Link to="/" className="btn btn-link text-decoration-none mt-2 px-0">
            <i className="bi bi-arrow-left"></i> {t("cart.continue")}
          </Link>
        </div>
        <div className="col-lg-4">
          <OrderSummary cartItems={cartItems}>
            <div className="d-grid mt-3">
              <Link to={isLoggedIn ? "/checkout" : "/login"} className="btn btn-gradient btn-lg">
                <i className="bi bi-lock"></i> {isLoggedIn ? t("cart.checkout") : t("cart.loginToCheckout")}
              </Link>
            </div>
            <div className="small text-muted text-center mt-3">
              <i className="bi bi-shield-check"></i> {t("cart.secure")}
            </div>
          </OrderSummary>
        </div>
      </div>
    </div>
  );
};

export default Cart;
