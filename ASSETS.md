# Required Assets

This document lists the required image assets for the Digital Library Management System.

## Logo

**Path**: `public/images/logo.png`
- **Dimensions**: 200x200 pixels (or larger, maintaining aspect ratio)
- **Format**: PNG with transparency
- **Usage**:
  - README.md header
  - Application header/navigation
  - Email templates
  - Print materials
- **Design Notes**: Should represent a modern library or book-related imagery

## Default Book Cover

**Path**: `public/images/default-cover.jpg`
- **Dimensions**: 300x450 pixels (2:3 aspect ratio)
- **Format**: JPG
- **Usage**: Fallback image when book cover is not uploaded
- **Design Notes**:
  - Neutral, professional design
  - Should indicate "No cover available" or similar
  - Match application color scheme

## Default User Avatar

**Path**: `public/images/default-avatar.jpg`
- **Dimensions**: 200x200 pixels (square)
- **Format**: JPG
- **Usage**: Default user profile picture
- **Design Notes**:
  - Generic user silhouette or initials placeholder
  - Neutral, professional appearance
  - Match application color scheme

## Creating Placeholder Images

### Option 1: Use Online Tools
- [Placeholder.com](https://placeholder.com)
- [Unsplash](https://unsplash.com) for book covers
- [UI Avatars](https://ui-avatars.com) for user avatars

### Option 2: Design Tools
- Figma, Adobe Photoshop, or GIMP
- Canva for quick designs
- Inkscape for SVG logos

### Option 3: Laravel Fallbacks
Implement dynamic placeholders in code:

```php
// In Book model or view
public function getCoverUrlAttribute()
{
    if ($this->cover_image) {
        return asset('storage/covers/' . $this->cover_image);
    }

    // Return placeholder
    return asset('images/default-cover.jpg');
}
```

## Directory Structure

Ensure the following directories exist:

```
public/
└── images/
    ├── logo.png           # Library logo
    ├── default-cover.jpg  # Default book cover
    └── default-avatar.jpg # Default user avatar
```

## Setup Instructions

1. Create the `public/images` directory if it doesn't exist:
   ```bash
   mkdir -p public/images
   ```

2. Add your images to the directory following the naming conventions above

3. Verify images are accessible:
   ```bash
   ls -la public/images/
   ```

## Git Considerations

These placeholder images should be committed to the repository so all developers have consistent defaults. However, uploaded user content (in `storage/app/public/`) should be in `.gitignore`.

Current `.gitignore` should include:
```
/storage/app/public/covers/*
/storage/app/public/profiles/*
!/storage/app/public/.gitkeep
```

But allow:
```
# These are defaults, should be committed
!/public/images/
```
