// Bootstrap opens the card that was clicked.
// This file closes every other open card so only one stays expanded.

document.querySelectorAll(".frame-card-toggle").forEach(function (button) {
    button.addEventListener("click", function () {
        var target = button.getAttribute("data-bs-target");

        document.querySelectorAll(".frame-details.show").forEach(function (panel) {
            if ("#" + panel.id !== target) {
                bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false }).hide();
            }
        });
    });
});
