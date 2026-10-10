"use strict";
const button = document.querySelector("#details-button");
const details = document.querySelector("#details");
if (button && details) button.addEventListener("click", () => {
  details.hidden = !details.hidden;
  button.setAttribute("aria-expanded", String(!details.hidden));
  button.textContent = details.hidden ? "Подробнее" : "Скрыть";
});
