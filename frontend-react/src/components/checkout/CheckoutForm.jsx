import { PaymentElement } from "@stripe/react-stripe-js";
import { useState } from "react";
import { useStripe, useElements } from "@stripe/react-stripe-js";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import { BASE_URL, getConfig } from "../../helpers/config";
import axios from "axios";
import { setCurrentUser } from "../redux/slices/userSlice";
import { useT } from "../../i18n";

export default function CheckoutForm() {
  const stripe = useStripe();
  const elements = useElements();

  const [message, setMessage] = useState(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const t = useT();
  const { cartItems } = useSelector((state) => state.cart);
  const { token } = useSelector((state) => state.user);
  const total = cartItems.reduce((acc, item) => acc + Number(item.price), 0);
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const placeOrder = async (paymentIntent) => {
    try {
      const response = await axios.post(`${BASE_URL}/store/order`, { pictures: cartItems }, getConfig(token));
      dispatch(setCurrentUser(response.data.user));
      // Hand the receipt to the success page (it empties the cart once it is shown,
      // otherwise the checkout page would see an empty cart and bounce to /cart)
      navigate("/checkout/success", {
        replace: true,
        state: {
          items: cartItems,
          total,
          paymentId: paymentIntent.id,
          paidAt: new Date().toISOString(),
        },
      });
    } catch (error) {
      const errors = error?.response?.data?.errors;
      setMessage(errors ? Object.values(errors)[0][0] : t("common.somethingWrong"));
      setIsProcessing(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!stripe || !elements) {
      // Stripe.js has not yet loaded.
      return;
    }

    setMessage(null);
    setIsProcessing(true);

    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      redirect: "if_required",
    });

    if (error) {
      setMessage(error.message);
      setIsProcessing(false);
    } else if (paymentIntent?.status === "succeeded") {
      await placeOrder(paymentIntent);
    } else {
      setMessage(t("checkout.initFailed"));
      setIsProcessing(false);
    }
  };

  return (
    <form id="payment-form" onSubmit={handleSubmit}>
      <PaymentElement id="payment-element" />
      <button disabled={isProcessing || !stripe || !elements} id="submit">
        <span id="button-text">
          {isProcessing ? (
            <>
              <span className="spinner-border spinner-border-sm me-2"></span>
              {t("checkout.processing")}
            </>
          ) : (
            <>
              <i className="bi bi-lock-fill me-2"></i>
              {t("checkout.pay", { amount: `$${total}` })}
            </>
          )}
        </span>
      </button>
      {/* Show any error messages */}
      {message && <div id="payment-message">{message}</div>}
    </form>
  );
}
