import React from "react";

// Initials avatar with a gradient background (no external image needed)
export default function Avatar({ user, size = "" }) {
  const initials = (user?.name || "?")
    .split(" ")
    .map((part) => part[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();

  return (
    <span className={`avatar avatar-initials ${size === "lg" ? "avatar-lg" : ""}`}>
      {initials}
    </span>
  );
}
