document.querySelectorAll(".custom-file").forEach((container) => {
  let input = container.querySelector("input");
  let label = container.querySelector("label");

  const placeholder = label.textContent;

  $(input).on("change", (event) => {
    console.log('CHANGED');
    let basename = input.value.split(/[\/\\]/).pop();
    label.textContent = basename || placeholder;
  });
});
