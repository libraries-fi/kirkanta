$.fn.collection = function() {
  return this.each((i, fieldset) => {
    // const template = fieldset.dataset.template;

    let btnAdd = $("<button/>")
      .attr("type", "button")
      .attr("class", "btn btn-outline-primary btn-sm")
      .text("Add")
      ;

    let icon = $("<i/>").attr("class", "fas fa-plus-circle");

    btnAdd.prepend(icon);
    btnAdd.insertBefore(fieldset);

    btnAdd.on("click", (event) => {
      const i = fieldset.children.length + 1;
      const template = fieldset.dataset.prototype
        .replace(/__name__label__/g, `Row ${i}`)
        .replace(/__name__/g, i - 1)
        ;

      let row = $(template)
        .attr("class", "list-group-item")
      ;

      row.appendTo(fieldset);
      row.children().last().collapse();

      const legend = row.children("legend");

      let btnCollapse = $("<button/>")
        .attr("type", "button")
        .attr("class", "btn btn-link btn-lg")
        .append($("<i/>").attr("class", "fas fa-caret-square-down"))
        .appendTo(row.children("legend"))
        ;

      let btnExpand = $("<button/>")
        .attr("type", "button")
        .attr("class", "btn btn-link btn-lg")
        .append($("<i/>").attr("class", "far fa-caret-square-up"))
        .appendTo(row.children("legend"))
        .hide()
        ;

      let content = row.children().last();

      btnCollapse.on("click", () => {
        content.collapse("hide");
        btnCollapse.hide();
        btnExpand.show();
      });

      btnExpand.on("click", () => {
        content.collapse("show");
        btnCollapse.show();
        btnExpand.hide();
      });
    });
  });
};

$('[data-collection]').collection();
