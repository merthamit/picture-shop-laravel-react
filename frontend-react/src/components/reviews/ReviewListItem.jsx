import React, { useContext } from "react";
import { useSelector } from "react-redux";
import { ReviewContext } from "./context/reviewContext";
import { Rating } from "react-simple-star-rating";
import Avatar from "../layouts/Avatar";
import { useT } from "../../i18n";

const ReviewListItem = ({ review }) => {
  const { user } = useSelector((state) => state.user);
  const { editReview, deleteReview } = useContext(ReviewContext);
  const t = useT();

  const renderReviewActions = () =>
    review?.user_id === user?.id && (
      <div className="dropdown ms-auto">
        <button
          className="btn btn-sm btn-icon btn-ghost border-0"
          data-bs-toggle="dropdown"
          aria-label={t("reviews.actions")}
        >
          <i className="bi bi-three-dots-vertical"></i>
        </button>
        <ul className="dropdown-menu dropdown-menu-end">
          <li>
            <button className="dropdown-item" onClick={() => editReview(review)}>
              <i className="bi bi-pencil"></i> {t("common.edit")}
            </button>
          </li>
          <li>
            <button className="dropdown-item text-danger" onClick={() => deleteReview(review)}>
              <i className="bi bi-trash"></i> {t("common.delete")}
            </button>
          </li>
        </ul>
      </div>
    );

  return (
    <div className="review-item">
      <Avatar user={review?.user} />
      <div className="flex-grow-1">
        <div className="d-flex align-items-center gap-2 flex-wrap">
          <span className="fw-semibold text-ink">{review?.user?.name}</span>
          <Rating initialValue={review?.rating} readonly size={16} />
          <span className="small text-muted">{review?.created_at}</span>
        </div>
        <p>{review?.comment}</p>
      </div>
      {renderReviewActions()}
    </div>
  );
};

export default ReviewListItem;
