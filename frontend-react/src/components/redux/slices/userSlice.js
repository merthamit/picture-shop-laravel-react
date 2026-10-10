import { createSlice } from "@reduxjs/toolkit";
import axios from "axios";
import { toast } from "react-toastify";
import { BASE_URL, getConfig } from "../../../helpers/config";
import { translate } from "../../../i18n";

const initialState = {
  isLoggedIn: false,
  token: "",
  user: null,
};

export const userSlice = createSlice({
  name: "user",
  initialState,
  reducers: {
    setCurrentUser(state, action) {
      state.user = action.payload;
    },
    setLoggedInOut(state, action) {
      state.isLoggedIn = action.payload;
    },
    setToken(state, action) {
      state.token = action.payload;
    },
    setFavoriteIds(state, action) {
      if (state.user) state.user.favorite_ids = action.payload;
    },
  },
});

const userReducer = userSlice.reducer;

export const { setCurrentUser, setLoggedInOut, setToken, setFavoriteIds } = userSlice.actions;

// Heart button: guests are sent to the login page by the caller
export const toggleFavorite = (pictureId) => async (dispatch, getState) => {
  const { token } = getState().user;
  const lang = getState().ui.lang;
  if (!token) {
    toast.info(translate(lang, "favorites.loginFirst"));
    return false;
  }
  try {
    const response = await axios.post(`${BASE_URL}/favorites/${pictureId}`, null, getConfig(token));
    dispatch(setFavoriteIds(response.data.favorite_ids));
    toast.success(response.data.message, { autoClose: 1500 });
    return true;
  } catch (error) {
    console.log(error);
    toast.error(translate(lang, "common.somethingWrong"));
    return false;
  }
};

export default userReducer;
