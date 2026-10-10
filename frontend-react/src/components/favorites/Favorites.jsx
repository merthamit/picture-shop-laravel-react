import React, { useEffect, useState } from "react";
import axios from "axios";
import { useSelector } from "react-redux";
import { Link, useNavigate } from "react-router-dom";
import { BASE_URL, getConfig } from "../../helpers/config";
import useTitle from "../custom/useTitle";
import EmptyState from "../layouts/EmptyState";
import PictureCard, { PictureCardSkeleton } from "../pictures/PictureCard";
import { useT } from "../../i18n";

export default function Favorites() {
  const t = useT();
  const navigate = useNavigate();
  const { isLoggedIn, token, user } = useSelector((state) => state.user);
  const [pictures, setPictures] = useState([]);
  const [loading, setLoading] = useState(true);
  useTitle(t("favorites.title"));

  useEffect(() => {
    if (!isLoggedIn) {
      navigate("/login");
      return;
    }
    axios
      .get(`${BASE_URL}/favorites`, getConfig(token))
      .then((response) => setPictures(response.data.data))
      .catch((error) => console.log(error))
      .finally(() => setLoading(false));
  }, [isLoggedIn]);

  // Hide cards right away when they are un-hearted on this page
  const visible = pictures.filter((picture) => user?.favorite_ids?.includes(picture.id));

  return (
    <div className="container page">
      <h1 className="page-title">
        <i className="bi bi-heart-fill text-danger me-2"></i>
        {t("favorites.title")}
      </h1>
      <p className="page-subtitle">{t("favorites.subtitle")}</p>

      {loading ? (
        <div className="picture-grid">
          {Array.from({ length: 4 }).map((_, index) => (
            <PictureCardSkeleton key={index} />
          ))}
        </div>
      ) : visible.length === 0 ? (
        <div className="surface">
          <EmptyState icon="bi-heart" title={t("favorites.emptyTitle")} text={t("favorites.emptyText")}>
            <Link to="/" className="btn btn-primary">
              <i className="bi bi-compass"></i> {t("cart.explore")}
            </Link>
          </EmptyState>
        </div>
      ) : (
        <div className="picture-grid">
          {visible.map((picture) => (
            <PictureCard key={picture.id} picture={picture} />
          ))}
        </div>
      )}
    </div>
  );
}
