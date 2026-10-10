import { useSelector } from "react-redux";
import axios from "axios";
import en from "./en";
import tr from "./tr";

export const LANGUAGES = {
  en: { label: "English", short: "EN", flag: "🇬🇧" },
  tr: { label: "Türkçe", short: "TR", flag: "🇹🇷" },
};

const dictionaries = { en, tr };

export const detectLanguage = () =>
  typeof navigator !== "undefined" && navigator.language?.toLowerCase().startsWith("tr") ? "tr" : "en";

const lookup = (dict, key) => key.split(".").reduce((node, part) => node?.[part], dict);

/**
 * translate("tr", "home.count", { count: 3 }) -> "3 fotoğraf"
 * Falls back to English, then to `fallback`, then to the key itself.
 */
export function translate(lang, key, vars = {}, fallback) {
  const value = lookup(dictionaries[lang] || en, key) ?? lookup(en, key) ?? fallback ?? key;
  return typeof value === "string"
    ? value.replace(/\{(\w+)\}/g, (match, name) => (vars[name] ?? match))
    : value;
}

// Every API request carries the chosen language so Laravel answers in it
export function applyLanguage(lang) {
  axios.defaults.headers.common["Accept-Language"] = lang;
  document.documentElement.lang = lang;
}

export function useLang() {
  return useSelector((state) => state.ui?.lang || "en");
}

export function useT() {
  const lang = useLang();
  const t = (key, vars, fallback) => translate(lang, key, vars, fallback);
  // Category names come from the API in English
  t.category = (name) => translate(lang, `categories.${name}`, {}, name);
  t.lang = lang;
  return t;
}
