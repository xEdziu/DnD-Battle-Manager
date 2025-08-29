        <!-- Navigation -->
        <nav class="mb-4 flex space-x-4">
            <a href="index.php"
                class="px-3 py-1 rounded <?= (!isset($_GET['page']) && !isset($_GET['battle']) ? 'bg-blue-500 text-white' : 'bg-gray-200') ?>">Battles</a>
            <a href="index.php?page=presets"
                class="px-3 py-1 rounded <?= (isset($_GET['page']) && $_GET['page'] === 'presets' ? 'bg-blue-500 text-white' : 'bg-gray-200') ?>">Presets</a>
            <span class="flex-1"></span>
            <!-- Import/Export buttons -->
            <form method="post" enctype="multipart/form-data" class="inline">
                <label class="cursor-pointer bg-gray-200 px-3 py-1 rounded">
                    Import
                    <input type="file" name="import_file" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="index.php?action=export_json" class="px-3 py-1 rounded bg-gray-200">Export JSON</a>
        </nav>