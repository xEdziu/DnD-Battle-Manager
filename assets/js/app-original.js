// D&D Battle Manager JavaScript

// Global functions that need to be accessible from AJAX calls
function sortTable() {
  const tbody = document.querySelector("#battleForm table tbody");
  if (!tbody) return;

  const rows = Array.from(tbody.querySelectorAll("tr"));
  rows.sort((r1, r2) => {
    const dead1 = r1.classList.contains("opacity-50");
    const dead2 = r2.classList.contains("opacity-50");

    if (dead1 !== dead2) {
      return dead1 ? 1 : -1; // dead goes last
    }
    const init1 = parseInt(r1.querySelector('input[name^="init"]').value) || 0;
    const init2 = parseInt(r2.querySelector('input[name^="init"]').value) || 0;
    return init2 - init1; // descending
  });
  rows.forEach((row) => tbody.appendChild(row));
}

// Function to update button states and counts
function updateButtonStates() {
  const selected = document.querySelectorAll('input[name="selected"]:checked');
  const count = selected.length;
  const selectAllCheckbox = document.getElementById("selectAll");
  const allCheckboxes = document.querySelectorAll(".participant-checkbox");

  // Update select all checkbox state
  if (selectAllCheckbox) {
    selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
    selectAllCheckbox.checked = count === allCheckboxes.length && count > 0;
  }

  // Update button states and counts
  const damageBtn = document.getElementById("damageBtn");
  const healBtn = document.getElementById("healBtn");
  const removeBtn = document.getElementById("removeBtn");

  if (damageBtn) {
    damageBtn.disabled = count === 0;
    if (count === 0) {
      damageBtn.classList.add(
        "disabled:pointer-events-none",
        "disabled:opacity-50"
      );
    } else {
      damageBtn.classList.remove(
        "disabled:pointer-events-none",
        "disabled:opacity-50"
      );
    }
    const damageCount = document.getElementById("damageCount");
    if (damageCount) damageCount.textContent = count;
  }
  if (healBtn) {
    healBtn.disabled = count === 0;
    if (count === 0) {
      healBtn.classList.add(
        "disabled:pointer-events-none",
        "disabled:opacity-50"
      );
    } else {
      healBtn.classList.remove(
        "disabled:pointer-events-none",
        "disabled:opacity-50"
      );
    }
    const healCount = document.getElementById("healCount");
    if (healCount) healCount.textContent = count;
  }
  if (removeBtn) {
    removeBtn.disabled = count === 0;
    if (count === 0) {
      removeBtn.classList.add(
        "disabled:pointer-events-none",
        "disabled:opacity-50"
      );
    } else {
      removeBtn.classList.remove(
        "disabled:pointer-events-none",
        "disabled:opacity-50"
      );
    }
    const removeCount = document.getElementById("removeCount");
    if (removeCount) removeCount.textContent = count;
  }

  // Update row highlighting with modern style
  document
    .querySelectorAll("#battleForm tr")
    .forEach((tr) =>
      tr.classList.remove("bg-dnd-gold/10", "ring-1", "ring-dnd-gold")
    );
  selected.forEach((checkbox) => {
    const row = checkbox.closest("tr");
    row.classList.add("bg-dnd-gold/10", "ring-1", "ring-dnd-gold");
  });
}

document.addEventListener("DOMContentLoaded", function () {
  // Initialize Lucide icons
  lucide.createIcons();

  // Attach SweetAlert2 to deletion links for presets
  document.querySelectorAll(".delete-preset-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      Swal.fire({
        title: "Delete Preset?",
        text: "Are you sure you want to delete this preset?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
          cancelButton: "bg-dnd-crimson hover:bg-red-600 text-white",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          window.location = this.href;
        }
      });
    });
  });

  // Attach SweetAlert2 to deletion buttons for battles
  document.querySelectorAll(".delete-battle-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const battleId = this.getAttribute("data-battle-id");
      const battleName = this.getAttribute("data-battle-name");

      Swal.fire({
        title: "Delete Battle?",
        text: `Are you sure you want to delete "${battleName}"? This will also remove all participants.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
          cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          // Create a form and submit it
          const form = document.createElement("form");
          form.method = "POST";
          form.action = "index.php";

          const actionInput = document.createElement("input");
          actionInput.type = "hidden";
          actionInput.name = "action";
          actionInput.value = "delete_battle";

          const battleIdInput = document.createElement("input");
          battleIdInput.type = "hidden";
          battleIdInput.name = "battle_id";
          battleIdInput.value = battleId;

          form.appendChild(actionInput);
          form.appendChild(battleIdInput);
          document.body.appendChild(form);

          console.log("Submitting form to delete battle:", battleId);
          form.submit();
        }
      });
    });
  });

  // New Battle button with prompt for name
  const newBattleBtn = document.getElementById("newBattleBtn");
  const newBattleBtn2 = document.getElementById("newBattleBtn2");

  function handleNewBattle() {
    Swal.fire({
      title: "New Battle",
      text: "Enter a name for the new battle:",
      input: "text",
      inputValue: "",
      showCancelButton: true,
      confirmButtonText: "Create",
      cancelButtonText: "Cancel",
      customClass: {
        confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
        cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        let name = result.value ? encodeURIComponent(result.value) : "";
        window.location = "index.php?action=create_battle&name=" + name;
      }
    });
  }

  if (newBattleBtn) {
    newBattleBtn.addEventListener("click", handleNewBattle);
  }

  if (newBattleBtn2) {
    newBattleBtn2.addEventListener("click", handleNewBattle);
  }

  // HP damage button
  const damageBtn = document.getElementById("damageBtn");
  if (damageBtn) {
    damageBtn.addEventListener("click", function () {
      const selected = document.querySelectorAll(
        'input[name="selected"]:checked'
      );
      if (selected.length === 0) {
        Swal.fire({
          title: "No selection",
          text: "Please select at least one participant first.",
          icon: "info",
          customClass: {
            confirmButton: "bg-dnd-royal hover:bg-blue-600 text-white",
          },
        });
        return;
      }

      const participantIds = Array.from(selected).map((cb) => cb.value);
      const battleId = document.querySelector('input[name="battle_id"]').value;
      const count = selected.length;

      Swal.fire({
        title: `Apply Damage to ${count} participant${count > 1 ? "s" : ""}`,
        text: "How many points of damage?",
        input: "number",
        inputAttributes: {
          min: 0,
        },
        showCancelButton: true,
        confirmButtonText: "Apply",
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
          cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
        },
      }).then((result) => {
        if (result.value !== undefined && result.value > 0) {
          updateHP(participantIds, battleId, "damage", result.value);
        }
      });
    });
  }

  // HP heal button
  const healBtn = document.getElementById("healBtn");
  if (healBtn) {
    healBtn.addEventListener("click", function () {
      const selected = document.querySelectorAll(
        'input[name="selected"]:checked'
      );
      if (selected.length === 0) {
        Swal.fire({
          title: "No selection",
          text: "Please select at least one participant first.",
          icon: "info",
          customClass: {
            confirmButton: "bg-dnd-royal hover:bg-blue-600 text-white",
          },
        });
        return;
      }

      const participantIds = Array.from(selected).map((cb) => cb.value);
      const battleId = document.querySelector('input[name="battle_id"]').value;
      const count = selected.length;

      Swal.fire({
        title: `Apply Healing to ${count} participant${count > 1 ? "s" : ""}`,
        text: "How many points to heal?",
        input: "number",
        inputAttributes: {
          min: 0,
        },
        showCancelButton: true,
        confirmButtonText: "Apply",
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
          cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
        },
      }).then((result) => {
        if (result.value !== undefined && result.value > 0) {
          updateHP(participantIds, battleId, "heal", result.value);
        }
      });
    });
  }

  // Remove participant button
  const removeBtn = document.getElementById("removeBtn");
  if (removeBtn) {
    removeBtn.addEventListener("click", function () {
      const selected = document.querySelectorAll(
        'input[name="selected"]:checked'
      );
      if (selected.length === 0) {
        Swal.fire({
          title: "No selection",
          text: "Please select at least one participant to remove.",
          icon: "info",
          customClass: {
            confirmButton: "bg-dnd-royal hover:bg-blue-600 text-white",
          },
        });
        return;
      }

      const participantIds = Array.from(selected).map((cb) => cb.value);
      const battleId = document.querySelector('input[name="battle_id"]').value;
      const count = selected.length;

      Swal.fire({
        title: "Remove Participants",
        text: `Remove ${count} participant${
          count > 1 ? "s" : ""
        } from the battle?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, remove",
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
          cancelButton: "bg-dnd-crimson hover:bg-red-600 text-white",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          removeParticipants(participantIds, battleId);
        }
      });
    });
  }

  // Add event listeners to initiative inputs
  document.querySelectorAll(".initiative-input").forEach((input) => {
    input.addEventListener("change", function () {
      const pid = this.name.match(/\[(\d+)\]/)[1];
      const battleId = document.querySelector('input[name="battle_id"]').value;
      const initiative = this.value;

      updateInitiative(pid, battleId, initiative);
      sortTable();
    });
  });

  // Add debounced name updates
  document.querySelectorAll('input[name^="name["]').forEach((input) => {
    let timeout;
    input.addEventListener("input", function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        const pid = this.name.match(/\[(\d+)\]/)[1];
        const battleId = document.querySelector(
          'input[name="battle_id"]'
        ).value;
        const name = this.value;

        updateName(pid, battleId, name);
      }, 1000); // Update after 1 second of no typing
    });
  });

  // Sort the table on initial load as well
  if (document.querySelector(".initiative-input")) {
    sortTable();
  }

  // Select All functionality
  const selectAllCheckbox = document.getElementById("selectAll");
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function () {
      const participantCheckboxes = document.querySelectorAll(
        ".participant-checkbox"
      );
      participantCheckboxes.forEach((checkbox) => {
        checkbox.checked = this.checked;
      });
      updateButtonStates();
    });
  }

  // Update button states when individual checkboxes change
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("participant-checkbox")) {
      updateButtonStates();
    }
  });

  // Initialize button states
  updateButtonStates();

  // Sort by initiative button
  const sortByInitBtn = document.getElementById("sortByInit");
  if (sortByInitBtn) {
    sortByInitBtn.addEventListener("click", function () {
      sortTable();
      Swal.fire({
        title: "Sorted!",
        text: "Participants sorted by initiative (highest first)",
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
        customClass: {
          confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
        },
      });
    });
  }

  // Deselect when clicking outside the table
  document.addEventListener("click", function (e) {
    const battleTable = document.querySelector("#battleForm table");
    if (battleTable && !battleTable.contains(e.target)) {
      // Check if click is outside table and not on action buttons
      const actionButtons = document.querySelectorAll(
        "#damageBtn, #healBtn, #removeBtn"
      );
      let clickedActionButton = false;
      actionButtons.forEach((btn) => {
        if (btn.contains(e.target)) {
          clickedActionButton = true;
        }
      });

      if (!clickedActionButton) {
        // Deselect all checkboxes
        document
          .querySelectorAll('input[name="selected"]')
          .forEach((checkbox) => {
            checkbox.checked = false;
          });
        updateButtonStates();
      }
    }
  });
});

// AJAX Functions for faster updates
async function updateHP(participantIds, battleId, action, amount) {
  try {
    // Handle both single ID and array of IDs
    const ids = Array.isArray(participantIds)
      ? participantIds
      : [participantIds];

    const response = await fetch("api/battle_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "update_hp",
        participant_ids: ids.join(","),
        battle_id: battleId,
        hp_action: action,
        amount: amount,
      }),
    });

    const result = await response.json();

    if (result.success && result.participants) {
      // Update HP display for each participant
      result.participants.forEach((participant) => {
        const row = document
          .querySelector(`input[value="${participant.id}"]`)
          .closest("tr");
        const hpCell = row.children[3]; // HP column
        const newHP = participant.hp_current;
        const maxHP = participant.hp_max;
        const hpPercentage = maxHP > 0 ? (newHP / maxHP) * 100 : 0;
        const hpColor =
          hpPercentage > 75
            ? "text-dnd-emerald"
            : hpPercentage > 25
            ? "text-dnd-gold"
            : "text-dnd-crimson";
        const barColor =
          hpPercentage > 75
            ? "bg-dnd-emerald"
            : hpPercentage > 25
            ? "bg-dnd-gold"
            : "bg-dnd-crimson";

        // Update HP display with modern styling
        hpCell.innerHTML = `
          <div class="flex items-center justify-center space-x-1">
            <span class="font-medium ${hpColor}">
              ${newHP}
            </span>
            <span class="text-muted-foreground">/</span>
            <span class="text-muted-foreground">${maxHP}</span>
          </div>
          <div class="w-full bg-secondary rounded-full h-1 mt-1">
            <div class="h-1 rounded-full transition-all duration-300 ${barColor}" 
                 style="width: ${Math.max(0, hpPercentage)}%"></div>
          </div>
        `;

        // Update row styling if dead
        if (newHP <= 0) {
          row.classList.add("opacity-50", "bg-destructive/5");
          const nameCell = row.querySelector('input[name^="name"]');
          nameCell.classList.add("bg-destructive/10", "text-destructive");

          // Add skull icon - completely replace the icon element
          const nameContainer = nameCell.parentElement;
          const icon = nameContainer.querySelector("i[data-lucide]");
          if (icon) {
            // Remove old icon and create new one
            icon.remove();
            const newIcon = document.createElement("i");
            newIcon.setAttribute("data-lucide", "skull");
            newIcon.className = "h-4 w-4 text-destructive";
            nameContainer.insertBefore(newIcon, nameCell);
          }
        } else {
          row.classList.remove("opacity-50", "bg-destructive/5");
          const nameCell = row.querySelector('input[name^="name"]');
          nameCell.classList.remove("bg-destructive/10", "text-destructive");

          // Restore user icon - completely replace the icon element
          const nameContainer = nameCell.parentElement;
          const icon = nameContainer.querySelector("i[data-lucide]");
          if (icon) {
            // Remove old icon and create new one
            icon.remove();
            const newIcon = document.createElement("i");
            newIcon.setAttribute("data-lucide", "user");
            newIcon.className = "h-4 w-4 text-muted-foreground";
            nameContainer.insertBefore(newIcon, nameCell);
          }
        }

        // Re-initialize Lucide icons for the updated elements
        lucide.createIcons();
      });

      // Re-sort table (dead go to bottom) - with small delay to ensure DOM is updated
      setTimeout(() => {
        sortTable();
      }, 100);

      // Clear selection
      document
        .querySelectorAll('input[name="selected"]')
        .forEach((cb) => (cb.checked = false));
      updateButtonStates();

      const actionText = action === "damage" ? "Damage" : "Healing";
      const count = result.updated_count;
      Swal.fire({
        title: "Success!",
        text: `${actionText} applied to ${count} participant${
          count > 1 ? "s" : ""
        }`,
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
        customClass: {
          confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
        },
      });
    } else {
      throw new Error(result.error || "Update failed");
    }
  } catch (error) {
    console.error("Error updating HP:", error);
    Swal.fire({
      title: "Error",
      text: "Failed to update HP. Please try again.",
      icon: "error",
      customClass: {
        confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
      },
    });
  }
}

async function removeParticipants(participantIds, battleId) {
  try {
    // Handle both single ID and array of IDs
    const ids = Array.isArray(participantIds)
      ? participantIds
      : [participantIds];

    const response = await fetch("api/battle_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "remove_participant",
        participant_ids: ids.join(","),
        battle_id: battleId,
      }),
    });

    const result = await response.json();

    if (result.success) {
      // Remove rows from table
      ids.forEach((participantId) => {
        const row = document
          .querySelector(`input[value="${participantId}"]`)
          .closest("tr");
        if (row) row.remove();
      });

      // Update button states
      updateButtonStates();

      const count = result.removed_count;
      Swal.fire({
        title: "Success!",
        text: `${count} participant${
          count > 1 ? "s" : ""
        } removed successfully`,
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
        customClass: {
          confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
        },
      });
    } else {
      throw new Error(result.error || "Remove failed");
    }
  } catch (error) {
    console.error("Error removing participants:", error);
    Swal.fire({
      title: "Error",
      text: "Failed to remove participants. Please try again.",
      icon: "error",
      customClass: {
        confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
      },
    });
  }
}

// Keep backward compatibility
async function removeParticipant(participantId, battleId) {
  return removeParticipants([participantId], battleId);
}

async function updateInitiative(participantId, battleId, initiative) {
  try {
    const response = await fetch("api/battle_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "update_initiative",
        participant_id: participantId,
        battle_id: battleId,
        initiative: initiative,
      }),
    });

    const result = await response.json();

    if (!result.success) {
      throw new Error(result.error || "Update failed");
    }
  } catch (error) {
    console.error("Error updating initiative:", error);
    // Silently fail for initiative updates, as they're not critical
  }
}

async function updateName(participantId, battleId, name) {
  try {
    const response = await fetch("api/battle_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "update_name",
        participant_id: participantId,
        battle_id: battleId,
        name: name,
      }),
    });

    const result = await response.json();

    if (!result.success) {
      throw new Error(result.error || "Update failed");
    }
  } catch (error) {
    console.error("Error updating name:", error);
    // Silently fail for name updates, as they're not critical
  }
}
