$("#btn-manage-entity-translations").each((i, button) => {
  const template = $($('script[type="text/x-service-popover"]#no-body')[0].textContent)[0];

  $(button)
    .popover({
      container: button.parentElement,
      title: () => '',
      // content: "Manage translations",
      content: () => document.createElement('span'),
      placement: "bottom",
      trigger: "click",
      template: $('script[type="text/x-service-popover"]#no-body')[0].textContent,
    })
    .popover("show")
    .on("inserted.bs.popover", (event) => {
      console.log("INSERTED", event);
    });
});

$(document).on("click", ".popover .close", (event) => {
  $(event.currentTarget).closest(".popover").popover("hide");
});
