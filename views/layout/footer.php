    </main>

    <!-- Footer -->
    <footer class="border-t border-border bg-background mt-auto">
        <div class="container mx-auto p-6">
            <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                <p class="text-sm text-muted-foreground">
                    © 2025 D&D Battle Manager. Built with
                    <span class="text-dnd-crimson">♥</span> for tabletop gamers. <br>
                    Coded with strong use of AI under the watchful eye of
                    <a href="https://github.com/xEdziu" target="_blank" rel="noopener noreferrer"
                        class="font-medium text-dnd-crimson hover:underline">
                        Adrian Goral
                    </a>
                </p>

                <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                    <i data-lucide="dice-6" class="w-4 h-4"></i>
                    <span>Roll for initiative!</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Re-initialize Lucide icons after any DOM changes
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

    <?php if (isset($_GET['saved'])): ?>
        <script>
            Swal.fire({
                title: 'Saved!',
                text: 'Battle changes have been saved.',
                icon: 'success',
                customClass: {
                    confirmButton: 'bg-dnd-emerald hover:bg-green-600 text-white'
                }
            });
        </script>
    <?php endif; ?>

    <?php if (getSuccess()): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: '<?= h(getSuccess()) ?>',
                icon: 'success',
                customClass: {
                    confirmButton: 'bg-dnd-emerald hover:bg-green-600 text-white'
                }
            });
        </script>
    <?php endif; ?>

    <?php if (getError()): ?>
        <script>
            Swal.fire({
                title: 'Error!',
                text: '<?= h(getError()) ?>',
                icon: 'error',
                customClass: {
                    confirmButton: 'bg-dnd-crimson hover:bg-red-600 text-white'
                }
            });
        </script>
    <?php endif; ?>
    </body>

    </html>