import React, { useEffect, useState } from "react";
import { loadStripe } from "@stripe/stripe-js";
import { Elements } from "@stripe/react-stripe-js";
import axios from "axios";
import { BASE_URL, getConfig } from "../../helpers/config";
import { useSelector } from "react-redux";
import CheckoutForm from "./CheckOutForm";

export default function Stripe() {
  const stripePromise = loadStripe(
    "pk_test_51PtWxg06PAiJgxypTXDSJH8vg4qM38YFyp7hOXObPCb6QdkclUpuPtduDXrlF0qw5vfq2WFZYTeXW475CNPGnXNs00Bng3oyL1"
  );
  const [clientSecret, setClientSecret] = useState("");
  const { cartItems } = useSelector((state) => state.cart);
  const { token } = useSelector((state) => state.user);

  useEffect(() => {
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
      setClientSecret(response.data.clientSecret);
    } catch (error) {
      console.log(error);
    }
  };

  return (
    <>
      {stripePromise && clientSecret && (
        <Elements stripe={stripePromise} options={{ clientSecret }}>
          <CheckoutForm />
        </Elements>
      )}
    </>
  );
}
