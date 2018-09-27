$(() => {
  const items = $('form.service_merge_form .list-group-item');
  const class_name = "list-group-item-warning";

  let inputs = $('form.service_merge_form input[data-choose-item]').each((i, input) => {
    $(input).on("change", (event) => {
      items.removeClass(class_name);
      items.filter(`[data-id=${event.target.value}]`).addClass(class_name);

      items.each((i, group) => {
        $(group).find(".inputs :input,select").prop("readOnly", !$(group).hasClass(class_name));
      })
    });
  });

  inputs.filter(":checked").change();
});
