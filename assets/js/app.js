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

// Function to initialize participant modal functionality
function initializeParticipantModal() {
  document.querySelectorAll(".participant-row").forEach((row) => {
    if (!row.hasAttribute("data-participant-listener-added")) {
      row.setAttribute("data-participant-listener-added", "true");
      row.addEventListener("click", function (e) {
        // Don't show modal if clicking on interactive elements
        if (e.target.closest("input, button, .delete-participant-btn")) {
          return;
        }

        const participantData = {
          id: this.dataset.participantId,
          name: this.dataset.participantName,
          skills: this.dataset.participantSkills || "",
          actions: this.dataset.participantActions || "",
          notes: this.dataset.participantNotes || "",
          ac: this.dataset.participantAc,
          hpCurrent: this.dataset.participantHpCurrent,
          hpMax: this.dataset.participantHpMax,
          str: this.dataset.participantStr,
          dex: this.dataset.participantDex,
          con: this.dataset.participantCon,
          int: this.dataset.participantInt,
          wis: this.dataset.participantWis,
          cha: this.dataset.participantCha,
          passive: this.dataset.participantPassive,
          type: this.dataset.participantType,
        };

        console.log("Participant clicked:", participantData);
        showParticipantDetails(participantData);
      });
    }
  });
}

// Function to show participant details modal
function showParticipantDetails(participant) {
  console.log("showParticipantDetails called with:", participant);

  // Character type configuration
  const typeConfig = {
    pc: {
      icon: "user-check",
      color: "text-emerald-600",
      name: "PC",
    },
    npc: {
      icon: "user",
      color: "text-blue-600",
      name: "NPC",
    },
    enemy: {
      icon: "skull",
      color: "text-red-600",
      name: "Enemy",
    },
  };
  const config = typeConfig[participant.type] || typeConfig["enemy"];

  // Calculate ability modifiers
  const calculateModifier = (score) => Math.floor((score - 10) / 2);
  const formatModifier = (modifier) =>
    modifier >= 0 ? `+${modifier}` : `${modifier}`;

  // HP percentage for color coding
  const hpPercentage =
    participant.hpMax > 0
      ? (participant.hpCurrent / participant.hpMax) * 100
      : 0;
  const hpColor =
    hpPercentage > 75
      ? "text-emerald-600"
      : hpPercentage > 25
      ? "text-yellow-600"
      : "text-red-600";

  // Build ability scores section
  const abilities = [
    {
      name: "STR",
      value: participant.str,
    },
    {
      name: "DEX",
      value: participant.dex,
    },
    {
      name: "CON",
      value: participant.con,
    },
    {
      name: "INT",
      value: participant.int,
    },
    {
      name: "WIS",
      value: participant.wis,
    },
    {
      name: "CHA",
      value: participant.cha,
    },
  ];

  const abilityScoresHTML = abilities
    .map(
      (ability) => `
    <div class="text-center">
      <div class="font-medium">${ability.name}</div>
      <div>${ability.value}</div>
      <div class="text-gray-500">(${formatModifier(
        calculateModifier(ability.value)
      )})</div>
    </div>
  `
    )
    .join("");

  // Build details sections
  let detailsSections = "";

  if (participant.skills) {
    detailsSections += `
      <div>
        <h4 class="font-medium text-gray-700 mb-2 flex items-center">
          <i data-lucide="zap" class="h-4 w-4 mr-1"></i>
          Skills
        </h4>
        <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.skills}</div>
      </div>
    `;
  }

  if (participant.actions) {
    detailsSections += `
      <div>
        <h4 class="font-medium text-gray-700 mb-2 flex items-center">
          <i data-lucide="sword" class="h-4 w-4 mr-1"></i>
          Actions
        </h4>
        <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.actions}</div>
      </div>
    `;
  }

  if (participant.notes) {
    detailsSections += `
      <div>
        <h4 class="font-medium text-gray-700 mb-2 flex items-center">
          <i data-lucide="file-text" class="h-4 w-4 mr-1"></i>
          Notes
        </h4>
        <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.notes}</div>
      </div>
    `;
  }

  if (!participant.skills && !participant.actions && !participant.notes) {
    detailsSections = `
      <div class="text-center text-gray-500 py-4">
        <i data-lucide="info" class="h-8 w-8 mx-auto mb-2 opacity-50"></i>
        <p>No additional details available for this participant.</p>
      </div>
    `;
  }

  const modalContent = `
    <div class="text-left space-y-4">
      <!-- Basic Stats -->
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="font-medium">AC:</span>
            <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${participant.ac}</span>
          </div>
          <div class="flex justify-between">
            <span class="font-medium">HP:</span>
            <span class="${hpColor} font-semibold">${participant.hpCurrent}/${participant.hpMax}</span>
          </div>
          <div class="flex justify-between">
            <span class="font-medium">Passive Perception:</span>
            <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${participant.passive}</span>
          </div>
        </div>
        <div class="space-y-1">
          <div class="text-sm font-medium text-gray-600">Ability Scores</div>
          <div class="grid grid-cols-3 gap-1 text-xs">
            ${abilityScoresHTML}
          </div>
        </div>
      </div>

      ${detailsSections}
    </div>
  `;

  Swal.fire({
    title: `
      <div class="flex items-center justify-center space-x-2">
        <i data-lucide="${config.icon}" class="h-5 w-5 ${config.color}"></i>
        <span>${participant.name}</span>
        <span class="text-sm ${config.color} bg-gray-100 px-2 py-1 rounded">${config.name}</span>
      </div>
    `,
    html: modalContent,
    width: 600,
    showConfirmButton: true,
    confirmButtonText: "Close",
    customClass: {
      confirmButton: "bg-blue-600 hover:bg-blue-700 text-white",
      htmlContainer: "text-left",
    },
    didOpen: () => {
      // Re-render lucide icons in the modal
      setTimeout(() => {
        if (window.lucide) {
          window.lucide.createIcons();
        }
      }, 100);
    },
  });
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
