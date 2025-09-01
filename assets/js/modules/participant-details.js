// Participant Details Modal Module
// Handles clicking on participant rows to show details

export class ParticipantDetails {
  constructor() {
    this.init();
  }

  init() {
    document.querySelectorAll(".participant-row").forEach((row) => {
      row.addEventListener("click", (e) => this.handleRowClick(e));
    });
  }

  handleRowClick(e) {
    // Don't show modal if clicking on interactive elements
    if (e.target.closest("input, button, .delete-participant-btn")) {
      return;
    }

    const participantData = this.extractParticipantData(e.currentTarget);
    this.showParticipantModal(participantData);
  }

  extractParticipantData(row) {
    return {
      id: row.dataset.participantId,
      name: row.dataset.participantName,
      skills: row.dataset.participantSkills,
      actions: row.dataset.participantActions,
      notes: row.dataset.participantNotes,
      ac: row.dataset.participantAc,
      hpCurrent: row.dataset.participantHpCurrent,
      hpMax: row.dataset.participantHpMax,
      str: row.dataset.participantStr,
      dex: row.dataset.participantDex,
      con: row.dataset.participantCon,
      int: row.dataset.participantInt,
      wis: row.dataset.participantWis,
      cha: row.dataset.participantCha,
      passive: row.dataset.participantPassive,
      initiative: row.dataset.participantInitiative,
      type: row.dataset.participantType,
    };
  }

  showParticipantModal(participant) {
    // Character type configuration
    const typeConfig = {
      pc: { icon: "user-check", color: "text-emerald-600", name: "PC" },
      npc: { icon: "user", color: "text-blue-600", name: "NPC" },
      enemy: { icon: "skull", color: "text-red-600", name: "Enemy" },
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

    const modalContent = this.buildModalContent(
      participant,
      config,
      calculateModifier,
      formatModifier,
      hpColor
    );

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

  buildModalContent(
    participant,
    config,
    calculateModifier,
    formatModifier,
    hpColor
  ) {
    return `
            <div class="text-left space-y-4">
                <!-- Basic Stats -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">AC:</span>
                            <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${
                              participant.ac
                            }</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">HP:</span>
                            <span class="${hpColor} font-semibold">${
      participant.hpCurrent
    }/${participant.hpMax}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Passive Perception:</span>
                            <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${
                              participant.passive
                            }</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Initiative:</span>
                            <span class="bg-gray-100 px-2 py-1 rounded text-sm font-semibold">${
                              participant.initiative || 0
                            }</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-gray-600">Ability Scores</div>
                        <div class="grid grid-cols-3 gap-1 text-xs">
                            ${this.buildAbilityScores(
                              participant,
                              calculateModifier,
                              formatModifier
                            )}
                        </div>
                    </div>
                </div>

                ${this.buildDetailsSection(participant)}
                
                ${this.buildEmptyState(participant)}
            </div>
        `;
  }

  buildAbilityScores(participant, calculateModifier, formatModifier) {
    const abilities = [
      { name: "STR", value: participant.str },
      { name: "DEX", value: participant.dex },
      { name: "CON", value: participant.con },
      { name: "INT", value: participant.int },
      { name: "WIS", value: participant.wis },
      { name: "CHA", value: participant.cha },
    ];

    return abilities
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
  }

  buildDetailsSection(participant) {
    let sections = "";

    if (participant.skills) {
      sections += `
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
      sections += `
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
      sections += `
                <div>
                    <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                        <i data-lucide="file-text" class="h-4 w-4 mr-1"></i>
                        Notes
                    </h4>
                    <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">${participant.notes}</div>
                </div>
            `;
    }

    return sections;
  }

  buildEmptyState(participant) {
    if (!participant.skills && !participant.actions && !participant.notes) {
      return `
                <div class="text-center text-gray-500 py-4">
                    <i data-lucide="info" class="h-8 w-8 mx-auto mb-2 opacity-50"></i>
                    <p>No additional details available for this participant.</p>
                </div>
            `;
    }
    return "";
  }
}
