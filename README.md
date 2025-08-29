# 🎲 D&D Battle Manager

A comprehensive web-based battle management system for Dungeons & Dragons campaigns. Manage character presets, organize battles, track initiative, handle HP changes, and more - all through an intuitive web interface.

![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)
![SQLite](https://img.shields.io/badge/SQLite-3-green.svg)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

## ✨ What Makes This Special?

Perfect for Dungeon Masters who want to focus on storytelling instead of spreadsheets. This battle manager handles all the number crunching, initiative tracking, and character management so you can focus on creating memorable adventures.

### 🎯 Key Features

- **🎭 Character Preset Management** - Create detailed character templates with full D&D 5e stats
- **⚔️ Battle Organization** - Organize encounters with descriptions and visual badges
- **🎲 Initiative System** - Automatic sorting with manual reordering when needed
- **❤️ HP Management** - Group damage/healing with real-time health indicators
- **💾 Data Persistence** - SQLite database with JSON export/import for backups
- **🎨 Modern Interface** - Clean, responsive design that works on any device
- **⚡ Real-time Updates** - No page reloads, everything updates instantly

---

## 🚀 Getting Started (Easy Setup)

**Want to start using this right now? Follow these simple steps:**

### 🐳 Docker (Recommended for Most Users)

The easiest way to get started! Works on Windows, Mac, and Linux.

1. **Install Docker**

   - Download from [docker.com](https://www.docker.com/get-started)
   - Follow the installation wizard for your operating system

2. **Download the Project**

   - Click the green "Code" button → "Download ZIP"
   - Extract the ZIP file to a folder (e.g., `C:\dnd-battle-manager` or `~/dnd-battle-manager`)

3. **Start the Application**

   - Open Command Prompt (Windows) or Terminal (Mac/Linux)
   - Navigate to your extracted folder:
     ```bash
     cd C:\dnd-battle-manager        # Windows
     cd ~/dnd-battle-manager         # Mac/Linux
     ```
   - Run this single command:
     ```bash
     docker-compose up -d
     ```

4. **Start Playing**

   - Open your browser to `http://localhost:8080`
   - That's it! The application is ready to use

5. **When You're Done**
   ```bash
   # Stop the application
   docker-compose down
   ```

### 📱 Quick Usage Guide

1. **Create Characters**: Go to "Presets" → "Add New Preset" → Fill in your character details
2. **Start a Battle**: Click "New Battle" → Give it a name and description
3. **Add Participants**: Choose from your presets or create new characters on the fly
4. **Manage Combat**: Track initiative, apply damage/healing, manage turn order
5. **Save Your Work**: Everything is automatically saved, export to JSON for backups

---

## 🎮 Features in Detail

### Character Management

- **Full D&D 5e Support**: All six ability scores, AC, HP, passive perception
- **Skills & Actions**: Custom text fields for abilities, spells, and special actions
- **Character Types**: Distinguish between PCs, NPCs, and enemies
- **Quick Creation**: Save time with preset templates

### Battle Organization

- **Visual Badges**: Color-coded labels (Active Battle, Boss Fight, Random Encounter, etc.)
- **Detailed Descriptions**: Add context and notes to each encounter
- **Easy Navigation**: Clean interface to switch between battles and manage multiple encounters

### Combat Features

- **Initiative Tracking**: Automatic sorting with drag-and-drop reordering
- **HP Management**: Quick damage/heal buttons with group operations
- **Real-time Updates**: See changes instantly without page refreshes
- **Turn Management**: Clear visual indicators for current turn and character status

---

## 📊 Perfect For

- **Dungeon Masters** running D&D 5e campaigns
- **Players** who want to organize their character sheets
- **Groups** playing online or in-person
- **Anyone** who loves organized, efficient gameplay

---

## 🤝 Contributing

Found a bug or have a great idea? Contributions are welcome!

1. Fork the repository on GitHub
2. Create your feature branch
3. Make your changes
4. Submit a pull request

Report issues on the [GitHub Issues page](https://github.com/xEdziu/ttrpg-battle-manager/issues).

---

## 📝 License

This project is licensed under the MIT License - free to use, modify, and distribute.

---

## 🛠️ Local Setup (For Developers)

> [!NOTE]
> Whole this repository was created in a one night stand (as of 28.07.2025) - I was using a hella lot of AI help to develop this project. It is not a secret that AI played a significant role in its development.

**For developers who want to contribute, modify the code, or build from source:**

### Manual Installation Prerequisites

- **PHP 8.1 or higher** with the following extensions:
  - `pdo_sqlite` (for database operations)
  - `sqlite3` (for SQLite support)
- **Web browser** with JavaScript ES6+ support
- **Git** (for cloning the repository)

### Manual Installation Steps

1. **Clone the Repository**

   ```bash
   git clone https://github.com/xEdziu/DnD-Battle-Manager.git
   cd DnD-Battle-Manager
   ```

2. **Install PHP** (if not already installed)
   - **Windows**: Download from [windows.php.net](https://windows.php.net/download/) → Choose "Thread Safe" ZIP
   - **Mac**: Install via Homebrew: `brew install php`
   - **Linux**: Install via package manager: `sudo apt install php php-sqlite3` (Ubuntu/Debian)

> [!IMPORTANT]
> Check if you have sqlite extensions enabled!

3. **Verify PHP Installation**

   ```bash
   php --version
   ```

4. **Set Up the Database**

   ```bash
   # The SQLite database will be created automatically on first run
   # Ensure the application has write permissions in the project directory
   chmod 755 .
   chmod 777 data/  # If the data directory exists
   ```

5. **Start Development Server**

   ```bash
   php -S localhost:8000
   ```

6. **Access the Application**
   - Open your browser to `http://localhost:8000`
   - The database (`data.sqlite`) will be created automatically
   - Default presets will be initialized on first visit

### Alternative Docker Setup for Development

```bash
# Build and run for development
docker build -t dnd-battle-manager .
docker run -p 8080:80 -v $(pwd):/var/www/html dnd-battle-manager

# Or use docker-compose for development
docker-compose up -d --build
```

### Project Structure

```
├── index.php                   # Main entry point and router
├── dockerfile                  # Docker configuration
├── docker-compose.yml          # Docker Compose setup
├── .gitignore                  # Git ignore file
├── .dockerignore              # Docker ignore file
├── config/
│   └── config.php             # Application configuration
├── includes/
│   ├── database.php           # SQLite database management
│   ├── utils.php              # Helper functions and default presets
│   └── BadgeManager.php       # Battle badge management
├── classes/
│   ├── PresetManager.php      # Character preset operations
│   ├── BattleManager.php      # Battle management logic
│   └── ParticipantManager.php # Battle participant handling
├── handlers/
│   ├── preset_handler.php     # Preset action handling
│   ├── battle_handler.php     # Battle action handling
│   └── import_export_handler.php # Data import/export
├── api/
│   └── battle_api.php         # API endpoints for battle operations
├── views/
│   ├── layout/                # Header, footer, navigation templates
│   ├── battles/               # Battle-related views and components
│   ├── presets/               # Preset management views
│   └── badges/                # Badge management views
├── assets/
│   └── js/
│       ├── app.js             # Main frontend JavaScript
│       ├── battle-detail.js   # Battle detail page scripts
│       └── modules/           # Modular JavaScript components
├── data/
│   └── .gitkeep              # Keep data directory in git
├── data.sqlite               # SQLite database (auto-created)
└── data.json                 # JSON export/import file (auto-created)
```

---

## 📊 Database Schema

The application uses SQLite with the following main tables:

- **presets**: Character templates with stats and abilities
- **battles**: Battle information with names and descriptions
- **participants**: Characters participating in specific battles
- **badges**: Visual labels for organizing battles

---

## 🔧 API Endpoints

The application handles these main actions:

### Battle Management

- `POST /?action=create_battle` - Create new battle
- `POST /?action=update_battle_info` - Update battle name/description
- `POST /?action=update_battle_badge` - Update battle badge
- `POST /?action=delete_battle` - Remove battle
- `POST /?action=add_participant` - Add character to battle
- `POST /?action=remove_participant` - Remove character from battle

### Character Management

- `POST /?action=add_preset` - Create character preset
- `POST /?action=edit_preset` - Update character preset
- `POST /?action=delete_preset` - Remove character preset

### Battle Operations

- `POST /?action=damage` - Apply damage to participants
- `POST /?action=heal` - Heal participants
- `POST /?action=update_battle` - Update initiative/names (legacy method)

---

## 🎮 Usage Guide

### Creating Characters

1. Go to the "Presets" tab
2. Click "Add New Preset"
3. Fill in character details (name, stats, abilities)
4. Choose character type (PC/NPC/Enemy)
5. Save the preset

### Managing Battles

1. Create a new battle with a descriptive name
2. Add participants from your presets
3. Roll or set initiative values
4. Use the battle interface to:
   - Track HP changes
   - Manage turn order
   - Remove defeated characters

### Data Backup

- Use the "Export" button to download your data as JSON
- Use the "Import" button to restore from a backup file

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🐛 Troubleshooting

### Common Issues

**🐳 Docker Issues**

- Make sure Docker Desktop is running
- Check if port 8080 is already in use: `docker ps` or try `http://localhost:8081`
- Restart Docker: `docker-compose down && docker-compose up -d`

**💾 Database Connection Errors**

- Ensure PHP has SQLite support
- Check file permissions on the project directory
- Verify the application directory is writable

**🌐 JavaScript Errors**

- Check browser console (F12) for specific error messages
- Ensure you're using a modern browser (Chrome 60+, Firefox 55+, Safari 12+)

**🚫 Permission Denied**

- On Unix systems: `chmod 755 .` and `chmod 777 data/`
- On Windows: Ensure the folder isn't in a restricted location

**🔌 Port Already in Use**

- For PHP: Change the port: `php -S localhost:8001` (or any other port)
- For Docker: Edit `docker-compose.yml` and change `8080:80` to `8081:80`

### Getting Help

- Check the [Issues page](https://github.com/xEdziu/ttrpg-battle-manager/issues) for known problems
- Create a new issue with detailed error information
- Include your PHP version, operating system, and browser details

---

**Built with ❤️ for tabletop gaming enthusiasts by [xEdziu](https://github.com/xEdziu) Assisted by Various AI tools**
