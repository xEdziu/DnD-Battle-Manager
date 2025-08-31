// Battle Detail - Main JavaScript Entry Point
// Modular version of the battle detail functionality

import { PresetFilter } from "./modules/preset-filter.js";
import { InitiativeManager } from "./modules/initiative.js";
// import { ParticipantDetails } from "./modules/participant-details.js"; // Disabled - using inline expandable rows
import { ParticipantActions } from "./modules/participant-actions.js";

class BattleDetailApp {
  constructor() {
    this.modules = {};
    this.init();
  }

  init() {
    // Wait for DOM to be ready
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () =>
        this.initializeModules()
      );
    } else {
      this.initializeModules();
    }
  }

  initializeModules() {
    try {
      // Initialize UI modules
      this.modules.presetFilter = new PresetFilter();
      this.modules.initiative = new InitiativeManager();
      // this.modules.participantDetails = new ParticipantDetails(); // Disabled - using inline expandable rows
      this.modules.participantActions = new ParticipantActions();

      // Initialize Lucide icons
      this.initializeLucideIcons();

      console.log("Battle Detail modules initialized successfully");
    } catch (error) {
      console.error("Error initializing Battle Detail modules:", error);
    }
  }

  initializeLucideIcons() {
    // Ensure Lucide icons are properly initialized
    setTimeout(() => {
      if (window.lucide) {
        window.lucide.createIcons();
      }
    }, 100);
  }

  // Public method to reinitialize modules after dynamic content changes
  reinitialize() {
    this.initializeModules();
  }
}

// Auto-initialize when script loads
const battleDetailApp = new BattleDetailApp();

// Export for global access if needed
window.BattleDetailApp = battleDetailApp;
