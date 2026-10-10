import React from "react";
import axios from "axios";
import { useDispatch, useSelector } from "react-redux";
import { LANGUAGES, applyLanguage, useLang, useT } from "../../i18n";
import { setLanguage } from "../redux/slices/uiSlice";
import { BASE_URL, getConfig } from "../../helpers/config";

export default function LanguageSwitcher({ dropUp = false }) {
  const lang = useLang();
  const t = useT();
  const dispatch = useDispatch();
  const { token } = useSelector((state) => state.user);

  const change = (code) => {
    if (code === lang) return;
    applyLanguage(code);
    dispatch(setLanguage(code));
    // Remember it on the account so e-mails are sent in this language too
    if (token) {
      axios.put(`${BASE_URL}/user/locale`, { locale: code }, getConfig(token)).catch(() => {});
    }
  };

  return (
    <div className={`dropdown ${dropUp ? "dropup" : ""}`}>
      <button
        className="btn btn-ghost btn-sm border-0 lang-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label={t("common.language")}
      >
        <i className="bi bi-globe2"></i>
        <span className="fw-bold">{LANGUAGES[lang].short}</span>
      </button>
      <ul className="dropdown-menu dropdown-menu-end">
        {Object.entries(LANGUAGES).map(([code, info]) => (
          <li key={code}>
            <button className={`dropdown-item ${code === lang ? "active-lang" : ""}`} onClick={() => change(code)}>
              <span>{info.flag}</span> {info.label}
              {code === lang && <i className="bi bi-check2 ms-auto"></i>}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}
