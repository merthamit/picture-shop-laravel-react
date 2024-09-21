import React, { useState } from "react";
import { useSelector } from "react-redux";
import { ReviewContext } from "./context/reviewContext";
import ReviewList from "./ReviewList";
import { BASE_URL, getConfig } from "../../helpers/config";
import AddUpdateReview from "./AddUpdateReview";
import axios from "axios";
import { toast } from "react-toastify";

const Reviews = ({ picture, setLoading }) => {
  const { user, token, isLoggedIn } = useSelector((state) => state.user);
  const [review, setReview] = useState({
    picture_id: "",
    user_id: "",
    comment: "",
    rating: 0,
  });

  const [updating, setUpdating] = useState(false);

  const handleRating = (rating) => {
    setReview({ ...review, rating: rating });
  };

  const addReview = async (e) => {
    e.preventDefault();
    setLoading(true);

    const data = {
      user_id: user?.id,
      picture_id: picture?.id,
      comment: review.comment,
      rating: review.rating,
    };

    try {
      const response = await axios.post(
        `${BASE_URL}/store/review`,
        data,
        getConfig(token)
      );

      setLoading(false);
      clearReview();

      if (!response.data.error) {
        toast.success(response.data.message, {
          position: "top-right",
        });
      } else {
        toast.error(response.data.error, {
          position: "top-right",
        });
      }
    } catch (error) {
      console.log(error);
    }
  };

  const updateReview = async (e) => {
    e.preventDefault();
    setLoading(true);

    const data = {
      user_id: user?.id,
      picture_id: picture?.id,
      comment: review.comment,
      rating: review.rating,
    };

    try {
      const response = await axios.put(
        `${BASE_URL}/update/review`,
        data,
        getConfig(token)
      );

      setLoading(false);
      clearReview();

      if (!response.data.error) {
        picture.reviews = picture.reviews.filter(
          (item) => item.id !== review.id
        );
        toast.success(response.data.message, {
          position: "top-right",
        });
      } else {
        toast.error(response.data.error, {
          position: "top-right",
        });
      }
    } catch (error) {
      console.log(error);
    }
  };

  const deleteReview = async (data) => {
    setLoading(true);

    try {
      const response = await axios.post(
        `${BASE_URL}/delete/review`,
        data,
        getConfig(token)
      );

      setLoading(false);
      clearReview();

      if (!response.data.error) {
        picture.reviews = picture.reviews.filter((item) => item.id !== data.id);
        toast.success(response.data.message, {
          position: "top-right",
        });
      } else {
        toast.error(response.data.error, {
          position: "top-right",
        });
      }
    } catch (error) {
      console.log(error);
    }
  };

  const editReview = (data) => {
    setReview(data);
    setUpdating(true);
  };

  const clearReview = () => {
    setReview({
      picture_id: "",
      user_id: "",
      comment: "",
      rating: 0,
    });
    if (updating) {
      setUpdating(false);
    }
  };

  return (
    <ReviewContext.Provider
      value={{
        addReview,
        review,
        setReview,
        updating,
        handleRating,
        clearReview,
        editReview,
        updateReview,
        deleteReview,
      }}
    >
      {isLoggedIn && <AddUpdateReview />}
      <ReviewList picture={picture} />
    </ReviewContext.Provider>
  );
};

export default Reviews;
