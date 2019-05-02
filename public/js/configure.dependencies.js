
import * as ReactWidgets from "react-widgets";
import * as datefns from "date-fns";

let date_localizer = {
  firstOfWeek: (locale) => 1,
  formats: {
    default: "YYYY-MM-DD",
    date: "YYYY-MM-DD",
    time: "HH:mm",
    day: "DD",
    month: "MMM",
    year: "YYYY",
    dayOfMonth: "D",
    dayOfWeek: "DDD",
    footer: "dddd, MMMM DD, YYYY",
    header: "MMMM YYYY",
    decade: "[decade]",
    century: "[century]",
    weekday: "dd",
  },

  format: (value, format, locale) => {
    if (typeof format == "undefined") {
      console.error("UNDEFINED FORMAT PASSED");
    }
    return datefns.format(value, format);
  },

  parse: (value, format, locale) => {
    if (/^\d{1,2}\.\d{2}$/.test(value)) {
      value = value.replace(/\./, "");
    }

    if (/^\d{3,4}$/.test(value)) {
      let hours = value.substr(0, value.length - 2);
      let minutes = value.substr(-2);
      value = hours + ":" + minutes;

      if (value.length == 4) {
        value = "0" + value;
      }
    }

    if (/^\d{1,2}:\d{2}$/.test(value)) {
      // This hack works around datefns limitation of not being able to parse time-only formats.
      value = "2000-01-01T" + value;
    }

    let parsed = datefns.parse(value, format);

    return isNaN(parsed.getTime()) ? null : parsed;
  }
};

ReactWidgets.setDateLocalizer(date_localizer);
