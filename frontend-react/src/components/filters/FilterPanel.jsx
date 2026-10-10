import React, { useEffect, useState } from "react";
import { useT } from "../../i18n";

const PRICE_PRESETS = [
  { key: "under10", label: "filters.under10", min: "", max: 10 },
  { key: "10-25", label: "$10 – $25", min: 10, max: 25 },
  { key: "25-50", label: "$25 – $50", min: 25, max: 50 },
  { key: "over50", label: "filters.over50", min: 50, max: "" },
];

export default function FilterPanel({ filters, categories, extensions, priceRange, onChange, idPrefix = "f" }) {
  const t = useT();
  const [minPrice, setMinPrice] = useState(filters.min_price);
  const [maxPrice, setMaxPrice] = useState(filters.max_price);

  useEffect(() => {
    setMinPrice(filters.min_price);
    setMaxPrice(filters.max_price);
  }, [filters.min_price, filters.max_price]);

  // Apply typed prices after the user stops typing
  useEffect(() => {
    if (String(minPrice) === String(filters.min_price) && String(maxPrice) === String(filters.max_price)) return;
    const timer = setTimeout(() => onChange({ min_price: minPrice, max_price: maxPrice }), 600);
    return () => clearTimeout(timer);
  }, [minPrice, maxPrice]);

  const toggle = (key, value) => {
    const current = filters[key];
    const next = current.includes(value)
      ? current.filter((item) => item !== value)
      : [...current, value];
    onChange({ [key]: next });
  };

  const activePreset = (preset) =>
    String(preset.min) === String(filters.min_price) && String(preset.max) === String(filters.max_price);

  return (
    <div>
      <div className="filter-section">
        <div className="filter-title">
          {t("filters.categories")}
          {filters.categories.length > 0 && (
            <button onClick={() => onChange({ categories: [] })}>{t("filters.clear")}</button>
          )}
        </div>
        {categories.length === 0 && <div className="small text-muted">{t("filters.noCategories")}</div>}
        {categories.map((category) => {
          const id = `${idPrefix}-cat-${category.id}`;
          return (
            <label key={category.id} className="filter-option" htmlFor={id}>
              <span className="label">
                <input
                  type="checkbox"
                  className="form-check-input"
                  id={id}
                  checked={filters.categories.includes(String(category.id))}
                  onChange={() => toggle("categories", String(category.id))}
                />
                {t.category(category.name)}
              </span>
              <span className="badge-count">{category.pictures_count}</span>
            </label>
          );
        })}
      </div>

      <div className="filter-section">
        <div className="filter-title">
          {t("filters.price")}
          {(filters.min_price || filters.max_price) && (
            <button onClick={() => onChange({ min_price: "", max_price: "" })}>{t("filters.clear")}</button>
          )}
        </div>
        <div className="price-inputs">
          <div className="input-icon">
            <i className="bi bi-currency-dollar"></i>
            <input
              type="number"
              min="0"
              className="form-control"
              placeholder={priceRange?.min ?? t("filters.min")}
              value={minPrice}
              onChange={(e) => setMinPrice(e.target.value)}
              aria-label={t("filters.min")}
            />
          </div>
          <span className="text-muted">–</span>
          <div className="input-icon">
            <i className="bi bi-currency-dollar"></i>
            <input
              type="number"
              min="0"
              className="form-control"
              placeholder={priceRange?.max ?? t("filters.max")}
              value={maxPrice}
              onChange={(e) => setMaxPrice(e.target.value)}
              aria-label={t("filters.max")}
            />
          </div>
        </div>
        <div className="price-presets">
          {PRICE_PRESETS.map((preset) => (
            <button
              key={preset.key}
              className={activePreset(preset) ? "active" : ""}
              onClick={() =>
                activePreset(preset)
                  ? onChange({ min_price: "", max_price: "" })
                  : onChange({ min_price: preset.min, max_price: preset.max })
              }
            >
              {t(preset.label)}
            </button>
          ))}
        </div>
      </div>

      <div className="filter-section">
        <div className="filter-title">
          {t("filters.format")}
          {filters.ext.length > 0 && <button onClick={() => onChange({ ext: [] })}>{t("filters.clear")}</button>}
        </div>
        <div className="pill-group">
          {extensions.map((item) => (
            <button
              key={item.ext}
              className={`pill ${filters.ext.includes(item.ext) ? "active" : ""}`}
              onClick={() => toggle("ext", item.ext)}
              title={t("filters.picturesCount", { count: item.pictures_count })}
            >
              {item.ext} <span className="opacity-75 fw-normal">· {item.pictures_count}</span>
            </button>
          ))}
          {extensions.length === 0 && <div className="small text-muted">{t("filters.noFormats")}</div>}
        </div>
      </div>
    </div>
  );
}
