import { describe, expect, it } from "vitest";
import en from "./en";
import tr from "./tr";
import { translate } from "./index";

const keysOf = (node, prefix = "") =>
  Object.entries(node).flatMap(([key, value]) =>
    typeof value === "object" ? keysOf(value, `${prefix}${key}.`) : [`${prefix}${key}`]
  );

describe("i18n", () => {
  it("has the same keys in English and Turkish", () => {
    expect(keysOf(tr).sort()).toEqual(keysOf(en).sort());
  });

  it("translates and interpolates variables", () => {
    expect(translate("en", "home.count", { count: 3 })).toBe("3 pictures");
    expect(translate("tr", "home.count", { count: 3 })).toBe("3 fotoğraf");
    expect(translate("tr", "checkout.pay", { amount: "$20" })).toBe("$20 öde");
  });

  it("translates category names coming from the API", () => {
    expect(translate("tr", "categories.Food & Drink")).toBe("Yiyecek & İçecek");
  });

  it("falls back to the fallback value, then to the key", () => {
    expect(translate("tr", "categories.Unknown", {}, "Unknown")).toBe("Unknown");
    expect(translate("tr", "does.not.exist")).toBe("does.not.exist");
  });

  it("falls back to English for unknown languages", () => {
    expect(translate("de", "nav.explore")).toBe("Explore");
  });
});
