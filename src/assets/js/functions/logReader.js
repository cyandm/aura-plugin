export default function logReader() {
  const logType = document.querySelector("#importProductsForm")
    ? "import"
    : "update";

  jQuery.ajax({
    url: restDetails.url + `product-importer/v1/log/${logType}`,
    method: "GET",
    success: function (response) {
      const textarea = document.querySelector("#log-content");
      if (textarea) {
        textarea.value = response;
        textarea.scrollTop = textarea.scrollHeight;
      }
    },
  });
}

let logInterval;

export function startLogMonitoring() {
  logReader(); // Read immediately
  logInterval = setInterval(logReader, 20000);
}

export function stopLogMonitoring() {
  clearInterval(logInterval);
}

// Remove the automatic interval and DOM content loaded event
