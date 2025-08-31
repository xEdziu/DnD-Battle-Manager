// Simplified Global App.js
// Contains only global functionality and shared utilities

// Global utility functions
function h(str) {
  return str
    ? String(str).replace(/[&<>"']/g, function (s) {
        return {
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        }[s];
      })
    : "";
}

// AJAX helper
function submitAjaxForm(action, battleId, participantIds = [], amount = "") {
  document.getElementById("ajaxAction").value = action;
  document.getElementById("ajaxBattleId").value = battleId;
  document.getElementById("ajaxParticipantIds").value = Array.isArray(
    participantIds
  )
    ? participantIds.join(",")
    : participantIds;
  document.getElementById("ajaxAmount").value = amount;
  document.getElementById("ajaxForm").submit();
}

// Initialize Lucide icons
function initializeLucide() {
  if (typeof lucide !== "undefined" && lucide.createIcons) {
    lucide.createIcons();
  }
}

// Global functionality that applies to all pages
document.addEventListener("DOMContentLoaded", function () {
  // Initialize Lucide icons
  initializeLucide();

  // Initialize global features
  initializeGlobalFeatures();

  // Re-initialize icons after any dynamic content changes
  const observer = new MutationObserver(function (mutations) {
    let shouldUpdate = false;
    mutations.forEach(function (mutation) {
      if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
        shouldUpdate = true;
      }
    });
    if (shouldUpdate) {
      setTimeout(initializeLucide, 100);
    }
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
});

function initializeGlobalFeatures() {
  // Global preset deletion with SweetAlert2
  document.querySelectorAll(".delete-preset-link").forEach((link) => {
    if (!link.hasAttribute("data-listener-added")) {
      link.setAttribute("data-listener-added", "true");
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const presetName = this.dataset.presetName;
        const url = this.href;

        Swal.fire({
          title: "Delete Preset",
          text: `Are you sure you want to delete "${presetName}"?`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, delete it!",
          cancelButtonText: "Cancel",
          customClass: {
            confirmButton: "bg-red-600 hover:bg-red-700 text-white",
            cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
          },
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = url;
          }
        });
      });
    }
  });

  // Global battle deletion with SweetAlert2
  document.querySelectorAll(".delete-battle-btn").forEach((button) => {
    if (!button.hasAttribute("data-listener-added")) {
      button.setAttribute("data-listener-added", "true");
      button.addEventListener("click", function (e) {
        e.preventDefault();
        const battleName = this.dataset.battleName;
        const battleId = this.dataset.battleId;

        Swal.fire({
          title: "Delete Battle",
          text: `Are you sure you want to delete "${battleName}"?`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, delete it!",
          cancelButtonText: "Cancel",
          customClass: {
            confirmButton: "bg-red-600 hover:bg-red-700 text-white",
            cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
          },
        }).then((result) => {
          if (result.isConfirmed) {
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
            form.submit();
          }
        });
      });
    }
  });

  // Global battle edit with SweetAlert2
  document.querySelectorAll(".edit-battle-btn").forEach((button) => {
    if (!button.hasAttribute("data-listener-added")) {
      button.setAttribute("data-listener-added", "true");
      button.addEventListener("click", function (e) {
        e.preventDefault();
        const battleId = this.dataset.battleId;
        const battleName = this.dataset.battleName;
        const battleDescription = this.dataset.battleDescription;

        handleEditBattleInfo(battleId, battleName, battleDescription);
      });
    }
  });

  // Global new battle creation with SweetAlert2
  const newBattleButtons = document.querySelectorAll(
    "#newBattleBtn, #newBattleBtn2"
  );
  newBattleButtons.forEach((button) => {
    if (button && !button.hasAttribute("data-listener-added")) {
      button.setAttribute("data-listener-added", "true");
      button.addEventListener("click", handleNewBattle);
    }
  });

  // Battle-specific functionality
  initializeBattleActions();
  initializeParticipantCheckboxes();

  // Initialize battle detail events (participant modals, delete buttons)
  initializeBattleDetailEvents();
}

function handleNewBattle() {
  Swal.fire({
    title: "Create New Battle",
    html: `
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Battle Name</label>
          <input id="battle-name" class="w-full p-2 border border-gray-300 rounded" 
                 placeholder="Enter battle name..." />
        </div>
        <div>
          <label class="block text-sm font-medium mb-2">Description (optional)</label>
          <textarea id="battle-description" class="w-full p-2 border border-gray-300 rounded h-24" 
                    placeholder="Enter battle description..."></textarea>
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Create Battle",
    cancelButtonText: "Cancel",
    preConfirm: () => {
      const name = document.getElementById("battle-name").value.trim();
      const description = document
        .getElementById("battle-description")
        .value.trim();

      if (!name) {
        Swal.showValidationMessage("Please enter a battle name!");
        return false;
      }

      return { name, description };
    },
    customClass: {
      confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
      cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "index.php";

      const actionInput = document.createElement("input");
      actionInput.type = "hidden";
      actionInput.name = "action";
      actionInput.value = "create_battle";

      const nameInput = document.createElement("input");
      nameInput.type = "hidden";
      nameInput.name = "name";
      nameInput.value = result.value.name;

      const descriptionInput = document.createElement("input");
      descriptionInput.type = "hidden";
      descriptionInput.name = "description";
      descriptionInput.value = result.value.description;

      form.appendChild(actionInput);
      form.appendChild(nameInput);
      form.appendChild(descriptionInput);
      document.body.appendChild(form);
      form.submit();
    }
  });
}

function handleEditBattleInfo(battleId, currentName, currentDescription) {
  Swal.fire({
    title: "Edit Battle Information",
    html: `
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Battle Name</label>
          <input id="edit-battle-name" class="w-full p-2 border border-gray-300 rounded" 
                 value="${
                   currentName || ""
                 }" placeholder="Enter battle name..." />
        </div>
        <div>
          <label class="block text-sm font-medium mb-2">Description</label>
          <textarea id="edit-battle-description" class="w-full p-2 border border-gray-300 rounded h-24" 
                    placeholder="Enter battle description...">${
                      currentDescription || ""
                    }</textarea>
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Update Battle",
    cancelButtonText: "Cancel",
    preConfirm: () => {
      const name = document.getElementById("edit-battle-name").value.trim();
      const description = document
        .getElementById("edit-battle-description")
        .value.trim();

      if (!name) {
        Swal.showValidationMessage("Please enter a battle name!");
        return false;
      }

      return { name, description };
    },
    customClass: {
      confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
      cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Use AJAX to update battle info
      const formData = new FormData();
      formData.append("action", "update_battle_info");
      formData.append("battle_id", battleId);
      formData.append("name", result.value.name);
      formData.append("description", result.value.description);

      fetch("index.php", {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            Swal.fire({
              title: "Success!",
              text: data.message,
              icon: "success",
              customClass: {
                confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
              },
            }).then(() => {
              // Update the UI without page reload
              updateBattleInfoInUI(
                battleId,
                result.value.name,
                result.value.description
              );
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: data.message,
              icon: "error",
              customClass: {
                confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
              },
            });
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          Swal.fire({
            title: "Error!",
            text: "An unexpected error occurred.",
            icon: "error",
            customClass: {
              confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
            },
          });
        });
    }
  });
}

// Export for use in other modules
window.AppUtils = {
  h,
  submitAjaxForm,
  initializeLucide,
  handleNewBattle,
  handleEditBattleInfo,
};

// Battle-specific functionality
function initializeBattleActions() {
  // HP damage button
  const damageBtn = document.getElementById("damageBtn");
  if (damageBtn && !damageBtn.hasAttribute("data-listener-added")) {
    damageBtn.setAttribute("data-listener-added", "true");
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
          submitAjaxForm("damage", battleId, participantIds, result.value);
        }
      });
    });
  }

  // HP heal button
  const healBtn = document.getElementById("healBtn");
  if (healBtn && !healBtn.hasAttribute("data-listener-added")) {
    healBtn.setAttribute("data-listener-added", "true");
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
          submitAjaxForm("heal", battleId, participantIds, result.value);
        }
      });
    });
  }

  // Remove participant button
  const removeBtn = document.getElementById("removeBtn");
  if (removeBtn && !removeBtn.hasAttribute("data-listener-added")) {
    removeBtn.setAttribute("data-listener-added", "true");
    removeBtn.addEventListener("click", function () {
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
        title: `Remove ${count} participant${count > 1 ? "s" : ""}?`,
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, remove!",
        cancelButtonText: "Cancel",
        customClass: {
          confirmButton: "bg-red-600 hover:bg-red-700 text-white",
          cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          submitAjaxForm("remove_participant", battleId, participantIds);
        }
      });
    });
  }
}

function initializeParticipantCheckboxes() {
  const selectAllCheckbox = document.getElementById("selectAll");
  if (
    selectAllCheckbox &&
    !selectAllCheckbox.hasAttribute("data-listener-added")
  ) {
    selectAllCheckbox.setAttribute("data-listener-added", "true");
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

  // Listen for checkbox changes to update button states (only add once)
  if (!document.documentElement.hasAttribute("data-checkbox-listener-added")) {
    document.documentElement.setAttribute(
      "data-checkbox-listener-added",
      "true"
    );
    document.addEventListener("change", function (e) {
      if (e.target.classList.contains("participant-checkbox")) {
        console.log("Checkbox changed:", e.target);
        updateButtonStates();
      }
    });
  }

  // Initialize button states
  updateButtonStates();
}

function updateButtonStates() {
  const selected = document.querySelectorAll('input[name="selected"]:checked');
  const selectAllCheckbox = document.getElementById("selectAll");
  const allCheckboxes = document.querySelectorAll(".participant-checkbox");

  console.log("updateButtonStates called, selected:", selected.length);

  // Update select all checkbox state
  if (selectAllCheckbox) {
    selectAllCheckbox.indeterminate =
      selected.length > 0 && selected.length < allCheckboxes.length;
    selectAllCheckbox.checked =
      selected.length === allCheckboxes.length && allCheckboxes.length > 0;
  }

  // Update button states
  const damageBtn = document.getElementById("damageBtn");
  const healBtn = document.getElementById("healBtn");
  const removeBtn = document.getElementById("removeBtn");

  const hasSelection = selected.length > 0;

  if (damageBtn) {
    damageBtn.disabled = !hasSelection;
    const damageCount = document.getElementById("damageCount");
    if (damageCount) damageCount.textContent = selected.length;
  }

  if (healBtn) {
    healBtn.disabled = !hasSelection;
    const healCount = document.getElementById("healCount");
    if (healCount) healCount.textContent = selected.length;
  }

  if (removeBtn) {
    removeBtn.disabled = !hasSelection;
    const removeCount = document.getElementById("removeCount");
    if (removeCount) removeCount.textContent = selected.length;
  }
}

// Function to initialize participant modal functionality - DISABLED
function initializeParticipantModal() {
  // Functionality moved to inline expandable rows in detail.php
  console.log(
    "Participant modal functionality disabled - using expandable rows"
  );
}

// Function to show participant details modal - DISABLED
function showParticipantDetails(participant) {
  // Functionality moved to inline expandable rows in detail.php
  console.log("showParticipantDetails disabled - using expandable rows");
}

// Initialize battle detail specific events
function initializeBattleDetailEvents() {
  // Particle detail modal initialization
  if (document.querySelector(".participant-row")) {
    console.log(
      "Battle detail page detected, initializing participant modal functionality"
    );
    initializeParticipantModal();
  }

  // Delete participant buttons with event delegation (only add once)
  if (!document.hasAttribute("data-delete-participant-listener-added")) {
    document.setAttribute("data-delete-participant-listener-added", "true");
    document.addEventListener("click", function (e) {
      if (e.target.closest(".delete-participant-btn")) {
        e.stopPropagation();
        const button = e.target.closest(".delete-participant-btn");
        const participantId = button.dataset.participantId;
        const participantName = button.dataset.participantName;
        const battleId = button.dataset.battleId;

        console.log("Delete participant clicked:", {
          participantId,
          participantName,
          battleId,
        });

        Swal.fire({
          title: "Remove Participant",
          text: `Are you sure you want to remove "${participantName}" from the battle?`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, remove!",
          cancelButtonText: "Cancel",
          customClass: {
            confirmButton: "bg-red-600 hover:bg-red-700 text-white",
            cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
          },
        }).then((result) => {
          if (result.isConfirmed) {
            console.log("Confirmed delete, calling submitAjaxForm");

            // Use the global submitAjaxForm function
            if (window.AppUtils && window.AppUtils.submitAjaxForm) {
              window.AppUtils.submitAjaxForm("remove_participant", battleId, [
                participantId,
              ]);
            } else {
              // Fallback: direct form submission
              const form = document.getElementById("ajaxForm");
              if (form) {
                document.getElementById("ajaxAction").value =
                  "remove_participant";
                document.getElementById("ajaxBattleId").value = battleId;
                document.getElementById("ajaxParticipantIds").value =
                  participantId;
                document.getElementById("ajaxAmount").value = "";
                form.submit();
              } else {
                console.error("Ajax form not found");
              }
            }
          }
        });
      }
    });
  }
}

// Function to update battle info in UI without page reload
function updateBattleInfoInUI(battleId, newName, newDescription) {
  // Update battle list if we're on the battle list page
  const battleCards = document.querySelectorAll(".battle-card");
  battleCards.forEach((card) => {
    const editBtn = card.querySelector(
      '.edit-battle-btn[data-battle-id="' + battleId + '"]'
    );
    if (editBtn) {
      // This is the card for our battle
      const nameElement = card.querySelector("h3");
      if (nameElement) {
        nameElement.textContent = newName;
      }

      // Update or add description
      const descElement = card.querySelector(
        "p.text-sm.text-muted-foreground.line-clamp-2"
      );
      const cardContent = card.querySelector(".space-y-2");

      if (newDescription) {
        if (descElement) {
          // Update existing description
          descElement.textContent = newDescription;
        } else {
          // Add new description
          const newDescElement = document.createElement("p");
          newDescElement.className =
            "text-sm text-muted-foreground line-clamp-2";
          newDescElement.textContent = newDescription;

          // Insert after the h3 element
          const h3Element = cardContent.querySelector("h3");
          if (h3Element) {
            h3Element.insertAdjacentElement("afterend", newDescElement);
          }
        }
      } else if (descElement) {
        // Remove description if empty
        descElement.remove();
      }

      // Update edit button data attributes
      editBtn.setAttribute("data-battle-name", newName);
      editBtn.setAttribute("data-battle-description", newDescription);
    }
  });

  // Update battle detail page if we're on it
  const battleHeader = document.querySelector("h1.text-3xl");
  if (battleHeader && window.location.href.includes("battle=" + battleId)) {
    battleHeader.textContent = newName;

    // Update or add description in detail view
    const existingDesc = document.querySelector(
      "p.text-muted-foreground.mt-2.text-base"
    );
    const headerContent = battleHeader.closest(".flex-1");

    if (newDescription) {
      if (existingDesc) {
        // Update existing description
        existingDesc.textContent = newDescription;
      } else {
        // Add new description
        const newDescElement = document.createElement("p");
        newDescElement.className = "text-muted-foreground mt-2 text-base";
        newDescElement.textContent = newDescription;

        // Insert after the h1 and edit button container
        const titleContainer = headerContent.querySelector(
          ".flex.items-center.space-x-3"
        );
        if (titleContainer) {
          titleContainer.insertAdjacentElement("afterend", newDescElement);
        }
      }
    } else if (existingDesc) {
      // Remove description if empty
      existingDesc.remove();
    }

    // Update edit button data attributes
    const editBtn = document.querySelector(".edit-battle-btn");
    if (editBtn) {
      editBtn.setAttribute("data-battle-name", newName);
      editBtn.setAttribute("data-battle-description", newDescription);
    }
  }
}
