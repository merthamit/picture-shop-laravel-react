import React, { useEffect, useMemo, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import axios from "axios";
import { BASE_URL } from "../helpers/config";
import useCategory from "./custom/useCategory";
import useTitle from "./custom/useTitle";
import FilterPanel from "./filters/FilterPanel";
import PictureCard, { PictureCardSkeleton } from "./pictures/PictureCard";
import EmptyState from "./layouts/EmptyState";
import { useT } from "../i18n";

const SORT_OPTIONS = ["newest", "popular", "rating", "price_asc", "price_desc", "oldest"];

const PER_PAGE = 12;
const csv = (value) => (value ? value.split(",").filter(Boolean) : []);

const Home = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const rawCategories = useCategory(1);
  const [extensions, setExtensions] = useState([]);
  const [pictures, setPictures] = useState([]);
  const [meta, setMeta] = useState(null);
  const [priceRange, setPriceRange] = useState(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [compact, setCompact] = useState(false);
  const [heroTerm, setHeroTerm] = useState("");
  const [featured, setFeatured] = useState([]);

  const t = useT();
  useTitle(t("home.title"));
  // Alphabetical in the selected language
  const categories = useMemo(
    () => [...rawCategories].sort((x, y) => t.category(x.name).localeCompare(t.category(y.name), t.lang)),
    [rawCategories, t.lang]
  );

  // Filters live in the URL so results are shareable and survive refresh
  const filters = useMemo(
    () => ({
      q: searchParams.get("q") || "",
      categories: csv(searchParams.get("categories")),
      ext: csv(searchParams.get("ext")),
      min_price: searchParams.get("min_price") || "",
      max_price: searchParams.get("max_price") || "",
      sort: searchParams.get("sort") || "newest",
    }),
    [searchParams]
  );

  const updateFilters = (patch) => {
    const next = { ...filters, ...patch };
    const params = new URLSearchParams();
    if (next.q) params.set("q", next.q);
    if (next.categories.length) params.set("categories", next.categories.join(","));
    if (next.ext.length) params.set("ext", next.ext.join(","));
    if (next.min_price !== "" && next.min_price !== null) params.set("min_price", next.min_price);
    if (next.max_price !== "" && next.max_price !== null) params.set("max_price", next.max_price);
    if (next.sort !== "newest") params.set("sort", next.sort);
    setSearchParams(params, { replace: true });
  };

  const clearAll = () => setSearchParams({}, { replace: true });

  const fetchPictures = async (pageToLoad, signal) => {
    const params = { page: pageToLoad, per_page: PER_PAGE, sort: filters.sort };
    if (filters.q) params.q = filters.q;
    if (filters.categories.length) params.categories = filters.categories.join(",");
    if (filters.ext.length) params.ext = filters.ext.join(",");
    if (filters.min_price !== "") params.min_price = filters.min_price;
    if (filters.max_price !== "") params.max_price = filters.max_price;

    const response = await axios.get(`${BASE_URL}/pictures`, { params, signal });
    return response.data;
  };

  // First page whenever the filters change
  useEffect(() => {
    const controller = new AbortController();
    setLoading(true);
    setPage(1);
    fetchPictures(1, controller.signal)
      .then((data) => {
        setPictures(data.data);
        setMeta(data.meta);
        setPriceRange(data.price_range);
        setLoading(false);
      })
      .catch((error) => {
        if (!axios.isCancel(error)) {
          console.log(error);
          setLoading(false);
        }
      });
    return () => controller.abort();
  }, [searchParams.toString()]);

  useEffect(() => {
    axios
      .get(`${BASE_URL}/extensions`)
      .then((response) => setExtensions(response.data))
      .catch((error) => console.log(error));
    axios
      .get(`${BASE_URL}/pictures`, { params: { sort: "popular", per_page: 3 } })
      .then((response) => setFeatured(response.data.data))
      .catch((error) => console.log(error));
  }, []);

  const loadMore = async () => {
    setLoadingMore(true);
    try {
      const data = await fetchPictures(page + 1);
      setPictures((prev) => [...prev, ...data.data]);
      setMeta(data.meta);
      setPage(page + 1);
    } catch (error) {
      console.log(error);
    }
    setLoadingMore(false);
  };

  const heroSearch = (e) => {
    e.preventDefault();
    updateFilters({ q: heroTerm.trim() });
    document.getElementById("catalog")?.scrollIntoView({ behavior: "smooth" });
  };

  const categoryName = (id) => t.category(categories.find((c) => String(c.id) === id)?.name || id);

  const activeChips = [
    ...(filters.q ? [{ key: "q", label: `“${filters.q}”`, remove: () => updateFilters({ q: "" }) }] : []),
    ...filters.categories.map((id) => ({
      key: `c${id}`,
      label: categoryName(id),
      remove: () => updateFilters({ categories: filters.categories.filter((c) => c !== id) }),
    })),
    ...filters.ext.map((ext) => ({
      key: `e${ext}`,
      label: ext.toUpperCase(),
      remove: () => updateFilters({ ext: filters.ext.filter((e) => e !== ext) }),
    })),
    ...(filters.min_price || filters.max_price
      ? [
          {
            key: "price",
            label: `$${filters.min_price || 0} – ${filters.max_price ? `$${filters.max_price}` : "∞"}`,
            remove: () => updateFilters({ min_price: "", max_price: "" }),
          },
        ]
      : []),
  ];

  const filterCount = activeChips.length;
  const total = meta?.total ?? 0;
  const totalPictures = categories.reduce((sum, c) => sum + (c.pictures_count || 0), 0);

  const panelProps = {
    filters,
    categories,
    extensions,
    priceRange,
    onChange: updateFilters,
  };

  return (
    <div className="container pb-5">
      {/* Hero */}
      <section className="hero">
        <span className="badge rounded-pill bg-white bg-opacity-25 mb-3 px-3 py-2 fw-semibold position-relative">
          <i className="bi bi-stars me-1"></i> {t("home.badge")}
        </span>
        <h1 className="mb-3 position-relative">{t("home.heroTitle")}</h1>
        <p className="mb-4 position-relative">
          {t("home.heroText")}
        </p>
        <form className="hero-search" onSubmit={heroSearch}>
          <div className="input-icon flex-grow-1">
            <i className="bi bi-search"></i>
            <input
              type="search"
              className="form-control"
              placeholder={t("home.heroPlaceholder")}
              value={heroTerm}
              onChange={(e) => setHeroTerm(e.target.value)}
            />
          </div>
          <button className="btn btn-dark px-4">{t("home.search")}</button>
        </form>
        <div className="hero-stats">
          <div>
            <strong>{totalPictures}</strong>
            <span>{t("home.statPictures")}</span>
          </div>
          <div>
            <strong>{categories.length}</strong>
            <span>{t("home.statCategories")}</span>
          </div>
          <div>
            <strong>
              <i className="bi bi-shield-check"></i>
            </strong>
            <span>{t("home.statSecure")}</span>
          </div>
        </div>
        {featured.length === 3 && (
          <div className="hero-collage d-none d-xl-block" aria-hidden="true">
            {featured.map((picture, index) => (
              <Link key={picture.id} to={`/picture/${picture.id}`} className={`collage-item item-${index}`} tabIndex={-1}>
                <img src={picture.thumbnail_path} alt="" />
                <span>
                  {picture.title} · ${picture.price}
                </span>
              </Link>
            ))}
          </div>
        )}
      </section>

      {/* Quick category chips */}
      <div className="chip-row my-4" id="catalog">
        <button
          className={`chip ${filters.categories.length === 0 ? "active" : ""}`}
          onClick={() => updateFilters({ categories: [] })}
        >
          <i className="bi bi-grid"></i> {t("home.all")}
        </button>
        {categories.map((category) => {
          const active = filters.categories.length === 1 && filters.categories[0] === String(category.id);
          return (
            <button
              key={category.id}
              className={`chip ${active ? "active" : ""}`}
              onClick={() => updateFilters({ categories: active ? [] : [String(category.id)] })}
            >
              {t.category(category.name)} <span className="count">{category.pictures_count}</span>
            </button>
          );
        })}
      </div>

      <div className="row g-4">
        {/* Desktop filters */}
        <aside className="col-lg-3 d-none d-lg-block">
          <div className="surface surface-pad filter-panel">
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h6 className="m-0 fw-bold">
                <i className="bi bi-sliders me-2"></i>{t("home.filters")}
              </h6>
              {filterCount > 0 && (
                <button className="btn btn-link btn-sm p-0 text-decoration-none" onClick={clearAll}>
                  {t("home.resetAll")}
                </button>
              )}
            </div>
            <FilterPanel {...panelProps} idPrefix="desk" />
          </div>
        </aside>

        {/* Results */}
        <section className="col-lg-9">
          <div className="toolbar">
            <div className="d-flex align-items-center gap-2">
              <button
                className="btn btn-ghost btn-sm d-lg-none"
                data-bs-toggle="offcanvas"
                data-bs-target="#filtersCanvas"
              >
                <i className="bi bi-sliders"></i> {t("home.filters")}
                {filterCount > 0 && <span className="badge rounded-pill text-bg-primary">{filterCount}</span>}
              </button>
              <span className="result-count">
                {loading ? t("common.loading") : total === 1 ? t("home.countOne") : t("home.count", { count: total })}
                {filters.q && !loading && (
                  <span className="text-muted fw-normal"> {t("home.for", { q: filters.q })}</span>
                )}
              </span>
            </div>
            <div className="d-flex align-items-center gap-2">
              <select
                className="form-select form-select-sm sort-select"
                value={filters.sort}
                onChange={(e) => updateFilters({ sort: e.target.value })}
                aria-label={t("home.sortLabel")}
              >
                {SORT_OPTIONS.map((option) => (
                  <option key={option} value={option}>
                    {t(`sort.${option}`)}
                  </option>
                ))}
              </select>
              <div className="btn-group view-toggle d-none d-sm-flex">
                <button
                  className={`btn btn-sm btn-icon ${!compact ? "btn-dark" : "btn-ghost"}`}
                  onClick={() => setCompact(false)}
                  aria-label={t("home.largeGrid")}
                >
                  <i className="bi bi-grid"></i>
                </button>
                <button
                  className={`btn btn-sm btn-icon ${compact ? "btn-dark" : "btn-ghost"}`}
                  onClick={() => setCompact(true)}
                  aria-label={t("home.compactGrid")}
                >
                  <i className="bi bi-grid-3x3-gap"></i>
                </button>
              </div>
            </div>
          </div>

          {activeChips.length > 0 && (
            <div className="d-flex flex-wrap align-items-center gap-2 mb-3">
              {activeChips.map((chip) => (
                <button key={chip.key} className="chip chip-removable" onClick={chip.remove}>
                  {chip.label} <i className="bi bi-x-lg small"></i>
                </button>
              ))}
              <button className="btn btn-link btn-sm text-decoration-none" onClick={clearAll}>
                {t("home.clearAll")}
              </button>
            </div>
          )}

          {loading ? (
            <div className={`picture-grid ${compact ? "compact" : ""}`}>
              {Array.from({ length: 6 }).map((_, index) => (
                <PictureCardSkeleton key={index} />
              ))}
            </div>
          ) : pictures.length === 0 ? (
            <div className="surface">
              <EmptyState
                icon="bi-search"
                title={t("home.emptyTitle")}
                text={t("home.emptyText")}
              >
                {filterCount > 0 && (
                  <button className="btn btn-primary" onClick={clearAll}>
                    {t("home.clearFilters")}
                  </button>
                )}
              </EmptyState>
            </div>
          ) : (
            <>
              <div className={`picture-grid ${compact ? "compact" : ""}`}>
                {pictures.map((picture) => (
                  <PictureCard key={picture.id} picture={picture} />
                ))}
              </div>
              {meta && meta.current_page < meta.last_page && (
                <div className="text-center mt-4">
                  <button className="btn btn-ghost px-4" onClick={loadMore} disabled={loadingMore}>
                    {loadingMore ? (
                      <span className="spinner-border spinner-border-sm"></span>
                    ) : (
                      <i className="bi bi-arrow-down-circle"></i>
                    )}
                    {t("common.loadMore")}
                  </button>
                  <div className="small text-muted mt-2">
                    {t("common.showing", { count: pictures.length, total })}
                  </div>
                </div>
              )}
            </>
          )}
        </section>
      </div>

      {/* Mobile filters */}
      <div className="offcanvas offcanvas-start" tabIndex="-1" id="filtersCanvas" aria-labelledby="filtersCanvasLabel">
        <div className="offcanvas-header border-bottom">
          <h5 className="offcanvas-title" id="filtersCanvasLabel">
            <i className="bi bi-sliders me-2"></i>{t("home.filters")}
          </h5>
          <button type="button" className="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div className="offcanvas-body">
          <FilterPanel {...panelProps} idPrefix="mob" />
        </div>
        <div className="p-3 border-top d-flex gap-2">
          <button className="btn btn-ghost flex-fill" onClick={clearAll}>
            {t("home.reset")}
          </button>
          <button className="btn btn-primary flex-fill" data-bs-dismiss="offcanvas">
            {t("home.showResults", { count: total })}
          </button>
        </div>
      </div>
    </div>
  );
};

export default Home;
