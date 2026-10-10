import { combineReducers, configureStore } from "@reduxjs/toolkit";
import {
  persistStore,
  persistReducer,
  FLUSH,
  REHYDRATE,
  PAUSE,
  PERSIST,
  PURGE,
  REGISTER,
} from "redux-persist";
import storage from "redux-persist/lib/storage";
import userReducer from "../slices/userSlice";
import cartReducer from "../slices/cartSlice";
import uiReducer from "../slices/uiSlice";

const rootReducer = combineReducers({
  user: userReducer,
  cart: cartReducer,
  ui: uiReducer,
});

const persistConfig = {
  key: "root",
  storage,
};

const persisedReducer = persistReducer(persistConfig, rootReducer);

const store = configureStore({
  reducer: persisedReducer,
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
      },
    }),
});

const persistor = persistStore(store);

export { store, persistor };
