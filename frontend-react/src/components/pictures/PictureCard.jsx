import React from "react";
import { Link } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { addItemToCart } from "../redux/slices/cartSlice";
import { toggleFavorite } from "../redux/slices/userSlice";
import { useT } from "../../i18n";

export function FavoriteButton({ pictureId, className = "" }) {
  const dispatch = useDispatch();
  const t = useT();
  const { user } = useSelector((state) => state.user);
  const favorited = user?.favorite_ids?.includes(pictureId);

  const toggle = (e) => {
    e.preventDefault();
    e.stopPropagation();
    dispatch(toggleFavorite(pictureId));
  };

  return (
    <button
      className={`favorite-btn ${favorited ? "active" : ""} ${className}`}
      onClick={toggle}
      aria-pressed={!!favorited}
      aria-label={favorited ? t("card.unfavorite") : t("card.favorite")}
      title={favorited ? t("card.unfavorite") : t("card.favorite")}
    >
      <i className={`bi ${favorited ? "bi-heart-fill" : "bi-heart"}`}></i>
    </button>
  );
}

export default function PictureCard({ picture }) {
  const dispatch = useDispatch();
  const t = useT();
  const { user } = useSelector((state) => state.user);
  const { cartItems } = useSelector((state) => state.cart);

  const owned =
    picture.user?.id === user?.id || user?.orders?.some((order) => order.picture?.id === picture.id);
  const inCart = cartItems.some((item) => item.id === picture.id);

  const quickAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    dispatch(
      addItemToCart({
        id: picture.id,
        title: picture.title,
        price: picture.price,
        image: picture.thumbnail_path,
      })
    );
  };

  return (
    <Link to={`/picture/${picture.id}`} className="picture-card">
      <div className="thumb">
        <img src={picture.thumbnail_path || picture.image_path} alt={picture.title} loading="lazy" />
        <div className="thumb-badges">
          {picture.category && <span className="glass-badge">{t.category(picture.category.name)}</span>}
          <FavoriteButton pictureId={picture.id} />
        </div>
        {!owned && (
          <div className="quick-add">
            <button
              className="btn btn-sm"
              onClick={quickAdd}
              disabled={inCart}
              aria-label={inCart ? t("card.alreadyInCart") : t("card.addToCart")}
            >
              <i className={`bi ${inCart ? "bi-bag-check-fill" : "bi-bag-plus"}`}></i>
              {inCart ? t("common.inCart") : t("common.add")}
            </button>
          </div>
        )}
      </div>
      <div className="body">
        <div className="title">{picture.title}</div>
        <div className="meta mb-2">
          <span className="text-truncate">{t("common.by", { name: picture.user?.name })}</span>
          {picture.rating ? (
            <span className="rating-inline flex-shrink-0">
              <i className="bi bi-star-fill"></i> {picture.rating}
              <span className="text-muted fw-normal">({picture.reviews_count})</span>
            </span>
          ) : (
            <span className="text-muted flex-shrink-0">{t("common.noReviews")}</span>
          )}
        </div>
        <div className="meta">
          <span className="price-tag">${picture.price}</span>
          {owned ? (
            <span className="status-dot text-success">{t("common.owned")}</span>
          ) : (
            picture.sales_count > 0 && (
              <span>
                <i className="bi bi-fire text-danger"></i> {t("common.sold", { count: picture.sales_count })}
              </span>
            )
          )}
        </div>
      </div>
    </Link>
  );
}

export function PictureCardSkeleton() {
  return (
    <div className="picture-card">
      <div className="thumb skeleton" style={{ borderRadius: 0 }}></div>
      <div className="body">
        <div className="skeleton mb-2" style={{ height: 16, width: "70%" }}></div>
        <div className="skeleton mb-3" style={{ height: 12, width: "45%" }}></div>
        <div className="skeleton" style={{ height: 18, width: "30%" }}></div>
      </div>
    </div>
  );
}
