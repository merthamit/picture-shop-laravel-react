import React, { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import { Link, useNavigate } from "react-router-dom";
import useValidation from "../custom/useValidation";
import axios from "axios";
import { BASE_URL } from "../../helpers/config";
import { toast } from "react-toastify";
import useTitle from "../custom/useTitle";
import { useT } from "../../i18n";

export default function Register() {
  const [user, setUser] = useState({
    name: "",
    email: "",
    password: "",
  });

  const [errors, setErrors] = useState([]);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { isLoggedIn } = useSelector((state) => state.user);
  const t = useT();

  const registerUser = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors([]);

    try {
      const response = await axios.post(`${BASE_URL}/user/register`, user);
      setLoading(false);
      toast.success(response.data.message);
      navigate("/login");
    } catch (error) {
      setLoading(false);
      if (error?.response?.status == 422) {
        setErrors(error.response.data.errors);
      }
      console.log(error);
    }
  };
  useTitle(t("auth.signup"));

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
          <h1 className="h4 fw-800 mb-1">{t("auth.createTitle")}</h1>
          <p className="text-muted m-0">{t("auth.createText")}</p>
        </div>
        <form onSubmit={(e) => registerUser(e)} noValidate>
          <div className="mb-3">
            <label htmlFor="name" className="form-label">
              {t("auth.name")}
            </label>
            <div className="input-icon">
              <i className="bi bi-person"></i>
              <input
                type="text"
                name="name"
                id="name"
                className="form-control"
                placeholder={t("auth.namePlaceholder")}
                autoComplete="name"
                onChange={(e) =>
                  setUser({
                    ...user,
                    name: e.target.value,
                  })
                }
              />
            </div>
            {useValidation(errors, "name")}
          </div>
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
          <div className="mb-3">
            <label htmlFor="password" className="form-label">
              {t("auth.password")}
            </label>
            <div className="input-icon">
              <i className="bi bi-lock"></i>
              <input
                type="password"
                name="password"
                id="password"
                className="form-control"
                placeholder={t("auth.passwordHint")}
                autoComplete="new-password"
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
          <div className="d-grid mt-4">
            <button type="submit" className="btn btn-primary btn-lg" disabled={loading}>
              {loading && <span className="spinner-border spinner-border-sm"></span>}
              {t("auth.create")}
            </button>
          </div>
        </form>
        <p className="text-center text-muted small mt-4 mb-0">
          {t("auth.haveAccount")} <Link to="/login" className="fw-semibold">{t("auth.login")}</Link>
        </p>
      </div>
    </div>
  );
}
