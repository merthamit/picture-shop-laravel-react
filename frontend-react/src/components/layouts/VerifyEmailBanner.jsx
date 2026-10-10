import React, { useState } from "react";
import axios from "axios";
import { useSelector } from "react-redux";
import { toast } from "react-toastify";
import { BASE_URL, getConfig } from "../../helpers/config";
import { useT } from "../../i18n";

// Shown while the logged-in user's e-mail address is not verified
export default function VerifyEmailBanner() {
  const { user, token } = useSelector((state) => state.user);
  const [sending, setSending] = useState(false);
  const t = useT();

  if (!user || user.email_verified !== false) return null;

  const resend = async () => {
    setSending(true);
    try {
      const response = await axios.post(`${BASE_URL}/email/verification-notification`, null, getConfig(token));
      toast.success(response.data.message);
    } catch (error) {
      toast.error(error?.response?.data?.message || t("common.somethingWrong"));
    }
    setSending(false);
  };

  return (
    <div className="verify-banner">
      <div className="container d-flex align-items-center gap-3 flex-wrap">
        <i className="bi bi-envelope-exclamation fs-5"></i>
        <span className="flex-grow-1">{t("verify.banner")}</span>
        <button className="btn btn-sm btn-dark" onClick={resend} disabled={sending}>
          {sending ? t("verify.sending") : t("verify.resend")}
        </button>
      </div>
    </div>
  );
}
