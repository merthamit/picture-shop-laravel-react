import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import Stripe from "./Stripe";
import useTitle from "../custom/useTitle";

const Checkout = () => {
  const { isLoggedIn } = useSelector((state) => state.user);
  const navigate = useNavigate();
  useTitle("CheckOut");

  useEffect(() => {
    if (!isLoggedIn) navigate("/login");
  }, [isLoggedIn, navigate]);

  return (
    <div className="container">
      <div className="row my-5">
        <div className="col-md-6 mx-auto">
          <Stripe />
        </div>
      </div>
    </div>
  );
};

export default Checkout;
