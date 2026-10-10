import React, { useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import axios from "axios";
import { toast } from "react-toastify";
import { BASE_URL } from "../../helpers/config";
import useValidation from "../custom/useValidation";
import useTitle from "../custom/useTitle";
import { useT } from "../../i18n";

// Target of the link in the password reset e-mail: /reset-password?token=...&email=...
export default function ResetPassword() {
  const t = useT();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const token = searchParams.get("token");
  const email = searchParams.get("email");
  const [password, setPassword] = useState("");
  const [confirmation, setConfirmation] = useState("");
  const [errors, setErrors] = useState([]);
  const [loading, setLoading] = useState(false);
  useTitle(t("auth.resetTitle"));

  const submit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors([]);
    try {
      const response = await axios.post(`${BASE_URL}/user/reset-password`, {
        token,
        email,
        password,
        password_confirmation: confirmation,
      });
      toast.success(response.data.message);
      navigate("/login");
    } catch (error) {
      if (error?.response?.status == 422) setErrors(error.response.data.errors);
      else toast.error(t("common.somethingWrong"));
    }
    setLoading(false);
  };

  return (
    <div className="auth-wrap">
      <div className="surface auth-card">
        <div className="text-center mb-4">
          <span className="brand-logo">
            <i className="bi bi-shield-lock"></i>
          </span>
          <h1 className="h4 fw-800 mb-1">{t("auth.resetTitle")}</h1>
          <p className="text-muted m-0">{t("auth.resetText")}</p>
        </div>
        {!token || !email ? (
          <div className="alert alert-danger">{t("auth.invalidLink")}</div>
        ) : (
          <form onSubmit={submit} noValidate>
            <div className="mb-3">
              <label className="form-label">{t("auth.email")}</label>
              <input type="email" className="form-control" value={email} disabled />
              {useValidation(errors, "email")}
            </div>
            <div className="mb-3">
              <label htmlFor="password" className="form-label">
                {t("auth.newPassword")}
              </label>
              <div className="input-icon">
                <i className="bi bi-lock"></i>
                <input
                  type="password"
                  id="password"
                  className="form-control"
                  autoComplete="new-password"
                  placeholder={t("auth.passwordHint")}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </div>
              {useValidation(errors, "password")}
            </div>
            <div className="mb-4">
              <label htmlFor="password_confirmation" className="form-label">
                {t("auth.confirmPassword")}
              </label>
              <div className="input-icon">
                <i className="bi bi-lock-fill"></i>
                <input
                  type="password"
                  id="password_confirmation"
                  className="form-control"
                  autoComplete="new-password"
                  value={confirmation}
                  onChange={(e) => setConfirmation(e.target.value)}
                />
              </div>
            </div>
            <div className="d-grid">
              <button className="btn btn-primary btn-lg" disabled={loading || !password}>
                {loading && <span className="spinner-border spinner-border-sm"></span>}
                {t("auth.resetButton")}
              </button>
            </div>
          </form>
        )}
        <p className="text-center small mt-4 mb-0">
          <Link to="/forgot-password" className="fw-semibold">
            {t("auth.forgot")}
          </Link>
        </p>
      </div>
    </div>
  );
}
