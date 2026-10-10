import { useEffect } from "react";

export default function useTitle(title) {
  useEffect(() => {
    document.title = title ? `${title} · Picture Shop` : "Picture Shop";
  }, [title]);

  return null;
}
