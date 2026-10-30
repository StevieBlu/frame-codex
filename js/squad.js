// Updates the role list as the four dropdowns change.
// The longer notes still come from squad.php after Check this squad.

var slots = document.querySelectorAll(".squad-slot");
var note = document.getElementById("squad-live-note");
var rows = document.querySelectorAll(".coverage-row");
var saveHint = document.getElementById("save-hint");
var saveButton = document.querySelector(".squad-save button");

function selectedFrames() {
    var frames = [];
    var seen = {};

    slots.forEach(function (slot) {
        var option = slot.options[slot.selectedIndex];

        if (!option || option.value === "0") {
            return;
        }

        var roles = option.getAttribute("data-roles") || "";

        frames.push({
            id: option.value,
            name: option.text,
            roles: roles === "" ? [] : roles.split("|"),
            duplicate: seen[option.value] === true
        });

        seen[option.value] = true;
    });

    return frames;
}

function paintCoverage() {
    var frames = selectedFrames();
    var duplicate = false;

    frames.forEach(function (frame) {
        if (frame.duplicate) {
            duplicate = true;
        }
    });

    rows.forEach(function (row) {
        var role = row.getAttribute("data-role");
        var names = [];

        frames.forEach(function (frame) {
            if (!frame.duplicate && frame.roles.indexOf(role) !== -1) {
                names.push(frame.name);
            }
        });

        row.querySelector(".coverage-names").textContent = names.length ? names.join(", ") : "Not covered";
        row.classList.toggle("is-missing", names.length === 0);
        row.classList.toggle("is-covered", names.length > 0);
    });

    if (frames.length < 4) {
        note.textContent = "Choose four Warframes to see how the roles fit.";
    } else if (duplicate) {
        note.textContent = "Pick four different Warframes. A Prime counts as its own card.";
    } else {
        note.textContent = "Covered roles are filled in. Use Check this squad for the written fit.";
    }

    if (saveButton && saveHint) {
        var changed = false;

        slots.forEach(function (slot) {
            var hidden = document.querySelector('.squad-save input[name="' + slot.name + '"]');

            if (hidden && hidden.value !== slot.value) {
                changed = true;
            }
        });

        saveButton.disabled = changed;
        saveHint.textContent = changed
            ? "You changed a slot. Check this squad again before saving."
            : "This saves the four Warframes you just checked.";
    }
}

slots.forEach(function (slot) {
    slot.addEventListener("change", paintCoverage);
});

paintCoverage();
