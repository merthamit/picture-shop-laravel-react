import React from "react";

export default function EmptyState({ icon = "bi-images", title, text, children }) {
  return (
    <div className="empty-state">
      <div className="icon">
        <i className={`bi ${icon}`}></i>
      </div>
      <h5>{title}</h5>
      {text && <p>{text}</p>}
      {children}
    </div>
  );
}
