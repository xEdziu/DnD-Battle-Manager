<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Battle Manager</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // D&D Inspired Color Palette
                        'dnd': {
                            'crimson': '#8B0000',
                            'gold': '#DAA520',
                            'emerald': '#228B22',
                            'royal': '#4169E1',
                            'shadow': '#2F2F2F',
                            'parchment': '#F5F5DC',
                            'stone': '#696969'
                        },
                        // Shadcn-ui inspired colors
                        'border': 'hsl(214.3 31.8% 91.4%)',
                        'input': 'hsl(214.3 31.8% 91.4%)',
                        'ring': 'hsl(222.2 84% 4.9%)',
                        'background': 'hsl(0 0% 100%)',
                        'foreground': 'hsl(222.2 84% 4.9%)',
                        'primary': {
                            DEFAULT: 'hsl(222.2 47.4% 11.2%)',
                            foreground: 'hsl(210 40% 98%)'
                        },
                        'secondary': {
                            DEFAULT: 'hsl(210 40% 96%)',
                            foreground: 'hsl(222.2 84% 4.9%)'
                        },
                        'destructive': {
                            DEFAULT: 'hsl(0 84.2% 60.2%)',
                            foreground: 'hsl(210 40% 98%)'
                        },
                        'muted': {
                            DEFAULT: 'hsl(210 40% 96%)',
                            foreground: 'hsl(215.4 16.3% 46.9%)'
                        },
                        'accent': {
                            DEFAULT: 'hsl(210 40% 96%)',
                            foreground: 'hsl(222.2 84% 4.9%)'
                        },
                        'card': {
                            DEFAULT: 'hsl(0 0% 100%)',
                            foreground: 'hsl(222.2 84% 4.9%)'
                        }
                    },
                    borderRadius: {
                        'lg': '0.5rem',
                        'md': '0.375rem',
                        'sm': '0.25rem'
                    }
                }
            }
        }
    </script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: hsl(210 40% 96%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: hsl(215.4 16.3% 46.9%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: hsl(222.2 47.4% 11.2%);
        }

        /* Line clamp utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 text-foreground antialiased flex flex-col">
    <!-- Navigation Header -->
    <header
        class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="container mx-auto flex h-16 items-center px-4">
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-dnd-crimson to-dnd-gold rounded-lg flex items-center justify-center">
                    <i data-lucide="sword" class="w-5 h-5 text-white"></i>
                </div>
                <h1
                    class="text-xl font-bold bg-gradient-to-r from-dnd-crimson to-dnd-gold bg-clip-text text-transparent hidden sm:block">
                    D&D Battle Manager
                </h1>
                <h1
                    class="text-lg font-bold bg-gradient-to-r from-dnd-crimson to-dnd-gold bg-clip-text text-transparent sm:hidden">
                    D&D
                </h1>
            </div>
            <nav class="ml-auto flex items-center space-x-2 md:space-x-4">
                <a href="index.php"
                    class="text-sm font-medium transition-colors hover:text-primary <?= (!isset($_GET['page']) && !isset($_GET['battle']) ? 'text-primary font-semibold' : '') ?>">
                    Battles
                </a>
                <a href="index.php?page=presets"
                    class="text-sm font-medium transition-colors hover:text-primary <?= (isset($_GET['page']) && $_GET['page'] === 'presets' ? 'text-primary font-semibold' : '') ?>">
                    Presets
                </a>
                <a href="index.php?page=badges"
                    class="text-sm font-medium transition-colors hover:text-primary <?= (isset($_GET['page']) && $_GET['page'] === 'badges' ? 'text-primary font-semibold' : '') ?>">
                    Badges
                </a>

                <!-- Separator -->
                <div class="h-6 w-px bg-border hidden md:block"></div>

                <!-- Storage Type Indicator -->
                <div class="flex items-center space-x-1 md:space-x-2">
                    <div
                        class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-dnd-emerald/10 text-dnd-emerald">
                        <i data-lucide="database" class="h-3 w-3 md:mr-1"></i>
                        <span class="hidden md:inline">SQLite</span>
                    </div>
                </div> <!-- Separator -->
                <div class="h-6 w-px bg-border hidden lg:block"></div>

                <!-- Import/Export -->
                <div class="flex items-center space-x-1 md:space-x-2">
                    <form method="post" enctype="multipart/form-data" class="inline">
                        <label
                            class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-2 md:px-3 cursor-pointer"
                            title="Import JSON data">
                            <i data-lucide="upload" class="h-3 w-3 md:mr-1"></i>
                            <span class="hidden md:inline">Import</span>
                            <input type="file" name="import_file" class="hidden" accept=".json"
                                onchange="this.form.submit()">
                        </label>
                    </form>
                    <a href="index.php?action=export_json"
                        class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-2 md:px-3"
                        title="Export data as JSON">
                        <i data-lucide="download" class="h-3 w-3 md:mr-1"></i>
                        <span class="hidden md:inline">Export</span>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-6 space-y-8 flex-1">