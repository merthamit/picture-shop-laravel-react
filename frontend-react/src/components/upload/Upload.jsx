import React, { useEffect, useState } from "react";
import useCategories from "../custom/useCategory";
import { useNavigate } from "react-router-dom";
import { FileUploader } from "react-drag-drop-files";
import Spinner from "../layouts/Spinner";
import axios from "axios";
import { BASE_URL, getConfig } from "../../helpers/config";
import { toast } from "react-toastify";
import useValidation from "../custom/useValidation";
import { useDispatch, useSelector } from "react-redux";
import { setCurrentUser } from "../redux/slices/userSlice";
import useTitle from "../custom/useTitle";

const Upload = () => {
  const { token, isLoggedIn, user } = useSelector((state) => state.user);
  const [picture, setPicture] = useState({
    title: "",
    price: "",
    category_id: "",
    user_id: user?.id,
    file: null,
  });
  useTitle("Upload Pictures");

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
    setFileSizeError("The file size must not be greater than 2mb.");
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
    formData.append("user_id", picture.user_id);

    try {
      const response = await axios.post(
        `${BASE_URL}/store/picture`,
        formData,
        getConfig(token, "multipart/form-data")
      );
      setLoading(false);
      dispatch(setCurrentUser(response.data.user));
      toast.success(response.data.message, {
        position: "top-right",
      });
      navigate("/");
    } catch (error) {
      setLoading(false);
      if (error?.response?.status == 422) {
        setErrors(error.response.data.errors);
      }
      console.log(error);
      console.log(picture);
    }
  };

  useEffect(() => {
    if (!isLoggedIn) navigate("/login");
  }, [isLoggedIn, navigate]);

  return (
    <div className="container">
      <div className="row my-5">
        <div className="col-md-6 mx-auto">
          <div className="card">
            <div className="card-header bg-white">
              <h5 className="text-center mt-3">Upload File</h5>
            </div>
            <div className="card-body">
              <form className="mt-5" onSubmit={(e) => storeImage(e)}>
                <div className="mb-3">
                  <label htmlFor="category" className="form-label">
                    Category*
                  </label>
                  <select
                    id="category"
                    className="form-control"
                    value={picture.category_id}
                    onChange={(e) =>
                      setPicture({ ...picture, category_id: e.target.value })
                    }
                  >
                    <option value="" disabled>
                      Choose a category
                    </option>
                    {categories?.map((category) => (
                      <option key={category.id} value={category.id}>
                        {category.name}
                      </option>
                    ))}
                  </select>
                  {useValidation(errors, "category_id")}
                </div>
                <div className="mb-3">
                  <label htmlFor="title" className="form-label">
                    Title
                  </label>
                  <input
                    type="text"
                    name="title"
                    id="title"
                    className="form-control"
                    onChange={(e) =>
                      setPicture({ ...picture, title: e.target.value })
                    }
                  />
                  {useValidation(errors, "title")}
                </div>
                <div className="mb-3">
                  <label htmlFor="price" className="form-label">
                    Price
                  </label>
                  <input
                    type="number"
                    name="price"
                    id="price"
                    className="form-control"
                    onChange={(e) =>
                      setPicture({ ...picture, price: e.target.value })
                    }
                  />
                  {useValidation(errors, "price")}
                </div>
                <div className="mb-3">
                  <FileUploader
                    handleChange={handleChange}
                    name="file"
                    types={fileTypes}
                    required={!picture.file}
                    maxSize={2}
                    classes="drop_area"
                    onSizeError={handleSizeError}
                  />
                  {fileSizeError && (
                    <div className="text-white my-2 rounded p-2 bg-danger">
                      {fileSizeError}
                    </div>
                  )}
                  {picture?.file && (
                    <img
                      src={URL.createObjectURL(picture.file)}
                      alt="Picture"
                      width={150}
                      height={150}
                      className="rounded my-2"
                    />
                  )}
                </div>
                <div className="mb-3">
                  {loading ? (
                    <Spinner />
                  ) : (
                    <button type="submit" className="btn btn-sm btn-dark">
                      Submit
                    </button>
                  )}
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Upload;
