import React from "react";
import ReactDOM from "react-dom";
import { DropdownList } from "react-widgets";

document.querySelectorAll("select").forEach((select) => {
  let widget = React.createElement(DropdownList, {
    textField: 'text',
    valueField: 'value',
    data: Array.from(select.querySelectorAll("option")),
    defaultValue: select.querySelector("[selected]"),
    placeholder: select.children[0].text,

    onChange(option) {
      option.selected = true;
      select.dispatchEvent(new Event("change"));
    }
  });

  const container = document.createElement("div");
  container.className = "rw-container";
  select.parentElement.insertBefore(container, select);
  select.style.display = "none";
  ReactDOM.render(widget, container);

});
