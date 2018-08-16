$.fn.tableDrag = function() {
  function findDropTarget(event) {
    for (let row of event.target.parentElement.children) {
      if (row != event.target) {
        let pos = event.target.offsetTop + event.offsetY - DRAG_ADJUST_POS;

        if (Math.abs(row.offsetTop - pos) < DROP_OFFSET_DELTA) {
          return row;
        }
      }
    }
  }

  /**
   * NOTE: This functions needs to be called before executing the drop i.e. re-ordering elements.
   */
  function getAffectedRows(first, last) {
    if (last.offsetTop < first.offsetTop) {
      let tmp = first;
      first = last;
      last = tmp;
    }

    let affected = [first];

    do {
      first = first.nextElementSibling;
      affected.push(first);
    } while (first != last);

    return affected;
  }

  // Maximum distance from the drop target edge for accepting the drop.
  const DROP_OFFSET_DELTA = 20;

  // Distance from drag coordinate to dragged element's top edge.
  let DRAG_ADJUST_POS = 0;

  // Last accepted drop target element.
  let DROP_TARGET_SUGGESTION = null;

  this.find("tbody").attr("dropzone", "move");

  this.find(".drag-handle").closest("tr")
    .prop("draggable", true)
    .on("mousedown", (event) => {
      if ($(event.target).closest(".drag-handle").length == 0) {
        event.preventDefault();
        // console.log(event.target);
      }
    })
    .on("dragstart", (event) => {
      DRAG_ADJUST_POS = event.offsetY;

      // Use timeout to allow the visual copy to retain original appearance.
      setTimeout(() => $(event.target).addClass("drag-active"), 100);
    })
    .on("drag", (event) => {
      let target = findDropTarget(event);

      if (target) {
        if (target != DROP_TARGET_SUGGESTION) {
          DROP_TARGET_SUGGESTION = target;
          $(target).addClass("drop-suggestion");
        }
      } else if (DROP_TARGET_SUGGESTION) {
        $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");
        DROP_TARGET_SUGGESTION = null;
      }
    })
    .on("dragend", (event) => {
      $(event.target).removeClass("drag-active");
      $(DROP_TARGET_SUGGESTION).removeClass("drop-suggestion");
      let target = findDropTarget(event);

      if (target) {
        let affected_rows = getAffectedRows(event.target, target);
        let after = event.target.offsetTop < target.offsetTop;
        target.insertAdjacentElement(after ? "afterend" : "beforebegin", event.target);

        $(event.target).closest("table").trigger("tabledragsuccess", {
          rows: affected_rows,
        });
      }

      DROP_TARGET_SUGGESTION = null;
    });

  return this;
};


$("table[data-app=\"table-drag\"]").tableDrag().on("tabledragsuccess", (event, custom_data) => {
  let url = window.location.pathname + "/tablesort";
  let handles = $(custom_data.rows).find(".drag-handle").toArray();

  console.log(handles);

  $.post(url, {
    rows: handles.map(h => h.dataset.dragId),
  });

});
