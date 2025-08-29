<?php
// Battle Actions Component
// Bulk action buttons for selected participants

function renderBattleActions()
{
?>
    <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6 pb-4">
            <h3 class="text-lg font-semibold leading-none tracking-tight flex items-center">
                <i data-lucide="zap" class="mr-2 h-5 w-5 text-dnd-gold"></i>
                Battle Actions
            </h3>
            <p class="text-sm text-muted-foreground">
                Select participants to perform actions
            </p>
        </div>
        <div class="p-6 pt-0">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Damage Action -->
                <button id="damageBtn"
                    class="group relative inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-12 px-4 py-2"
                    disabled>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="sword" class="h-5 w-5"></i>
                        <div class="text-left">
                            <div class="font-medium">Damage</div>
                            <div class="text-xs opacity-75">
                                <span id="damageCount">0</span> selected
                            </div>
                        </div>
                    </div>
                    <!-- Disabled overlay -->
                    <div
                        class="absolute inset-0 bg-muted/80 rounded-md flex items-center justify-center group-enabled:hidden">
                        <span class="text-xs text-muted-foreground">Select participants</span>
                    </div>
                </button>

                <!-- Heal Action -->
                <button id="healBtn"
                    class="group relative inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-dnd-emerald text-white hover:bg-green-600 h-12 px-4 py-2"
                    disabled>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="heart" class="h-5 w-5"></i>
                        <div class="text-left">
                            <div class="font-medium">Heal</div>
                            <div class="text-xs opacity-75">
                                <span id="healCount">0</span> selected
                            </div>
                        </div>
                    </div>
                    <!-- Disabled overlay -->
                    <div
                        class="absolute inset-0 bg-muted/80 rounded-md flex items-center justify-center group-enabled:hidden">
                        <span class="text-xs text-muted-foreground">Select participants</span>
                    </div>
                </button>

                <!-- Remove Action -->
                <button id="removeBtn"
                    class="group relative inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-12 px-4 py-2"
                    disabled>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="user-minus" class="h-5 w-5"></i>
                        <div class="text-left">
                            <div class="font-medium">Remove</div>
                            <div class="text-xs opacity-75">
                                <span id="removeCount">0</span> selected
                            </div>
                        </div>
                    </div>
                    <!-- Disabled overlay -->
                    <div
                        class="absolute inset-0 bg-muted/80 rounded-md flex items-center justify-center group-enabled:hidden">
                        <span class="text-xs text-muted-foreground">Select participants</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
<?php
}
?>