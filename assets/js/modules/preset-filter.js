// Preset Filter Module
// Handles filtering of presets by character type

export class PresetFilter {
  constructor() {
    this.filterButtons = document.querySelectorAll(".preset-type-filter");
    this.presetSelect = document.getElementById("preset-select");
    this.presetOptions = Array.from(
      this.presetSelect?.querySelectorAll("option") || []
    );

    this.init();
  }

  init() {
    if (!this.presetSelect || this.filterButtons.length === 0) return;

    this.filterButtons.forEach((button) => {
      button.addEventListener("click", (e) => this.handleFilterClick(e));
    });
  }

  handleFilterClick(e) {
    const filterType = e.target.getAttribute("data-type");

    // Update button states
    this.updateButtonStates(e.target);

    // Filter preset options
    this.filterOptions(filterType);
  }

  updateButtonStates(activeButton) {
    this.filterButtons.forEach((btn) => {
      btn.classList.remove(
        "active",
        "bg-background",
        "text-foreground",
        "shadow-sm"
      );
      btn.classList.add("hover:bg-background/80");
    });

    activeButton.classList.add(
      "active",
      "bg-background",
      "text-foreground",
      "shadow-sm"
    );
    activeButton.classList.remove("hover:bg-background/80");
  }

  filterOptions(filterType) {
    // Preserve default option
    const defaultOption = this.presetSelect.querySelector('option[value=""]');
    this.presetSelect.innerHTML = "";

    if (defaultOption) {
      this.presetSelect.appendChild(defaultOption);
    }

    // Filter and add options
    this.presetOptions.forEach((option) => {
      if (option.value === "") return; // Skip default option

      const optionType = option.getAttribute("data-character-type");

      if (filterType === "all" || optionType === filterType) {
        this.presetSelect.appendChild(option.cloneNode(true));
      }
    });
  }
}
