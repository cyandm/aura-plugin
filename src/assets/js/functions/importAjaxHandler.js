import { startLogMonitoring } from "./logReader.js";

export default function importProductsOnFormSubmit() {
  const form = document.querySelector("#importProductsForm");

  if (form) {
    console.log("import products is running");

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      startLogMonitoring(); // Start log monitoring when form is submitted

      const formData = new FormData(form);
      importProductAjax(formData);
    });
  }
}

function importProductAjax(formData) {
  jQuery(document).ready(function ($) {
    $.ajax({
      url: restDetails.url + "product-importer/v1/import",
      type: "POST",
      data: formData,
      cache: false,
      processData: false,
      contentType: false,
      success: async function (response) {
        const { page, totalPages } = response;
        console.log(`page: ${page} imported successfully`);

        if (page > totalPages || page < 1) {
          console.log("all products imported");

          // Create notification element
          const notification = document.createElement("div");
          notification.className = "custom-notification";

          const content = document.createElement("div");
          content.className = "notification-content";
          content.textContent = "✅ ایمپورت محصولات با موفقیت انجام شد";

          const button = document.createElement("button");
          button.className = "notification-button";
          button.textContent = "متوجه شدم";
          button.onclick = () => {
            notification.classList.remove("show");
            setTimeout(() => notification.remove(), 300);
          };

          notification.appendChild(content);
          notification.appendChild(button);
          document.body.appendChild(notification);

          // Show notification
          setTimeout(() => notification.classList.add("show"), 100);

          return;
        }

        formData.set("page", parseInt(page) + 1);

        //sleep for 35 seconds
        await new Promise((resolve) => setTimeout(resolve, 35000));

        importProductAjax(formData);
      },
      error: function (error) {
        console.error(`Error importing page ${formData.get("page")}:`, error);
      },
    });
  });
}
