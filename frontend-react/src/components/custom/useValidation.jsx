import React from "react";

const useValidation = (errors, field) => {
  return errors?.[field]?.map((error, index) => (
    <div key={index} className="field-error">
      <i className="bi bi-exclamation-circle"></i> {error}
    </div>
  ));
};

export default useValidation;
