# Weltspiegel Component

Joomla 5/6 component for Weltspiegel Cottbus cinema, integrating with the Cinetixx API to display movies and showtimes.

## Description

This component provides movie and showtime management by fetching data from the Cinetixx API. It includes models, views, and layouts for displaying cinema events on your Joomla site.

## Requirements

- Joomla 5.0 or higher (tested with Joomla 6)
- PHP 8.1 or higher
- Cinetixx API access (mandator ID required)

## Installation

### Manual Installation

1. Build the package:
   ```bash
   npm install
   npm run release
   ```

2. Upload the generated ZIP file via Joomla Administrator:
   - System → Extensions → Install
   - Upload `com_weltspiegel-*.zip`

3. Configure the component:
   - Components → Weltspiegel
   - Set your Mandator ID

### Via GitHub Releases

Download the latest release ZIP from the GitHub releases page and install via Joomla Administrator.

### Automatic Updates

Once installed, the component can be updated automatically through Joomla's update system:
- System → Update → Extensions
- Joomla checks the update server configured in the component manifest
- New versions are automatically detected and can be installed with one click

The update manifest is hosted at:
`https://raw.githubusercontent.com/weltspiegel-cottbus/com_weltspiegel/main/update-manifest.xml`

## Development

### Build Process

The component uses a simple packaging workflow (no asset minification needed):

```bash
# Install dependencies
npm install

# Create Joomla installation package
npm run package

# Build and package in one command
npm run release
```

**Note:** This component has no JavaScript or CSS assets, so `npm run build` is a no-op. If assets are added in the future, the build script can be expanded.

### Creating Releases

This project uses [changelogen](https://github.com/unjs/changelogen) for automated changelog generation and releases based on conventional commits.

#### Commit Message Format

Follow conventional commits format:

```
<type>: <description>

[optional body]
```

**Types:**
- `feat:` New feature (bumps minor version)
- `fix:` Bug fix (bumps patch version)
- `perf:` Performance improvement (bumps patch version)
- `refactor:` Code refactoring (bumps patch version)
- `docs:` Documentation changes
- `build:` Build system changes
- `chore:` Maintenance tasks
- `ci:` CI/CD changes
- `style:` Code style changes
- `test:` Test changes

**Examples:**
```bash
git commit -m "feat: add booking modal option"
git commit -m "fix: correct event filtering logic"
git commit -m "docs: update installation instructions"
```

#### Release Commands

Install dependencies first:
```bash
npm install
```

**Before releasing**, update version numbers and create SQL migration:

1. **`weltspiegel.xml`**:
   - Update the `<version>` tag to match the new version

2. **`update-manifest.xml`**:
   - Update the `<version>` tag to match the new version
   - Update the download URL to match the new version tag and filename

3. **`administrator/components/com_weltspiegel/sql/updates/{version}.sql`**:
   - Create a new SQL migration file named with the new version (e.g., `1.1.0.sql`)
   - For releases without database changes, use: `# Empty - No database updates`
   - This ensures Joomla's version tracking works correctly

4. Commit these changes

**Note:** Do NOT manually update `package.json` - changelogen will automatically bump the version there.

Then create a release:

```bash
# Patch release (0.4.0 -> 0.4.1) - for bug fixes
npm run release:patch

# Minor release (0.4.0 -> 0.5.0) - for new features
npm run release:minor

# Major release (0.4.0 -> 1.0.0) - for breaking changes
npm run release:major
```

This will:
1. Generate/update `CHANGELOG.md`
2. Bump version in `package.json`
3. Create a git commit with the changes
4. Create a git tag (e.g., `v0.5.0`)
5. Push to GitHub
6. Create a GitHub release with changelog
7. Trigger GitHub Actions to build and attach the ZIP file

#### Manual Changelog Generation

To generate changelog without releasing:

```bash
npm run changelog
```

### What Gets Packaged

The build script includes only the necessary files:
- `weltspiegel.xml` - Component manifest
- `administrator/` - Backend component files
- `components/` - Frontend component files
- `media/` - JavaScript and asset files

Excluded from package:
- `.idea/` - IDE files
- `.git/`, `.github/` - Git repository and workflows
- `.build/` - Build scripts
- `node_modules/` - Dependencies
- `package.json`, `package-lock.json` - npm files
- `update-manifest.xml` - Update manifest (hosted separately)
- `*.zip` - Previous builds

## Continuous Integration

### GitHub Actions

The component includes a GitHub Actions workflow (`.github/workflows/release.yml`) that automatically builds and attaches the ZIP file to releases.

#### How It Works

When you run `npm run release:minor` (or patch/major):
1. **Changelogen** creates the GitHub release with changelog
2. **GitHub Actions** triggers automatically on release creation
3. **Workflow** packages the ZIP and attaches it to the release

The workflow:
1. Checks out the code
2. Sets up Node.js LTS with npm caching
3. Installs dependencies with `npm ci`
4. Creates the installable ZIP package
5. Uploads the ZIP to the release created by changelogen

No manual intervention needed - just run the release command!

## Features

- **Event Management**: Fetches current movies from Cinetixx API
- **Showtime Display**: Shows upcoming showtimes for each movie
- **Booking Integration**: Links to external booking system
- **Responsive Design**: Mobile-friendly layouts
- **Template Overrides**: Supports Joomla template overrides
- **Automatic Updates**: Self-updating via Joomla's update system

## License

MIT License - see LICENSE file for details

## Credits

Developed for Weltspiegel Cottbus cinema
