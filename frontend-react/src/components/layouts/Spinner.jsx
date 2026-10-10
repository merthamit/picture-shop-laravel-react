import React from "react";

export default function Spinner({ small = false }) {
  return (
    <div className={`d-flex justify-content-center ${small ? "" : "py-5"}`}>
      <div
        className={`spinner-border text-primary ${small ? "spinner-border-sm" : ""}`}
        role="status"
      >
        <span className="visually-hidden">Loading...</span>
      </div>
    </div>
  );
}
