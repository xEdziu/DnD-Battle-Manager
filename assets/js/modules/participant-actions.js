// Participant Actions Module
// Handles delete confirmation for individual participants

export class ParticipantActions {
  constructor() {
    this.init();
  }

  init() {
    document.querySelectorAll(".delete-participant-btn").forEach((button) => {
      button.addEventListener("click", (e) => this.handleDeleteClick(e));
    });
  }

  handleDeleteClick(e) {
    const participantId = e.currentTarget.dataset.participantId;
    const participantName = e.currentTarget.dataset.participantName;
    const battleId = e.currentTarget.dataset.battleId;

    this.showDeleteConfirmation(participantId, participantName, battleId);
  }

  showDeleteConfirmation(participantId, participantName, battleId) {
    Swal.fire({
      title: "Remove Participant?",
      text: `Are you sure you want to remove "${participantName}" from this battle?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, remove",
      cancelButtonText: "Cancel",
      customClass: {
        confirmButton: "bg-dnd-crimson hover:bg-red-600 text-white",
        cancelButton: "bg-muted hover:bg-muted/80 text-muted-foreground",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        this.submitDeleteForm(participantId, battleId);
      }
    });
  }

  submitDeleteForm(participantId, battleId) {
    const form = document.createElement("form");
    form.method = "POST";

    const actionInput = document.createElement("input");
    actionInput.type = "hidden";
    actionInput.name = "action";
    actionInput.value = "remove_participant";

    const battleIdInput = document.createElement("input");
    battleIdInput.type = "hidden";
    battleIdInput.name = "battle_id";
    battleIdInput.value = battleId;

    const participantIdInput = document.createElement("input");
    participantIdInput.type = "hidden";
    participantIdInput.name = "participant_id";
    participantIdInput.value = participantId;

    form.appendChild(actionInput);
    form.appendChild(battleIdInput);
    form.appendChild(participantIdInput);
    document.body.appendChild(form);

    form.submit();
  }
}
