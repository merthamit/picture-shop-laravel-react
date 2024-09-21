import React from "react";

const useValidation = (errors, field) => {
  return errors?.[field]?.map((error, index) => (
    <div key={index} className="text-white my-2 rounded p-2 bg-danger">
      {error}
    </div>
  ));
};

export default useValidation;
