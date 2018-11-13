"use strict";

import React from "react";
import ReactDOM from "react-dom";
import { Calendar, DateTimePicker } from "react-widgets";
import { parse as parseDate } from "date-fns";

$.fn.datePicker = function() {
  this.each((i, input) => {
    let widget = React.createElement(DateTimePicker, {
      time: false,
      format: "dddd, MMMM DD, YYYY",
      defaultValue: input.value ? new Date(input.value) : null,
      footer: false,

      onChange: (value) => {
        input.value = moment(value).format("YYYY-MM-DD");
        this.first().trigger("kirkantaDateChange");
      }
    });

    let container = document.createElement("div");
    input.parentElement.insertBefore(container, input);
    input.style.display = "none";

    ReactDOM.render(widget, container);
  });
};

$.fn.timePicker = function() {
  this.each((i, input) => {
    // Prepend with date as date-fns v1 cannot parse only time...
    let current_time = input.value ? "2000-01-01T" + input.value : null;

    let widget = React.createElement(DateTimePicker, {
      date: false,
      format: "HH:mm",
      defaultValue: current_time ? parseDate(current_time) : null,
      footer: false,

      inputProps: {
        // readOnly: true
      },

      onChange: (value) => {
        input.value = moment(value).format("HH:mm");
      }
    });

    let container = document.createElement("div");
    input.parentElement.insertBefore(container, input);
    input.style.display = "none";

    ReactDOM.render(widget, container);
  });
};

$.fn.periodCalendar = function() {
  this.each((i, container) => {
    let calendar = React.createElement(Calendar, {
      footer: false,
      // currentDate: new Date,

      // onCurrentDateChange: (value) => {
      //   console.log(value);
      // }
    });

    ReactDOM.render(calendar, container);
  });
};

$("[data-app=\"date-picker\"]").datePicker();
// $("[data-app=\"time-picker\"]").timePicker();
$("[data-app=\"organisation-calendar\"]").periodCalendar();
