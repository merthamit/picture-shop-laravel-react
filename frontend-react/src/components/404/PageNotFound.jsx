import React from "react";
import { Link } from "react-router-dom";
import useTitle from "../custom/useTitle";
import EmptyState from "../layouts/EmptyState";
import { useT } from "../../i18n";

export default function PageNotFound() {
  const t = useT();
  useTitle(t("notFound.title"));

  return (
    <div className="container page">
      <div className="surface">
        <EmptyState icon="bi-signpost-split" title={t("notFound.title")} text={t("notFound.text")}>
          <Link to="/" className="btn btn-primary">
            <i className="bi bi-house"></i> {t("notFound.back")}
          </Link>
        </EmptyState>
      </div>
    </div>
  );
}
