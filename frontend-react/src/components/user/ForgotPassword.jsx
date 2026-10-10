import React, { useState } from "react";
import { Link } from "react-router-dom";
import axios from "axios";
import { BASE_URL } from "../../helpers/config";
import useValidation from "../custom/useValidation";
import useTitle from "../custom/useTitle";
import { useT } from "../../i18n";

export default function ForgotPassword() {
  const t = useT();
  const [email, setEmail] = useState("");
  const [errors, setErrors] = useState([]);
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  useTitle(t("auth.forgotTitle"));

  const submit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors([]);
    setMessage("");
    try {
      const response = await axios.post(`${BASE_URL}/user/forgot-password`, { email });
      setMessage(response.data.message);
    } catch (error) {
      if (error?.response?.status == 422) setErrors(error.response.data.errors);
      else setErrors({ email: [error?.response?.data?.message || t("common.somethingWrong")] });
    }
    setLoading(false);
  };

  return (
    <div className="auth-wrap">
      <div className="surface auth-card">
        <div className="text-center mb-4">
          <span className="brand-logo">
            <i className="bi bi-key"></i>
          </span>
          <h1 className="h4 fw-800 mb-1">{t("auth.forgotTitle")}</h1>
          <p className="text-muted m-0">{t("auth.forgotText")}</p>
        </div>
        {message ? (
          <div className="alert alert-success d-flex gap-2">
            <i className="bi bi-envelope-check"></i> {message}
          </div>
        ) : (
          <form onSubmit={submit} noValidate>
            <div className="mb-4">
              <label htmlFor="email" className="form-label">
                {t("auth.email")}
              </label>
              <div className="input-icon">
                <i className="bi bi-envelope"></i>
                <input
                  type="email"
                  id="email"
                  className="form-control"
                  placeholder="you@example.com"
                  autoComplete="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                />
              </div>
              {useValidation(errors, "email")}
            </div>
            <div className="d-grid">
              <button className="btn btn-primary btn-lg" disabled={loading || !email}>
                {loading && <span className="spinner-border spinner-border-sm"></span>}
                {t("auth.sendLink")}
              </button>
            </div>
          </form>
        )}
        <p className="text-center small mt-4 mb-0">
          <Link to="/login" className="fw-semibold">
            <i className="bi bi-arrow-left"></i> {t("auth.backToLogin")}
          </Link>
        </p>
      </div>
    </div>
  );
}
