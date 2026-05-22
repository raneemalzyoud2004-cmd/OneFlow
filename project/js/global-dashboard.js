document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.querySelector(".search-box input");

  if (searchInput) {
    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();

        const value = searchInput.value.trim().toLowerCase();

        if (value === "") return;

        const rows = document.querySelectorAll("table tbody tr");
        let found = false;

        rows.forEach(row => {
          const text = row.innerText.toLowerCase();

          if (text.includes(value)) {
            row.style.display = "";
            found = true;
          } else {
            row.style.display = "none";
          }
        });

        if (!found && rows.length > 0) {
          alert("No matching result found.");
        }
      }
    });
  }

  const bell = document.querySelector(".notification-bell");
  const dropdown = document.querySelector(".global-notification-dropdown");

  if (bell && dropdown) {
    bell.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      dropdown.style.display =
        dropdown.style.display === "block" ? "none" : "block";
    });

    dropdown.addEventListener("click", function (e) {
      e.stopPropagation();
    });

    document.addEventListener("click", function () {
      dropdown.style.display = "none";
    });
  }
});