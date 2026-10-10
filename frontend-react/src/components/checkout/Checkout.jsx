import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { Link, useNavigate } from "react-router-dom";
import Stripe from "./Stripe";
import useTitle from "../custom/useTitle";
import { OrderSummary } from "../cart/Cart";
import { useT } from "../../i18n";

const Checkout = () => {
  const { isLoggedIn } = useSelector((state) => state.user);
  const { cartItems } = useSelector((state) => state.cart);
  const navigate = useNavigate();
  const t = useT();
  useTitle(t("checkout.title"));

  useEffect(() => {
    if (!isLoggedIn) navigate("/login");
    else if (!cartItems.length) navigate("/cart");
  }, [isLoggedIn, cartItems.length, navigate]);

  return (
    <div className="container page">
      <Link to="/cart" className="small fw-semibold text-muted">
        <i className="bi bi-arrow-left"></i> {t("checkout.back")}
      </Link>
      <h1 className="page-title mt-2">{t("checkout.title")}</h1>
      <p className="page-subtitle">{t("checkout.subtitle")}</p>

      <div className="row g-4">
        <div className="col-lg-7">
          <div className="surface surface-pad">
            <h5 className="mb-3">
              <i className="bi bi-credit-card me-2"></i>{t("checkout.payment")}
            </h5>
            <Stripe />
          </div>
        </div>
        <div className="col-lg-5">
          <OrderSummary cartItems={cartItems}>
            <div className="mt-3">
              {cartItems.map((item) => (
                <div key={item.id} className="d-flex align-items-center gap-2 py-1 small">
                  <img src={item.image} alt="" width={40} height={30} className="rounded object-fit-cover" />
                  <span className="text-truncate flex-grow-1">{item.title}</span>
                  <span className="fw-semibold">${item.price}</span>
                </div>
              ))}
            </div>
          </OrderSummary>
        </div>
      </div>
    </div>
  );
};

export default Checkout;
