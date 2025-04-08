import "./functions/htmx.js";
import importProductsOnFormSubmit from "./functions/importAjaxHandler.js";
import updateProductsOnFormSubmit from "./functions/updateAjaxHandler.js";
//import updateValue from "./functions/updateValue.js";
import logReader from "./functions/logReader.js";

console.log("java script is working");

importProductsOnFormSubmit();
updateProductsOnFormSubmit();
//updateValue();
logReader();
