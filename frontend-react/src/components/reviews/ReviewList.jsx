import React from "react";
import ReviewListItem from "./ReviewListItem";
import { useT } from "../../i18n";

export default function ReviewList({ picture }) {
  const t = useT();
  if (!picture?.reviews?.length) {
    return <p className="text-muted small m-0 py-2">{t("reviews.empty")}</p>;
  }

  return (
    <div>
      {picture.reviews.map((review) => (
        <ReviewListItem key={review.id} review={review} />
      ))}
    </div>
  );
}
