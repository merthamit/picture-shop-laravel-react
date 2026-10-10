import { createSlice } from "@reduxjs/toolkit";
import { detectLanguage } from "../../../i18n";

const initialState = {
  lang: detectLanguage(),
};

export const uiSlice = createSlice({
  name: "ui",
  initialState,
  reducers: {
    setLanguage(state, action) {
      state.lang = action.payload;
    },
  },
});

export const { setLanguage } = uiSlice.actions;

export default uiSlice.reducer;
