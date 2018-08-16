$.fn.radioCollapse = function() {
  return this.each((i, input) => {
    $(`input[name="${input.name}"]`).on("change", (event) => {
      $(input.dataset.target).collapse(input.checked ? "show" : "hide");
    });

    // $(input).on("change", () => {
    //   console.log("STATE", input.checked);
    // });
  });
}

$('[data-app="radio-collapse"]').radioCollapse();
