export default function updateValue() {
  document.addEventListener("DOMContentLoaded", function () {
    function setupFormHandler(buttonId, formId) {
      const saveButton = document.getElementById(buttonId);
      const form = document.getElementById(formId);

      if (!saveButton || !form) return;

      saveButton.addEventListener("click", function (e) {
        e.preventDefault();

        const countElement = document.getElementById("count");
        if (!countElement) return;

        const count = parseInt(countElement.value);
        const totalProducts = parseInt(
          form.getAttribute("data-total-products")
        );

        if (isNaN(count) || isNaN(totalProducts)) return;

        const totalPages = Math.ceil(totalProducts / count);

        const pageElement = document.getElementById("page");

        if (!pageElement) return;

        pageElement.value = totalPages;
      });
    }

    setupFormHandler("importer_save_settings", "importProductsForm");

    setupFormHandler("updater_save_settings", "updateProductsForm");
  });
}
