import { useEffect } from "react";
import { BrowserRouter, Route, Routes, useLocation } from "react-router-dom";
import { useSelector } from "react-redux";
import Home from "./components/Home";
import Register from "./components/user/Register";
import Login from "./components/user/Login";
import ForgotPassword from "./components/user/ForgotPassword";
import ResetPassword from "./components/user/ResetPassword";
import Header from "./components/layouts/Header";
import Footer from "./components/layouts/Footer";
import VerifyEmailBanner from "./components/layouts/VerifyEmailBanner";
import Upload from "./components/upload/Upload";
import Profile from "./components/user/Profile";
import Favorites from "./components/favorites/Favorites";
import Picture from "./components/pictures/Picture";
import Cart from "./components/cart/Cart";
import Checkout from "./components/checkout/Checkout";
import PageNotFound from "./components/404/PageNotFound";
import { applyLanguage, useLang } from "./i18n";

// Scroll to top when navigating to another page (not on filter/query changes)
function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);
  return null;
}

function App() {
  const lang = useLang();
  const { isLoggedIn } = useSelector((state) => state.user);

  // Runs before children effects fetch data, so API calls use the right language
  applyLanguage(lang);

  return (
    <BrowserRouter>
      <ScrollToTop />
      <Header />
      {isLoggedIn && <VerifyEmailBanner />}
      <main key={lang}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/register" element={<Register />} />
          <Route path="/login" element={<Login />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/reset-password" element={<ResetPassword />} />
          <Route path="/upload" element={<Upload />} />
          <Route path="/profile" element={<Profile />} />
          <Route path="/favorites" element={<Favorites />} />
          <Route path="/picture/:id" element={<Picture />} />
          <Route path="/cart" element={<Cart />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="*" element={<PageNotFound />} />
        </Routes>
      </main>
      <Footer />
    </BrowserRouter>
  );
}

export default App;
