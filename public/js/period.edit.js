(function($) {
  "use strict";

  /***** DEFINE FUNCTIONS *****/

  let NAME_PLACEHOLDER = /__name__/g;

  function open_description(event) {
    let button = $(event.target);
    button.hide();
    button.siblings(".day-description-value").removeClass("hidden");
  }

  function insert_first_time(event) {
    let target_id = event.currentTarget.dataset.target;
    let container = $(target_id);
    let proto = container[0].dataset.prototype.replace(NAME_PLACEHOLDER, container.children().length);
    let item = $(proto);

    item.appendTo(container);

    // container.closest(".hidden").removeClass("hidden");
    let day_row = container.closest(".period-day-data");
    day_row.find(".period-day-data-times").removeClass("hidden");
    day_row.find(".period-day-closed").addClass("hidden");

    init_time_row(item);
    day_row.find("input").first().focus();
  }

  function add_time_row(event) {
    let container = $(event.target).closest("[data-prototype]");
    let proto = container[0].dataset.prototype.replace(NAME_PLACEHOLDER, container.children().length);
    let item = $(proto);

    let previous = container.children().last().find("input[type=\"text\"]").last().val();

    item.find("input[type=\"text\"]").first().val(previous);
    item.find("input[type=\"checkbox\"]").prop("checked", true);
    item.appendTo(container);

    init_time_row(item);
    item.find("input").first().focus();
  }

  function delete_time_row(event) {
    let row = $(event.currentTarget.dataset.target);
    let container = row.parent();

    row.remove();

    if (container.children().length == 0) {
      let day_row = container.closest(".period-day-data");
      day_row.find(".period-day-data-times").addClass("hidden");
      day_row.find(".period-day-closed").removeClass("hidden");
    }
  }

  function hide_element(event) {
    $(event.currentTarget.dataset.hide).addClass("hidden");
  }

  function show_element(event) {
    $(event.currentTarget.dataset.show).removeClass("hidden");
  }

  function init_day_row(container) {
    $(container).find('button[data-action="edit-day-description"]').on("click", open_description);
    $(container).find('button[data-action="insert-first-time"]').on("click", insert_first_time);
  }

  function init_time_row(container) {
    $(container).find("input[type=\"text\"]").timePicker();
    $(container).find('button[data-action="add-time-row"]').on("click", add_time_row);
    $(container).find('button[data-action="delete-time-row"]').on("click", delete_time_row);
  }

  function change_day_count(container, template, field_count) {
    let children = $(container).children();

    if (children.length > field_count) {
      children.slice(field_count).each(function(i, node) {
        container.removeChild(node);
      });
    }

    if (children.length < field_count) {
      let collection = $(container);
      for (let i = children.length + 1; i <= field_count; i++) {
        let child = $(template.replace(NAME_PLACEHOLDER, i - 1));
        collection.append(child);
        init_day_row(child);
      }
    }
  }

  function update_day_names(day_fields, mode, first_date) {
    let date = moment(first_date);

    day_fields.find(".day-name").each(function(i, element) {
      if (mode == MODE_WITH_DATES) {
        // element.innerText = date.format("dddd D.M.");
        $(element)
          .empty()
          .append($("<time/>").attr("datetime", date.format("Y-M-D")).text(date.format("dddd, D.M.")));

        date.add(1, "days");
      } else {
        element.innerText = date.isoWeekday(i + 1).format("dddd");

        if (i % 7 == 0) {
          $(element).closest(".row").attr("data-label", "Week " + (i / 7 + 1));
        }
      }
    });
  }

  const MODE_REGULAR = 1;
  const MODE_WITH_DATES = 2;

  $("form.period_form").each(function(i, form) {
    init_day_row(form);
    init_time_row($(form).find(".period-day-data-time-item"));
  });

  $("[data-show]").on("click", show_element);
  $("[data-hide]").on("click", hide_element);

  let days = $("[data-app=\"period-days\"]");
  let slider = $("[data-app=\"period-slider\"]");
  let date_pickers = $("[data-app=\"date-picker\"]");

  date_pickers
    .on("kirkantaDateChange", function() {
      let inputs = $("[data-app=\"date-picker\"]");
      let a = new Date(inputs[0].value);
      let b = new Date(inputs[1].value);
      let day_count = Math.min(moment(b).diff(a, "days") + 1);

      this.dataset.dayCount = day_count || 999;
      this.dataset.firstDate = moment(a).format("YYYY-MM-DD");
      this.dataset.lastDate = moment(b).format("YYYY-MM-DD");
    })
    .on("kirkantaDateChange", function() {
      let day_count = parseInt(this.dataset.dayCount);

      if (day_count > 0) {
        slider.prop("disabled", day_count < 14);
        slider.find("option[value=3]").prop("disabled", day_count < 21);
        slider.find("option[value=4]").prop("disabled", day_count < 28);
        slider.find("option[value=5]").prop("disabled", day_count < 35);
      }

      slider.trigger("change");
    });

    slider.on("change", (event) => {
      let day_count = date_pickers.get(0).dataset.dayCount;
      let first_date = date_pickers.get(0).dataset.firstDate;

      let container = days[0];
      let template = container.dataset.prototype;
      let field_count = Math.min(day_count, parseInt(slider.val()) * 7);
      let mode = field_count < 7 ? MODE_WITH_DATES : MODE_REGULAR;

      change_day_count(container, template, field_count);
      update_day_names(days, mode, first_date);
    });

    date_pickers.first().trigger("kirkantaDateChange");
}(jQuery));
