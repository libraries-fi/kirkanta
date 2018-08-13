(function($) {
  "use strict";

  /***** DEFINE FUNCTIONS *****/

  var NAME_PLACEHOLDER = /__name__/g;

  function open_description(event) {
    var button = $(event.target);
    button.hide();
    button.siblings(".day-description-value").removeClass("hidden");
  }

  function insert_first_time(event) {
    var target_id = event.currentTarget.dataset.target;
    var container = $(target_id);
    var proto = container[0].dataset.prototype.replace(NAME_PLACEHOLDER, container.children().length);
    var item = $(proto);

    item.appendTo(container);

    // container.closest(".hidden").removeClass("hidden");
    var day_row = container.closest(".period-day-data");
    day_row.find(".period-day-data-times").removeClass("hidden");
    day_row.find(".period-day-closed").addClass("hidden");

    init_time_row(item);
    day_row.find("input").first().focus();
  }

  function add_time_row(event) {
    var container = $(event.target).closest("[data-prototype]");
    var proto = container[0].dataset.prototype.replace(NAME_PLACEHOLDER, container.children().length);
    var item = $(proto);

    var previous = container.children().last().find("input[type=\"text\"]").last().val();

    item.find("input[type=\"text\"]").first().val(previous);
    item.find("input[type=\"checkbox\"]").prop("checked", true);
    item.appendTo(container);

    init_time_row(item);
    item.find("input").first().focus();
  }

  function delete_time_row(event) {
    var row = $(event.currentTarget.dataset.target);
    var container = row.parent();

    row.remove();

    if (container.children().length == 0) {
      var day_row = container.closest(".period-day-data");
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
    var children = $(container).children();

    if (children.length > field_count) {
      children.slice(field_count).each(function(i, node) {
        container.removeChild(node);
      });
    }

    if (children.length < field_count) {
      var collection = $(container);
      for (var i = children.length + 1; i <= field_count; i++) {
        var child = $(template.replace(NAME_PLACEHOLDER, i - 1));
        collection.append(child);
        init_day_row(child);
      }
    }
  }



  // return;



  /***** BEGIN INITIALIZATION *****/

  $("form.period_form").each(function(i, form) {
    init_day_row(form);
    init_time_row($(form).find(".period-day-data-time-item"));
  });

  $("[data-show]").on("click", show_element);
  $("[data-hide]").on("click", hide_element);

  var days = $("[data-app=\"period-days\"]");
  var slider = $("[data-app=\"period-slider\"]");

  // Really dirty hacks!
  $("[data-app=\"date-picker\"]")
    .on("kirkantaDateChange", function() {
      var inputs = $("[data-app=\"date-picker\"]");
      var a = new Date(inputs[0].value);
      var b = new Date(inputs[1].value);
      var day_count = Math.min(moment(b).diff(a, "days") + 1);

      this.dataset.dayCount = day_count;
      this.dataset.firstDate = moment(a).format("YYYY-MM-DD");
      this.dataset.lastDate = moment(b).format("YYYY-MM-DD");
    })
    .on("kirkantaDateChange", function() {
      var day_count = parseInt(this.dataset.dayCount);

      if (day_count > 0) {
        var container = days[0];
        var template = container.dataset.prototype;
        var field_count = Math.min(day_count, parseInt(slider.val()) * 7);

        change_day_count(container, template, field_count);

        slider.prop("disabled", day_count < 14);
        slider.find("option[value=3]").prop("disabled", day_count < 21);
        slider.find("option[value=4]").prop("disabled", day_count < 28);
        slider.find("option[value=5]").prop("disabled", day_count < 35);
      }
    })
    .on("kirkantaDateChange", function() {
      var day_count = parseInt(this.dataset.dayCount);
      var date = moment(this.dataset.firstDate);
      var range = parseInt(slider.val()) * 7;

      days.find(".day-name").each(function(i, element) {
        if (day_count < 7) {
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
    })
    .first()
    .trigger("kirkantaDateChange");
}(jQuery));
