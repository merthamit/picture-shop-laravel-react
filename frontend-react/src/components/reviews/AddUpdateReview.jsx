import React, { useContext } from "react";
import { ReviewContext } from "./context/reviewContext";
import { Rating } from "react-simple-star-rating";
import { useT } from "../../i18n";

export default function AddUpdateReview() {
  const {
    addReview,
    review,
    setReview,
    updating,
    handleRating,
    clearReview,
    updateReview,
  } = useContext(ReviewContext);
  const t = useT();

  return (
    <form
      className="bg-light rounded-4 p-3 my-3"
      onSubmit={(e) => (updating ? updateReview(e) : addReview(e))}
    >
      <div className="d-flex justify-content-between align-items-center mb-2">
        <span className="fw-semibold text-ink">{updating ? t("reviews.edit") : t("reviews.write")}</span>
        <Rating initialValue={review.rating} onClick={handleRating} size={24} />
      </div>
      <textarea
        name="comment"
        rows="3"
        onChange={(e) =>
          setReview({
            ...review,
            comment: e.target.value,
          })
        }
        value={review.comment}
        className="form-control mb-2"
        placeholder={t("reviews.placeholder")}
      ></textarea>
      <div className="d-flex justify-content-end gap-2">
        {updating ? (
          <>
            <button type="button" className="btn btn-sm btn-ghost" onClick={() => clearReview()}>
              {t("common.cancel")}
            </button>
            <button className="btn btn-sm btn-primary">{t("reviews.update")}</button>
          </>
        ) : (
          <button
            className="btn btn-sm btn-primary"
            disabled={!review.comment || review.rating === 0}
          >
            <i className="bi bi-send"></i> {t("reviews.submit")}
          </button>
        )}
      </div>
      {!updating && (
        <div className="small text-muted mt-2">
          {t("reviews.note")}
        </div>
      )}
    </form>
  );
}
