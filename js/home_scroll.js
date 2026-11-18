
document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll(".arrow[data-target][data-dir]")
    .forEach(button => {
      button.addEventListener("click", function () {
        const row = document.getElementById(button.dataset.target);
        const direction = Number(button.dataset.dir);

        if (row) {
          row.scrollBy({
            left: direction * 220,
            behavior: "smooth"
          });
        }
      });
    });
});
