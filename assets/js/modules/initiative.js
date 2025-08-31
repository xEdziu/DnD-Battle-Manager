// Initiative Module
// Handles initiative rolling and sorting

export class InitiativeManager {
  constructor() {
    this.rollButton = document.getElementById("rollInitiative");
    this.sortButton = document.getElementById("sortByInit");

    this.init();
  }

  init() {
    if (
      this.rollButton &&
      !this.rollButton.hasAttribute("data-listener-added")
    ) {
      this.rollButton.setAttribute("data-listener-added", "true");
      this.rollButton.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.rollInitiative();
      });
    }

    if (
      this.sortButton &&
      !this.sortButton.hasAttribute("data-listener-added")
    ) {
      this.sortButton.setAttribute("data-listener-added", "true");
      this.sortButton.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.sortByInitiative();
      });
    }

    // Auto-save initiative changes
    this.initAutoSave();
  }

  initAutoSave() {
    // Auto-save initiative changes
    document.querySelectorAll(".initiative-input").forEach((input) => {
      input.addEventListener("change", async (e) => {
        const participantId = e.target.name.match(/\[(\d+)\]/)[1];
        const battleId = document.querySelector(
          'input[name="battle_id"]'
        ).value;
        const initiative = e.target.value;

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
            console.error("Failed to save initiative:", result.error);
          }
        } catch (error) {
          console.error("Error saving initiative:", error);
        }
      });
    });

    // Auto-save name changes with debouncing
    document.querySelectorAll('input[name^="name["]').forEach((input) => {
      let timeoutId;

      input.addEventListener("input", (e) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(async () => {
          const participantId = e.target.name.match(/\[(\d+)\]/)[1];
          const battleId = document.querySelector(
            'input[name="battle_id"]'
          ).value;
          const name = e.target.value;

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
              console.error("Failed to save name:", result.error);
            }
          } catch (error) {
            console.error("Error saving name:", error);
          }
        }, 1000); // 1 second debounce
      });
    });
  }

  async rollInitiative() {
    console.log("Roll Initiative clicked - starting AJAX version");
    const participantRows = document.querySelectorAll(".participant-row");
    let updatedCount = 0;
    const updatePromises = [];

    participantRows.forEach((row) => {
      const participantType = row.dataset.participantType;

      // Only roll for NPCs and Enemies, skip PCs
      if (participantType === "npc" || participantType === "enemy") {
        const dexScore = parseInt(row.dataset.participantDex) || 10;
        const dexModifier = Math.floor((dexScore - 10) / 2);

        // Roll 1d20 + DEX modifier
        const roll = Math.floor(Math.random() * 20) + 1;
        const initiative = roll + dexModifier;

        // Update the initiative input
        const initiativeInput = row.querySelector(".initiative-input");
        if (initiativeInput) {
          initiativeInput.value = initiative;
          updatedCount++;

          // Get participant ID and battle ID for AJAX save
          const participantId = initiativeInput.name.match(/\[(\d+)\]/)[1];
          const battleId = document.querySelector(
            'input[name="battle_id"]'
          ).value;

          // Auto-save via AJAX
          const updatePromise = fetch("api/battle_api.php", {
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

          updatePromises.push(updatePromise);
        }
      }
    });

    try {
      // Wait for all AJAX requests to complete
      await Promise.all(updatePromises);

      // Auto-sort table after rolling initiative
      if (updatedCount > 0) {
        this.isAutoSort = true;
        this.sortByInitiative();
        this.isAutoSort = false;
      }

      this.showRollResult(updatedCount);
    } catch (error) {
      console.error("Error saving initiative:", error);
      this.showRollError(updatedCount);
    }
  }

  sortByInitiative() {
    const tbody = document.querySelector("#participants-table tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    // Sort rows by initiative value (descending - highest first)
    rows.sort((a, b) => {
      const aInit = parseInt(a.querySelector(".initiative-input").value) || 0;
      const bInit = parseInt(b.querySelector(".initiative-input").value) || 0;
      return bInit - aInit; // Descending order
    });

    // Clear tbody and append sorted rows
    tbody.innerHTML = "";
    rows.forEach((row) => tbody.appendChild(row));

    // Re-render Lucide icons
    if (window.lucide) {
      window.lucide.createIcons();
    }

    // Only show sort message when manually clicked, not when auto-sorting after roll
    if (!this.isAutoSort) {
      this.showSortResult();
    }
  }

  showRollResult(updatedCount) {
    if (updatedCount > 0) {
      Swal.fire({
        title: `Initiative rolled for ${updatedCount} NPCs and Enemies!`,
        icon: "success",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        showCancelButton: false,
        timer: 3000,
        timerProgressBar: true,
        backdrop: false,
        allowOutsideClick: true,
        allowEscapeKey: true,
        customClass: {
          popup: "colored-toast",
        },
      });
    } else {
      Swal.fire({
        title: "No NPCs or Enemies found to roll initiative for",
        icon: "info",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        showCancelButton: false,
        timer: 3000,
        timerProgressBar: true,
        backdrop: false,
        allowOutsideClick: true,
        allowEscapeKey: true,
        customClass: {
          popup: "colored-toast",
        },
      });
    }
  }

  showRollError(updatedCount) {
    Swal.fire({
      title: `Initiative rolled for ${updatedCount} participants, but there was an error saving`,
      icon: "warning",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      customClass: {
        popup: "colored-toast",
      },
    });
  }

  showSortResult() {
    Swal.fire({
      title: "Participants sorted by initiative (highest first)",
      icon: "success",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      showCancelButton: false,
      timer: 2000,
      timerProgressBar: true,
      backdrop: false,
      allowOutsideClick: true,
      allowEscapeKey: true,
      customClass: {
        popup: "colored-toast",
      },
    });
  }
}
