import React, { useEffect, useMemo, useState } from "react";
import useCategories from "../custom/useCategory";
import { useNavigate } from "react-router-dom";
import { FileUploader } from "react-drag-drop-files";
import axios from "axios";
import { BASE_URL, getConfig } from "../../helpers/config";
import { toast } from "react-toastify";
import useValidation from "../custom/useValidation";
import { useDispatch, useSelector } from "react-redux";
import { setCurrentUser } from "../redux/slices/userSlice";
import useTitle from "../custom/useTitle";
import { useT } from "../../i18n";

const Upload = () => {
  const { token, isLoggedIn, user } = useSelector((state) => state.user);
  const [picture, setPicture] = useState({
    title: "",
    price: "",
    category_id: "",
    file: null,
  });
  const t = useT();
  useTitle(t("upload.title"));

  const [errors, setErrors] = useState([]);
  const [fileSizeError, setFileSizeError] = useState("");
  const [loading, setLoading] = useState(false);
  const dispatch = useDispatch();

  const categories = useCategories(0);
  const navigate = useNavigate();
  const fileTypes = ["JPG", "PNG", "JPEG"];

  const handleChange = (file) => {
    setFileSizeError("");
    setPicture({ ...picture, file: file });
  };

  const handleSizeError = () => {
    setFileSizeError(t("upload.sizeError"));
  };

  const storeImage = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors([]);
    const formData = new FormData();
    formData.append("file", picture.file);
    formData.append("title", picture.title);
    formData.append("price", picture.price);
    formData.append("category_id", picture.category_id);

    try {
      const response = await axios.post(
        `${BASE_URL}/store/picture`,
        formData,
        getConfig(token, "multipart/form-data")
      );
      setLoading(false);
      dispatch(setCurrentUser(response.data.user));
      toast.success(response.data.message);
      navigate("/");
    } catch (error) {
      setLoading(false);
      if (error?.response?.status == 422) {
        setErrors(error.response.data.errors);
      } else if (error?.response?.status == 403) {
        toast.error(t("upload.verifyFirst"));
      } else {
        toast.error(t("common.somethingWrong"));
      }
      console.log(error);
    }
  };

  useEffect(() => {
    if (!isLoggedIn) navigate("/login");
  }, [isLoggedIn, navigate]);

  const previewUrl = useMemo(
    () => (picture.file ? URL.createObjectURL(picture.file) : null),
    [picture.file]
  );

  return (
    <div className="container page">
      <h1 className="page-title">{t("upload.title")}</h1>
      <p className="page-subtitle">{t("upload.subtitle")}</p>
      {user && user.email_verified === false && (
        <div className="alert alert-warning d-flex gap-2 align-items-center">
          <i className="bi bi-envelope-exclamation"></i> {t("upload.verifyFirst")}
        </div>
      )}

      <form onSubmit={(e) => storeImage(e)} noValidate>
        <div className="row g-4">
          <div className="col-lg-7">
            <div className="surface surface-pad">
              <div className="mb-4">
                <label className="form-label">{t("upload.file")}</label>
                <FileUploader
                  handleChange={handleChange}
                  name="file"
                  types={fileTypes}
                  required={!picture.file}
                  maxSize={10}
                  classes="drop_area"
                  onSizeError={handleSizeError}
                  label={t("upload.drop")}
                />
                <div className="small text-muted mt-2">{t("upload.fileHint")}</div>
                {fileSizeError && (
                  <div className="field-error">
                    <i className="bi bi-exclamation-circle"></i> {fileSizeError}
                  </div>
                )}
              </div>
              <div className="mb-3">
                <label htmlFor="title" className="form-label">
                  {t("upload.pictureTitle")}
                </label>
                <input
                  type="text"
                  name="title"
                  id="title"
                  className="form-control"
                  placeholder={t("upload.titlePlaceholder")}
                  onChange={(e) =>
                    setPicture({ ...picture, title: e.target.value })
                  }
                />
                {useValidation(errors, "title")}
              </div>
              <div className="row g-3">
                <div className="col-md-6">
                  <label htmlFor="category" className="form-label">
                    {t("upload.category")}
                  </label>
                  <select
                    id="category"
                    className="form-select"
                    value={picture.category_id}
                    onChange={(e) =>
                      setPicture({ ...picture, category_id: e.target.value })
                    }
                  >
                    <option value="" disabled>
                      {t("upload.chooseCategory")}
                    </option>
                    {categories?.map((category) => (
                      <option key={category.id} value={category.id}>
                        {t.category(category.name)}
                      </option>
                    ))}
                  </select>
                  {useValidation(errors, "category_id")}
                </div>
                <div className="col-md-6">
                  <label htmlFor="price" className="form-label">
                    {t("upload.price")}
                  </label>
                  <div className="input-icon">
                    <i className="bi bi-currency-dollar"></i>
                    <input
                      type="number"
                      min="1"
                      name="price"
                      id="price"
                      className="form-control"
                      placeholder="15"
                      onChange={(e) =>
                        setPicture({ ...picture, price: e.target.value })
                      }
                    />
                  </div>
                  {useValidation(errors, "price")}
                </div>
              </div>
            </div>
          </div>

          <div className="col-lg-5">
            <div className="surface surface-pad">
              <h6 className="fw-bold mb-3">{t("upload.preview")}</h6>
              <div className="upload-preview mb-3 d-grid">
                {previewUrl ? (
                  <img src={previewUrl} alt="Preview" />
                ) : (
                  <div className="d-grid text-muted text-center align-content-center">
                    <i className="bi bi-image fs-1 opacity-50"></i>
                    <span className="small">{t("upload.previewEmpty")}</span>
                  </div>
                )}
              </div>
              <div className="d-flex justify-content-between align-items-center mb-3">
                <span className="fw-semibold text-ink text-truncate">{picture.title || t("upload.untitled")}</span>
                <span className="price-tag">{picture.price ? `$${picture.price}` : "$—"}</span>
              </div>
              <div className="d-grid">
                <button type="submit" className="btn btn-gradient btn-lg" disabled={loading}>
                  {loading ? (
                    <span className="spinner-border spinner-border-sm"></span>
                  ) : (
                    <i className="bi bi-cloud-arrow-up"></i>
                  )}
                  {loading ? t("upload.uploading") : t("upload.publish")}
                </button>
              </div>
              <ul className="perk-list small mt-3">
                <li>
                  <i className="bi bi-check-circle-fill"></i> {t("upload.perkCdn")}
                </li>
                <li>
                  <i className="bi bi-check-circle-fill"></i> {t("upload.perkWatermark")}
                </li>
                <li>
                  <i className="bi bi-check-circle-fill"></i> {t("upload.perkMail")}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
};

export default Upload;
