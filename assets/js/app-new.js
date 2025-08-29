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
  });

  // Global battle deletion with SweetAlert2
  document.querySelectorAll(".delete-battle-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const battleName = this.dataset.battleName;
      const url = this.dataset.deleteUrl;

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
          window.location.href = url;
        }
      });
    });
  });

  // Global new battle creation with SweetAlert2
  const newBattleButtons = document.querySelectorAll(
    "#newBattleBtn, #newBattleBtn2"
  );
  newBattleButtons.forEach((button) => {
    if (button) {
      button.addEventListener("click", handleNewBattle);
    }
  });
}

function handleNewBattle() {
  Swal.fire({
    title: "Create New Battle",
    input: "text",
    inputLabel: "Battle Name",
    inputPlaceholder: "Enter battle name...",
    showCancelButton: true,
    confirmButtonText: "Create Battle",
    cancelButtonText: "Cancel",
    inputValidator: (value) => {
      if (!value || value.trim() === "") {
        return "Please enter a battle name!";
      }
    },
    customClass: {
      confirmButton: "bg-dnd-emerald hover:bg-green-600 text-white",
      cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = "actions/battle_action.php";

      const actionInput = document.createElement("input");
      actionInput.type = "hidden";
      actionInput.name = "action";
      actionInput.value = "create";

      const nameInput = document.createElement("input");
      nameInput.type = "hidden";
      nameInput.name = "name";
      nameInput.value = result.value.trim();

      form.appendChild(actionInput);
      form.appendChild(nameInput);
      document.body.appendChild(form);
      form.submit();
    }
  });
}

// Export for use in other modules
window.AppUtils = {
  h,
  submitAjaxForm,
  initializeLucide,
  handleNewBattle,
};
