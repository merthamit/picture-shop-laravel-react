import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import useValidation from "../custom/useValidation";
import axios from "axios";
import { BASE_URL } from "../../helpers/config";
import { toast } from "react-toastify";
import {
  setCurrentUser,
  setLoggedInOut,
  setToken,
} from "../redux/slices/userSlice";
import useTitle from "../custom/useTitle";
import { syncCart } from "../redux/slices/cartSlice";
import { useT } from "../../i18n";

export default function Login() {
  const [user, setUser] = useState({
    email: "",
    password: "",
  });

  const [errors, setErrors] = useState([]);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { isLoggedIn } = useSelector((state) => state.user);
  const dispatch = useDispatch();
  const t = useT();
  const [searchParams] = useSearchParams();
  // Result of clicking the link in the verification e-mail
  const verified = searchParams.get("verified");
  const verifyNotice = {
    1: ["success", t("auth.verified")],
    expired: ["warning", t("auth.verifyExpired")],
    invalid: ["danger", t("auth.verifyInvalid")],
  }[verified];

  const loginUser = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors([]);

    try {
      const response = await axios.post(`${BASE_URL}/user/login`, user);
      setLoading(false);
      if (!response.data.error) {
        dispatch(setLoggedInOut(true));
        dispatch(setCurrentUser(response.data.user));
        dispatch(setToken(response.data.access_token));
        dispatch(syncCart());
        toast.success(t("auth.welcomeBack", { name: response.data.user.name }));
        return navigate("/");
      }
      toast.error(response.data.error);
    } catch (error) {
      setLoading(false);
      if (error?.response?.status == 422) {
        setErrors(error.response.data.errors);
      }
      console.log(error);
    }
  };
  useTitle(t("auth.login"));

  useEffect(() => {
    if (isLoggedIn) navigate("/");
  }, [isLoggedIn, navigate]);

  return (
    <div className="auth-wrap">
      <div className="surface auth-card">
        <div className="text-center mb-4">
          <span className="brand-logo">
            <i className="bi bi-image"></i>
          </span>
          <h1 className="h4 fw-800 mb-1">{t("auth.welcome")}</h1>
          <p className="text-muted m-0">{t("auth.loginText")}</p>
        </div>
        {verifyNotice && <div className={`alert alert-${verifyNotice[0]} small`}>{verifyNotice[1]}</div>}
        <form onSubmit={(e) => loginUser(e)} noValidate>
          <div className="mb-3">
            <label htmlFor="email" className="form-label">
              {t("auth.email")}
            </label>
            <div className="input-icon">
              <i className="bi bi-envelope"></i>
              <input
                type="email"
                name="email"
                id="email"
                className="form-control"
                placeholder="you@example.com"
                autoComplete="email"
                onChange={(e) =>
                  setUser({
                    ...user,
                    email: e.target.value,
                  })
                }
              />
            </div>
            {useValidation(errors, "email")}
          </div>
          <div className="mb-4">
            <div className="d-flex justify-content-between">
              <label htmlFor="password" className="form-label">
                {t("auth.password")}
              </label>
              <Link to="/forgot-password" className="small fw-semibold">
                {t("auth.forgot")}
              </Link>
            </div>
            <div className="input-icon">
              <i className="bi bi-lock"></i>
              <input
                type="password"
                name="password"
                id="password"
                className="form-control"
                placeholder="••••••••"
                autoComplete="current-password"
                onChange={(e) =>
                  setUser({
                    ...user,
                    password: e.target.value,
                  })
                }
              />
            </div>
            {useValidation(errors, "password")}
          </div>
          <div className="d-grid">
            <button type="submit" className="btn btn-primary btn-lg" disabled={loading}>
              {loading && <span className="spinner-border spinner-border-sm"></span>}
              {t("auth.login")}
            </button>
          </div>
        </form>
        <p className="text-center text-muted small mt-4 mb-0">
          {t("auth.noAccount")} <Link to="/register" className="fw-semibold">{t("auth.signup")}</Link>
        </p>
      </div>
    </div>
  );
}
