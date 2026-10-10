import React, { useEffect, useState } from "react";
import { loadStripe } from "@stripe/stripe-js";
import { Elements } from "@stripe/react-stripe-js";
import axios from "axios";
import { BASE_URL, getConfig } from "../../helpers/config";
import { useSelector } from "react-redux";
import CheckoutForm from "./CheckoutForm";
import Spinner from "../layouts/Spinner";
import { useT } from "../../i18n";

// Publishable key comes from .env (VITE_STRIPE_PUBLISHABLE_KEY) at build time
const publishableKey = import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY;
const stripePromise = publishableKey ? loadStripe(publishableKey) : null;

export default function Stripe() {
  const [clientSecret, setClientSecret] = useState("");
  const [error, setError] = useState("");
  const { cartItems } = useSelector((state) => state.cart);
  const { token } = useSelector((state) => state.user);
  const t = useT();

  useEffect(() => {
    if (!stripePromise) {
      setError(t("checkout.notConfigured"));
      return;
    }
    fetchClientSecret();
  }, []);

  const fetchClientSecret = async () => {
    try {
      const response = await axios.post(
        `${BASE_URL}/order/pay`,
        {
          cartItems,
        },
        getConfig(token)
      );
      if (response.data.clientSecret) setClientSecret(response.data.clientSecret);
      else setError(response.data.error || t("checkout.initFailed"));
    } catch (error) {
      // 403 = e-mail not verified, 503 = Stripe key missing, 502 = Stripe error
      const status = error?.response?.status;
      setError(
        status === 403 ? t("checkout.verifyFirst") : error?.response?.data?.error || t("checkout.initFailed")
      );
      console.log(error);
    }
  };

  if (error) {
    return (
      <div className="alert alert-danger d-flex align-items-center gap-2 m-0">
        <i className="bi bi-exclamation-triangle"></i> {error}
      </div>
    );
  }

  if (!clientSecret) return <Spinner />;

  return (
    <Elements
      stripe={stripePromise}
      options={{
        clientSecret,
        locale: t.lang,
        appearance: {
          theme: "stripe",
          variables: {
            colorPrimary: "#6d5dfc",
            borderRadius: "12px",
            fontFamily: "Plus Jakarta Sans, system-ui, sans-serif",
          },
        },
      }}
    >
      <div className="alert alert-info small d-flex gap-2 align-items-start">
        <i className="bi bi-info-circle mt-1"></i> {t("checkout.testCard")}
      </div>
      <CheckoutForm />
    </Elements>
  );
}
