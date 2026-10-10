import React from "react";
import { Link } from "react-router-dom";
import { useT } from "../../i18n";
import LanguageSwitcher from "./LanguageSwitcher";

export default function Footer() {
  const t = useT();

  return (
    <footer className="ps-footer">
      <div className="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <Link to="/" className="brand">
          <span className="brand-logo">
            <i className="bi bi-image"></i>
          </span>
          Picture Shop
        </Link>
        <div className="d-flex gap-4 align-items-center">
          <Link to="/">{t("footer.explore")}</Link>
          <Link to="/upload">{t("footer.sell")}</Link>
          <Link to="/cart">{t("footer.cart")}</Link>
          <LanguageSwitcher dropUp />
        </div>
        <span>
          © {new Date().getFullYear()} Picture Shop. {t("footer.rights")}
        </span>
      </div>
    </footer>
  );
}
