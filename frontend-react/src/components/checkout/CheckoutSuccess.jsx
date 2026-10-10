import React, { useEffect, useState } from "react";
import axios from "axios";
import { useDispatch, useSelector } from "react-redux";
import { Link, Navigate, useLocation } from "react-router-dom";
import { BASE_URL, getConfig } from "../../helpers/config";
import { setCurrentUser } from "../redux/slices/userSlice";
import { clearCartItems } from "../redux/slices/cartSlice";
import useTitle from "../custom/useTitle";
import { useT } from "../../i18n";

/**
 * Receipt shown right after a successful Stripe payment. Orders are
 * completed asynchronously by the RabbitMQ worker, so we poll the user
 * until every purchased picture is ready to download.
 */
export default function CheckoutSuccess() {
  const t = useT();
  const dispatch = useDispatch();
  const { state } = useLocation();
  const { token, user } = useSelector((s) => s.user);
  const [ready, setReady] = useState(false);
  useTitle(t("success.title"));

  // The order is placed: empty the local cart (the API already cleared the Redis one)
  useEffect(() => {
    if (state) dispatch(clearCartItems());
  }, []);

  const ids = state?.items?.map((item) => item.id) || [];
  const orders = (user?.orders || []).filter((order) => ids.includes(order.picture.id));
  const allCompleted = orders.length >= ids.length && orders.every((o) => o.status === "completed");

  useEffect(() => {
    if (!state || allCompleted) {
      setReady(allCompleted);
      return;
    }
    const timer = setInterval(async () => {
      try {
        const response = await axios.get(`${BASE_URL}/user`, getConfig(token));
        dispatch(setCurrentUser(response.data.user));
      } catch (error) {
        console.log(error);
      }
    }, 1500);
    return () => clearInterval(timer);
  }, [allCompleted]);

  if (!state) return <Navigate to="/profile" replace />;

  const paidAt = new Date(state.paidAt).toLocaleString(t.lang === "tr" ? "tr-TR" : "en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });

  return (
    <div className="container page">
      <div className="success-wrap">
        <div className="surface success-card">
          <div className="success-hero">
            <div className="success-check">
              <svg viewBox="0 0 52 52" aria-hidden="true">
                <circle cx="26" cy="26" r="24" />
                <path d="M15 27l7 7 15-16" />
              </svg>
            </div>
            <h1 className="h3 fw-800 mb-1">{t("success.title")}</h1>
            <p className="text-muted mb-0">{t("success.subtitle", { email: user?.email })}</p>
          </div>

          <div className="success-meta">
            <div>
              <small>{t("success.amount")}</small>
              <strong>${state.total}</strong>
            </div>
            <div>
              <small>{t("success.date")}</small>
              <strong>{paidAt}</strong>
            </div>
            <div>
              <small>{t("success.reference")}</small>
              <strong className="text-truncate" title={state.paymentId}>
                {state.paymentId.slice(-10).toUpperCase()}
              </strong>
            </div>
          </div>

          <div className="success-items">
            {state.items.map((item) => {
              const order = orders.find((o) => o.picture.id === item.id);
              const done = order?.status === "completed";
              return (
                <div className="success-item" key={item.id}>
                  <img src={item.image} alt="" />
                  <div className="flex-grow-1 min-w-0">
                    <div className="fw-semibold text-ink text-truncate">{item.title}</div>
                    <span className={`status-dot small ${done ? "text-success" : "text-warning"}`}>
                      {done ? t("success.ready") : t("success.preparing")}
                    </span>
                  </div>
                  <span className="fw-bold me-2">${item.price}</span>
                  <Link
                    to={`/picture/${item.id}`}
                    className={`btn btn-sm ${done ? "btn-soft" : "btn-ghost disabled"}`}
                    aria-disabled={!done}
                  >
                    {done ? <i className="bi bi-download"></i> : <span className="spinner-border spinner-border-sm"></span>}
                    {t("profile.download")}
                  </Link>
                </div>
              );
            })}
          </div>

          <div className={`alert ${ready ? "alert-success" : "alert-info"} d-flex gap-2 align-items-center small`}>
            <i className={`bi ${ready ? "bi-envelope-check" : "bi-hourglass-split"}`}></i>
            {ready ? t("success.emailSent") : t("success.processing")}
          </div>

          <div className="d-flex gap-2 flex-wrap">
            <Link to="/profile" className="btn btn-gradient flex-fill">
              <i className="bi bi-collection"></i> {t("success.goDownloads")}
            </Link>
            <Link to="/" className="btn btn-ghost flex-fill">
              <i className="bi bi-compass"></i> {t("cart.continue")}
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
