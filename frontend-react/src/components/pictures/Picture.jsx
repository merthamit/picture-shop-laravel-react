import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { Link, useNavigate, useParams } from "react-router-dom";
import axios from "axios";
import { toast } from "react-toastify";
import { Rating } from "react-simple-star-rating";
import Spinner from "../layouts/Spinner";
import EmptyState from "../layouts/EmptyState";
import Avatar from "../layouts/Avatar";
import { BASE_URL, getConfig } from "../../helpers/config";
import { addItemToCart } from "../redux/slices/cartSlice";
import Reviews from "../reviews/Reviews";
import useTitle from "../custom/useTitle";
import PictureCard, { FavoriteButton } from "./PictureCard";
import { useT } from "../../i18n";

const Picture = () => {
  const [picture, setPicture] = useState(null);
  const [related, setRelated] = useState([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [downloading, setDownloading] = useState(false);
  const { user, token } = useSelector((state) => state.user);
  const { cartItems } = useSelector((state) => state.cart);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { id } = useParams();
  const t = useT();

  useTitle(picture?.title);

  const reviews = picture?.reviews || [];
  const average = reviews.length
    ? (reviews.reduce((acc, review) => acc + review.rating, 0) / reviews.length).toFixed(1)
    : 0;
  const purchased =
    picture?.is_purchased || user?.orders?.some((order) => order.picture?.id === picture?.id);
  const isOwner = picture?.is_owner || (user && picture?.user?.id === user.id);
  const canDownload = purchased || isOwner;
  const inCart = cartItems.some((item) => item.id === picture?.id);

  const addToCart = () =>
    dispatch(
      addItemToCart({
        id: picture?.id,
        title: picture?.title,
        price: picture?.price,
        image: picture?.thumbnail_path,
      })
    );

  const buyNow = async () => {
    if (!inCart) await addToCart();
    navigate("/cart");
  };

  // The API returns a short-lived signed link to the original on Cloudinary
  const downloadPicture = async () => {
    setDownloading(true);
    try {
      const response = await axios.get(`${BASE_URL}/download/picture/${picture?.id}`, getConfig(token));
      const link = document.createElement("a");
      link.href = response.data.url;
      link.setAttribute("download", response.data.filename);
      link.rel = "noopener";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      toast.info(t("picture.downloadStarted"), { autoClose: 2000 });
    } catch (error) {
      toast.error(error?.response?.data?.message || t("common.somethingWrong"));
    }
    setDownloading(false);
  };

  useEffect(() => {
    const fetchPictureById = async () => {
      setLoading(true);
      setError("");
      try {
        const response = await axios.get(`${BASE_URL}/pictures/${id}`, getConfig(token));
        const data = response.data.data;
        setPicture(data);
        setLoading(false);

        // Related pictures from the same category
        const relatedResponse = await axios.get(`${BASE_URL}/pictures`, {
          params: { categories: data.category?.id, per_page: 5, sort: "popular" },
        });
        setRelated(relatedResponse.data.data.filter((item) => item.id !== data.id).slice(0, 4));
      } catch (error) {
        if (error?.response?.status == 404) {
          setError(t("picture.notFoundText"));
        }
        console.log(error);
        setLoading(false);
      }
    };
    fetchPictureById();
  }, [id]);

  if (loading && !picture) return <Spinner />;

  if (error) {
    return (
      <div className="container page">
        <div className="surface">
          <EmptyState icon="bi-image" title={t("picture.notFound")} text={error}>
            <Link to="/" className="btn btn-primary">
              {t("picture.backToExplore")}
            </Link>
          </EmptyState>
        </div>
      </div>
    );
  }

  return (
    <div className="container page">
      <nav aria-label="breadcrumb">
        <ol className="breadcrumb">
          <li className="breadcrumb-item">
            <Link to="/">{t("nav.explore")}</Link>
          </li>
          {picture?.category && (
            <li className="breadcrumb-item">
              <Link to={`/?categories=${picture.category.id}`}>{t.category(picture.category.name)}</Link>
            </li>
          )}
          <li className="breadcrumb-item active" aria-current="page">
            {picture?.title}
          </li>
        </ol>
      </nav>

      <div className="row g-4 g-xl-5">
        <div className="col-lg-7">
          <div className="detail-media">
            <img src={picture?.image_path} alt={picture?.title} />
          </div>
          {!canDownload && (
            <div className="small text-muted mt-2">
              <i className="bi bi-shield-lock me-1"></i>
              {t("picture.previewNote")}
            </div>
          )}

          <div className="surface surface-pad mt-4">
            <div className="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
              <h5 className="m-0">{t("picture.reviews")}</h5>
              {reviews.length > 0 && (
                <div className="rating-summary">
                  <span className="big">{average}</span>
                  <div>
                    <Rating initialValue={Number(average)} readonly size={20} allowFraction />
                    <div className="small text-muted">
                      {reviews.length === 1
                        ? t("picture.reviewCountOne")
                        : t("picture.reviewCount", { count: reviews.length })}
                    </div>
                  </div>
                </div>
              )}
            </div>
            <Reviews picture={picture} setLoading={setLoading} />
          </div>
        </div>

        <div className="col-lg-5">
          <div className="detail-panel">
            <div className="surface surface-pad">
              <div className="d-flex justify-content-between align-items-start gap-2">
                <div>
                  {picture?.category && (
                    <span className="badge rounded-pill text-bg-light border mb-2">
                      {t.category(picture.category.name)}
                    </span>
                  )}
                  <h1 className="h3 fw-800 mb-3">{picture?.title}</h1>
                </div>
                {picture && <FavoriteButton pictureId={picture.id} className="favorite-btn-lg" />}
              </div>

              <div className="d-flex align-items-center gap-2 mb-3">
                <Avatar user={picture?.user} />
                <div className="small">
                  <div className="fw-semibold text-ink">{picture?.user?.name}</div>
                  <div className="text-muted">
                    {t("picture.picturesCount", { count: picture?.user?.pictures_count ?? 0 })}
                  </div>
                </div>
                {reviews.length > 0 && (
                  <span className="rating-inline ms-auto">
                    <i className="bi bi-star-fill"></i> {average}
                  </span>
                )}
              </div>

              {picture?.is_deleted && <div className="alert alert-warning small">{t("picture.removed")}</div>}
              {!picture?.status && isOwner && <div className="alert alert-warning small">{t("picture.hidden")}</div>}

              <hr />

              <div className="d-flex align-items-end justify-content-between mb-3">
                <div>
                  <div className="small text-muted">{t("picture.license")}</div>
                  <div className="detail-price">${picture?.price}</div>
                </div>
                {purchased && (
                  <span className="badge rounded-pill text-bg-success px-3 py-2">
                    <i className="bi bi-check2-circle me-1"></i> {t("picture.purchased")}
                  </span>
                )}
                {isOwner && !purchased && (
                  <span className="badge rounded-pill text-bg-primary px-3 py-2">
                    <i className="bi bi-person-check me-1"></i> {t("picture.yourPicture")}
                  </span>
                )}
              </div>

              <div className="d-grid gap-2 mb-4">
                {canDownload ? (
                  <button className="btn btn-gradient btn-lg" onClick={downloadPicture} disabled={downloading}>
                    {downloading ? (
                      <span className="spinner-border spinner-border-sm"></span>
                    ) : (
                      <i className="bi bi-download"></i>
                    )}
                    {t("picture.download")}
                  </button>
                ) : (
                  <>
                    <button className="btn btn-gradient btn-lg" onClick={buyNow}>
                      <i className="bi bi-lightning-charge"></i> {t("picture.buyNow")}
                    </button>
                    <button className="btn btn-ghost btn-lg" onClick={addToCart} disabled={inCart}>
                      <i className={`bi ${inCart ? "bi-bag-check" : "bi-bag-plus"}`}></i>
                      {inCart ? t("picture.addedToCart") : t("picture.addToCart")}
                    </button>
                  </>
                )}
              </div>

              <div className="spec-list mb-4">
                <div className="spec">
                  <small>{t("picture.format")}</small>
                  <strong className="text-uppercase">{picture?.ext}</strong>
                </div>
                <div className="spec">
                  <small>{t("picture.resolution")}</small>
                  <strong>{picture?.width ? `${picture.width} × ${picture.height}` : "—"}</strong>
                </div>
                <div className="spec">
                  <small>{t("picture.sales")}</small>
                  <strong>{picture?.sales_count || 0}</strong>
                </div>
                <div className="spec">
                  <small>
                    <i className="bi bi-heart-fill text-danger"></i>
                  </small>
                  <strong>{t("picture.favorites", { count: picture?.favorites_count || 0 })}</strong>
                </div>
              </div>

              <ul className="perk-list">
                <li>
                  <i className="bi bi-check-circle-fill"></i> {t("picture.perkLicense")}
                </li>
                <li>
                  <i className="bi bi-check-circle-fill"></i> {t("picture.perkDownload")}
                </li>
                <li>
                  <i className="bi bi-check-circle-fill"></i> {t("picture.perkPayment")}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {related.length > 0 && (
        <section className="mt-5">
          <div className="d-flex justify-content-between align-items-center mb-3">
            <h4 className="m-0">{t("picture.moreIn", { category: t.category(picture?.category?.name) })}</h4>
            <Link to={`/?categories=${picture?.category?.id}`} className="fw-semibold">
              {t("picture.viewAll")} <i className="bi bi-arrow-right"></i>
            </Link>
          </div>
          <div className="picture-grid">
            {related.map((item) => (
              <PictureCard key={item.id} picture={item} />
            ))}
          </div>
        </section>
      )}
    </div>
  );
};

export default Picture;
