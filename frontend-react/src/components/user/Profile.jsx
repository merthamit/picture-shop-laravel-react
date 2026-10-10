import React, { useEffect, useState } from "react";
import axios from "axios";
import { useDispatch, useSelector } from "react-redux";
import { Link, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import useTitle from "../custom/useTitle";
import useCategories from "../custom/useCategory";
import useValidation from "../custom/useValidation";
import Avatar from "../layouts/Avatar";
import EmptyState from "../layouts/EmptyState";
import { BASE_URL, getConfig } from "../../helpers/config";
import { setCurrentUser } from "../redux/slices/userSlice";
import { useT } from "../../i18n";

const PAGE_SIZE = 8;

const STATUS = {
  completed: { label: "profile.statusCompleted", className: "text-success" },
  pending: { label: "profile.statusPending", className: "text-warning" },
  failed: { label: "profile.statusFailed", className: "text-danger" },
};

function Modal({ title, onClose, children, footer }) {
  return (
    <>
      <div className="modal d-block" tabIndex="-1" role="dialog" aria-modal="true" onClick={onClose}>
        <div className="modal-dialog modal-dialog-centered" onClick={(e) => e.stopPropagation()}>
          <div className="modal-content">
            <div className="modal-header border-0 pb-0">
              <h5 className="modal-title">{title}</h5>
              <button type="button" className="btn-close" onClick={onClose} aria-label="Close"></button>
            </div>
            <div className="modal-body">{children}</div>
            <div className="modal-footer border-0 pt-0">{footer}</div>
          </div>
        </div>
      </div>
      <div className="modal-backdrop show"></div>
    </>
  );
}

function EditPictureModal({ picture, onClose }) {
  const t = useT();
  const dispatch = useDispatch();
  const { token } = useSelector((state) => state.user);
  const categories = useCategories(0);
  const [form, setForm] = useState({
    title: picture.title,
    price: picture.price,
    category_id: picture.category_id,
  });
  const [errors, setErrors] = useState([]);
  const [saving, setSaving] = useState(false);

  const save = async () => {
    setSaving(true);
    setErrors([]);
    try {
      const response = await axios.put(`${BASE_URL}/pictures/${picture.id}`, form, getConfig(token));
      dispatch(setCurrentUser(response.data.user));
      toast.success(response.data.message);
      onClose();
    } catch (error) {
      if (error?.response?.status == 422) setErrors(error.response.data.errors);
      else toast.error(t("common.somethingWrong"));
    }
    setSaving(false);
  };

  return (
    <Modal
      title={t("profile.editTitle")}
      onClose={onClose}
      footer={
        <>
          <button className="btn btn-ghost" onClick={onClose}>
            {t("common.cancel")}
          </button>
          <button className="btn btn-primary" onClick={save} disabled={saving}>
            {saving && <span className="spinner-border spinner-border-sm"></span>}
            {t("common.save")}
          </button>
        </>
      }
    >
      <div className="d-flex gap-3 align-items-center mb-3">
        <img src={picture.thumbnail_path} alt="" className="rounded-3" width={96} height={72} style={{ objectFit: "cover" }} />
        <div className="small text-muted">#{picture.id}</div>
      </div>
      <div className="mb-3">
        <label className="form-label">{t("upload.pictureTitle")}</label>
        <input className="form-control" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
        {useValidation(errors, "title")}
      </div>
      <div className="row g-3">
        <div className="col-7">
          <label className="form-label">{t("upload.category")}</label>
          <select
            className="form-select"
            value={form.category_id}
            onChange={(e) => setForm({ ...form, category_id: e.target.value })}
          >
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {t.category(category.name)}
              </option>
            ))}
          </select>
          {useValidation(errors, "category_id")}
        </div>
        <div className="col-5">
          <label className="form-label">{t("upload.price")}</label>
          <div className="input-icon">
            <i className="bi bi-currency-dollar"></i>
            <input
              type="number"
              min="1"
              className="form-control"
              value={form.price}
              onChange={(e) => setForm({ ...form, price: e.target.value })}
            />
          </div>
          {useValidation(errors, "price")}
        </div>
      </div>
    </Modal>
  );
}

function DeletePictureModal({ picture, onClose }) {
  const t = useT();
  const dispatch = useDispatch();
  const { token } = useSelector((state) => state.user);
  const [deleting, setDeleting] = useState(false);

  const remove = async () => {
    setDeleting(true);
    try {
      const response = await axios.delete(`${BASE_URL}/pictures/${picture.id}`, getConfig(token));
      dispatch(setCurrentUser(response.data.user));
      toast.success(response.data.message);
      onClose();
    } catch (error) {
      toast.error(t("common.somethingWrong"));
      setDeleting(false);
    }
  };

  return (
    <Modal
      title={t("profile.deleteTitle")}
      onClose={onClose}
      footer={
        <>
          <button className="btn btn-ghost" onClick={onClose}>
            {t("common.cancel")}
          </button>
          <button className="btn btn-danger" onClick={remove} disabled={deleting}>
            {deleting ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-trash"></i>}
            {t("profile.deleteConfirm")}
          </button>
        </>
      }
    >
      <p className="mb-2">{t("profile.deleteText", { title: picture.title })}</p>
      <p className="small text-muted mb-0">
        {picture.sales_count > 0 ? t("profile.deleteSoldText") : t("profile.deleteUnsoldText")}
      </p>
    </Modal>
  );
}

const Profile = () => {
  const { isLoggedIn, user } = useSelector((state) => state.user);
  const [tab, setTab] = useState("orders");
  const [ordersToShow, setOrdersToShow] = useState(PAGE_SIZE);
  const [picturesToShow, setPicturesToShow] = useState(PAGE_SIZE);
  const [editing, setEditing] = useState(null);
  const [deleting, setDeleting] = useState(null);
  const navigate = useNavigate();
  const t = useT();
  useTitle(t("profile.title"));

  useEffect(() => {
    if (!isLoggedIn) navigate("/login");
  }, [isLoggedIn, navigate]);

  const orders = user?.orders || [];
  const pictures = user?.pictures || [];
  const sales = pictures.reduce((acc, picture) => acc + (picture.sales_count || 0), 0);
  const revenue = pictures.reduce((acc, picture) => acc + (picture.sales_count || 0) * Number(picture.price), 0);

  const stats = [
    { label: t("profile.purchases"), value: orders.length, icon: "bi-bag-check" },
    { label: t("profile.myPictures"), value: pictures.length, icon: "bi-images" },
    { label: t("profile.sales"), value: sales, icon: "bi-graph-up-arrow" },
    { label: t("profile.earnings"), value: `$${revenue}`, icon: "bi-wallet2" },
  ];

  return (
    <div className="container page">
      <div className="surface surface-pad mb-4">
        <div className="profile-hero">
          <Avatar user={user} size="lg" />
          <div className="flex-grow-1">
            <h1 className="h4 fw-800 mb-0">
              {user?.name}{" "}
              {user?.email_verified && <i className="bi bi-patch-check-fill text-primary fs-6"></i>}
            </h1>
            <span className="text-muted">{user?.email}</span>
          </div>
          <Link to="/favorites" className="btn btn-ghost">
            <i className="bi bi-heart"></i> {t("profile.favorites")} ({user?.favorite_ids?.length || 0})
          </Link>
          <Link to="/upload" className="btn btn-primary">
            <i className="bi bi-cloud-arrow-up"></i> {t("nav.sellPicture")}
          </Link>
        </div>
      </div>

      <div className="row g-3 mb-4">
        {stats.map((stat) => (
          <div className="col-6 col-md-3" key={stat.label}>
            <div className="surface stat-card d-flex justify-content-between align-items-start">
              <div>
                <div className="value">{stat.value}</div>
                <div className="label">{stat.label}</div>
              </div>
              <i className={`bi ${stat.icon} fs-4 text-primary opacity-75`}></i>
            </div>
          </div>
        ))}
      </div>

      <div className="nav nav-tabs-modern mb-3" role="tablist">
        <button className={`nav-link ${tab === "orders" ? "active" : ""}`} onClick={() => setTab("orders")}>
          <i className="bi bi-bag me-1"></i> {t("profile.tabPurchases")}
        </button>
        <button className={`nav-link ${tab === "pictures" ? "active" : ""}`} onClick={() => setTab("pictures")}>
          <i className="bi bi-images me-1"></i> {t("profile.tabPictures")}
        </button>
      </div>

      <div className="surface">
        {tab === "orders" ? (
          !orders.length ? (
            <EmptyState icon="bi-bag" title={t("profile.noPurchases")} text={t("profile.noPurchasesText")}>
              <Link to="/" className="btn btn-primary">
                {t("cart.explore")}
              </Link>
            </EmptyState>
          ) : (
            <div className="table-responsive">
              <table className="table table-modern">
                <thead>
                  <tr>
                    <th className="ps-4">{t("profile.colPicture")}</th>
                    <th>{t("profile.colTotal")}</th>
                    <th>{t("profile.colStatus")}</th>
                    <th>{t("profile.colDate")}</th>
                    <th className="pe-4"></th>
                  </tr>
                </thead>
                <tbody>
                  {orders.slice(0, ordersToShow).map((order) => {
                    const status = STATUS[order.status] || STATUS.completed;
                    return (
                      <tr key={order.id}>
                        <td className="ps-4">
                          <div className="d-flex align-items-center gap-3">
                            <img src={order.picture.thumbnail_path} alt="" />
                            <span className="fw-semibold text-ink">{order.picture.title}</span>
                          </div>
                        </td>
                        <td className="fw-semibold">${order.picture.price}</td>
                        <td>
                          <span className={`status-dot ${status.className}`}>{t(status.label)}</span>
                        </td>
                        <td className="text-muted small">{order.created_at}</td>
                        <td className="pe-4 text-end">
                          <Link to={`/picture/${order.picture.id}`} className="btn btn-sm btn-soft">
                            <i className="bi bi-download"></i> {t("profile.download")}
                          </Link>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
              {ordersToShow < orders.length && (
                <div className="text-center pb-3">
                  <button className="btn btn-sm btn-ghost" onClick={() => setOrdersToShow((n) => n + PAGE_SIZE)}>
                    {t("common.loadMore")}
                  </button>
                </div>
              )}
            </div>
          )
        ) : !pictures.length ? (
          <EmptyState icon="bi-cloud-arrow-up" title={t("profile.noPictures")} text={t("profile.noPicturesText")}>
            <Link to="/upload" className="btn btn-primary">
              {t("profile.upload")}
            </Link>
          </EmptyState>
        ) : (
          <div className="table-responsive">
            <table className="table table-modern">
              <thead>
                <tr>
                  <th className="ps-4">{t("profile.colPicture")}</th>
                  <th>{t("profile.colPrice")}</th>
                  <th>{t("profile.colSales")}</th>
                  <th>{t("profile.colStatus")}</th>
                  <th className="pe-4"></th>
                </tr>
              </thead>
              <tbody>
                {pictures.slice(0, picturesToShow).map((picture) => (
                  <tr key={picture.id}>
                    <td className="ps-4">
                      <Link to={`/picture/${picture.id}`} className="d-flex align-items-center gap-3">
                        <img src={picture.thumbnail_path} alt="" />
                        <span className="fw-semibold text-ink">{picture.title}</span>
                      </Link>
                    </td>
                    <td className="fw-semibold">${picture.price}</td>
                    <td>{picture.sales_count}</td>
                    <td>
                      {picture.status ? (
                        <span className="status-dot text-success">{t("profile.live")}</span>
                      ) : (
                        <span className="status-dot text-danger">{t("profile.hiddenStatus")}</span>
                      )}
                    </td>
                    <td className="pe-4 text-end text-nowrap">
                      <button
                        className="btn btn-sm btn-icon btn-ghost"
                        onClick={() => setEditing(picture)}
                        title={t("common.edit")}
                        aria-label={t("common.edit")}
                      >
                        <i className="bi bi-pencil"></i>
                      </button>{" "}
                      <button
                        className="btn btn-sm btn-icon btn-ghost text-danger"
                        onClick={() => setDeleting(picture)}
                        title={t("common.delete")}
                        aria-label={t("common.delete")}
                      >
                        <i className="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            {picturesToShow < pictures.length && (
              <div className="text-center pb-3">
                <button className="btn btn-sm btn-ghost" onClick={() => setPicturesToShow((n) => n + PAGE_SIZE)}>
                  {t("common.loadMore")}
                </button>
              </div>
            )}
          </div>
        )}
      </div>

      {editing && <EditPictureModal picture={editing} onClose={() => setEditing(null)} />}
      {deleting && <DeletePictureModal picture={deleting} onClose={() => setDeleting(null)} />}
    </div>
  );
};

export default Profile;
