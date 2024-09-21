import React, { useEffect, useState } from "react";
import { BASE_URL } from "../helpers/config";
import axios from "axios";
import Spinner from "./layouts/Spinner";
import useCategory from "../components/custom/useCategory";
import Categories from "./partials/Categories";
import Extensions from "./partials/Extensions";
import { Link } from "react-router-dom";
import useTitle from "./custom/useTitle";

const Home = () => {
  const [pictures, setPictures] = useState([]);
  const [extensions, setExtensions] = useState([]);
  const [loading, setLoading] = useState(false);
  const categories = useCategory(1);
  const [categoryId, setCategoryId] = useState("");
  const [pictureExt, setPictureExt] = useState("");
  const [picturesToShow, setPicturesToShow] = useState(6);

  useTitle("Home");

  const loadMorePictures = () => {
    if (picturesToShow > pictures.length) {
      return;
    }
    setPicturesToShow((prevPicturesToShow) => prevPicturesToShow + 6);
  };

  useEffect(() => {
    setLoading(true);
    const fetchPictures = async () => {
      setPicturesToShow(6);
      try {
        if (categoryId) {
          const response = await axios.get(
            `${BASE_URL}/pictures/category/${categoryId}`
          );
          setPictures(response.data.data);
          setLoading(false);
        } else if (pictureExt) {
          const response = await axios.get(
            `${BASE_URL}/pictures/extensions/${pictureExt}`
          );
          setPictures(response.data.data);
          setLoading(false);
        } else {
          const response = await axios.get(`${BASE_URL}/pictures`);
          setPictures(response.data.data);
          setLoading(false);
        }
      } catch (error) {
        setLoading(false);
      }
    };
    const fetchExtensions = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/extensions`);
        setExtensions(response.data);
      } catch (error) {
        console.log(error);
      }
    };
    fetchPictures();
    fetchExtensions();
  }, [categoryId, pictureExt]);

  return (
    <div className="container">
      {loading ? (
        <Spinner />
      ) : (
        <div className="row my-5">
          <div className="col-md-8">
            <div className="row">
              {pictures?.slice(0, picturesToShow).map((picture) => (
                <div className="col-md-6 mb-2" key={picture.id}>
                  <Link to={`/picture/${picture.id}`}>
                    <div className="card">
                      <img
                        src={picture.image_path}
                        alt={picture.title}
                        className="card-img-top"
                        height={300}
                      />
                    </div>
                  </Link>
                </div>
              ))}
            </div>
            {picturesToShow < pictures.length && (
              <div className="d-flex justify-content-center my-3">
                <button
                  onClick={loadMorePictures}
                  className="btn btn-sm btn-dark"
                >
                  Load More
                </button>
              </div>
            )}
          </div>
          <div className="col-md-4">
            <div className="card">
              <div className="card-header bg-white">
                <h5 className="text-center mt-2">
                  <i className="bi bi-filter-circle"></i> Filters
                </h5>
              </div>
              <div className="card-body">
                <Categories
                  categories={categories}
                  setCategoryId={setCategoryId}
                  categoryId={categoryId}
                  setPictureExt={setPictureExt}
                />
                <hr />
                <Extensions
                  setPictureExt={setPictureExt}
                  setCategoryId={setCategoryId}
                  pictureExt={pictureExt}
                  extensions={extensions}
                />
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Home;
