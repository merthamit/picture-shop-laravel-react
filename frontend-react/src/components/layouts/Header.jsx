import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { Link, NavLink, useLocation, useNavigate, useSearchParams } from "react-router-dom";
import {
  setCurrentUser,
  setLoggedInOut,
  setToken,
} from "../redux/slices/userSlice";
import { BASE_URL, getConfig } from "../../helpers/config";
import axios from "axios";
import { toast } from "react-toastify";
import { clearCartItems, pruneOwnedItems } from "../redux/slices/cartSlice";
import Avatar from "./Avatar";
import LanguageSwitcher from "./LanguageSwitcher";
import { useT } from "../../i18n";

export default function Header() {
  const { isLoggedIn, user, token } = useSelector((state) => state.user);
  const { cartItems } = useSelector((state) => state.cart);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const [searchParams] = useSearchParams();
  const [term, setTerm] = useState(searchParams.get("q") || "");
  const t = useT();
  const favoriteCount = user?.favorite_ids?.length || 0;

  // Keep the search box in sync with the URL (e.g. when filters are cleared)
  useEffect(() => {
    setTerm(location.pathname === "/" ? searchParams.get("q") || "" : "");
  }, [location.pathname, searchParams]);

  useEffect(() => {
    const getLoggedInUser = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/user`, getConfig(token));
        dispatch(setCurrentUser(response.data.user));
        dispatch(pruneOwnedItems(response.data.user));
      } catch (error) {
        if (error?.response?.status == 401) {
          dispatch(setLoggedInOut(false));
          dispatch(setCurrentUser(null));
          dispatch(setToken(""));
          dispatch(clearCartItems());
        }
        console.log(error);
      }
    };
    if (token) getLoggedInUser();
  }, [token]);

  const search = (e) => {
    e.preventDefault();
    const params = location.pathname === "/" ? new URLSearchParams(searchParams) : new URLSearchParams();
    if (term.trim()) params.set("q", term.trim());
    else params.delete("q");
    navigate({ pathname: "/", search: params.toString() });
  };

  const logOutUser = async () => {
    try {
      const response = await axios.post(
        `${BASE_URL}/user/logout`,
        null,
        getConfig(token)
      );
      dispatch(setLoggedInOut(false));
      dispatch(setCurrentUser(null));
      dispatch(setToken(""));
      dispatch(clearCartItems());
      toast.success(response.data.message);
      navigate("/login");
    } catch (error) {
      console.log(error);
    }
  };

  const navClass = ({ isActive }) => `nav-link ${isActive ? "active" : ""}`;

  return (
    <header className="ps-navbar">
      <nav className="navbar navbar-expand-lg">
        <div className="container">
          <Link className="brand me-4" to="/">
            <span className="brand-logo">
              <i className="bi bi-image"></i>
            </span>
            Picture Shop
          </Link>

          <div className="d-flex align-items-center gap-1 order-lg-last ms-auto ms-lg-3">
            <LanguageSwitcher />
            {isLoggedIn && (
              <NavLink to="/favorites" className={(s) => `${navClass(s)} cart-link`} aria-label={t("nav.favorites")}>
                <i className="bi bi-heart fs-5"></i>
                {favoriteCount > 0 && <span className="cart-badge">{favoriteCount}</span>}
              </NavLink>
            )}
            <NavLink to="/cart" className={(s) => `${navClass(s)} cart-link`} aria-label={t("nav.cart")}>
              <i className="bi bi-bag fs-5"></i>
              {cartItems?.length > 0 && (
                <span className="cart-badge">{cartItems.length}</span>
              )}
            </NavLink>

            {isLoggedIn ? (
              <div className="dropdown">
                <button
                  className="btn btn-ghost border-0 px-2 d-flex align-items-center gap-2"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  <Avatar user={user} />
                  <span className="d-none d-md-inline fw-semibold text-ink">
                    {user?.name?.split(" ")[0]}
                  </span>
                  <i className="bi bi-chevron-down small text-muted"></i>
                </button>
                <ul className="dropdown-menu dropdown-menu-end">
                  <li className="px-3 py-2">
                    <div className="fw-bold text-ink">{user?.name}</div>
                    <div className="small text-muted">{user?.email}</div>
                  </li>
                  <li><hr className="dropdown-divider" /></li>
                  <li>
                    <Link className="dropdown-item" to="/profile">
                      <i className="bi bi-person"></i> {t("nav.profile")}
                    </Link>
                  </li>
                  <li>
                    <Link className="dropdown-item" to="/upload">
                      <i className="bi bi-cloud-arrow-up"></i> {t("nav.sellPicture")}
                    </Link>
                  </li>
                  <li><hr className="dropdown-divider" /></li>
                  <li>
                    <button className="dropdown-item text-danger" onClick={logOutUser}>
                      <i className="bi bi-box-arrow-right"></i> {t("nav.logout")}
                    </button>
                  </li>
                </ul>
              </div>
            ) : (
              <div className="d-none d-sm-flex gap-2">
                <Link to="/login" className="btn btn-ghost btn-sm">
                  {t("nav.login")}
                </Link>
                <Link to="/register" className="btn btn-primary btn-sm">
                  {t("nav.signup")}
                </Link>
              </div>
            )}

            <button
              className="navbar-toggler border-0 shadow-none ms-1"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#mainNav"
              aria-controls="mainNav"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <i className="bi bi-list fs-3"></i>
            </button>
          </div>

          <div className="collapse navbar-collapse" id="mainNav">
            <form className="nav-search input-icon my-3 my-lg-0" onSubmit={search} role="search">
              <i className="bi bi-search"></i>
              <input
                type="search"
                className="form-control"
                placeholder={t("nav.search")}
                value={term}
                onChange={(e) => setTerm(e.target.value)}
                aria-label={t("nav.search")}
              />
            </form>
            <ul className="navbar-nav ms-lg-3 me-auto gap-1">
              <li className="nav-item">
                <NavLink className={navClass} to="/" end>
                  <i className="bi bi-compass"></i> {t("nav.explore")}
                </NavLink>
              </li>
              <li className="nav-item">
                <NavLink className={navClass} to="/upload">
                  <i className="bi bi-cloud-arrow-up"></i> {t("nav.sell")}
                </NavLink>
              </li>
              {!isLoggedIn && (
                <li className="nav-item d-sm-none">
                  <NavLink className={navClass} to="/login">
                    <i className="bi bi-box-arrow-in-right"></i> {t("nav.login")}
                  </NavLink>
                </li>
              )}
            </ul>
          </div>
        </div>
      </nav>
    </header>
  );
}
