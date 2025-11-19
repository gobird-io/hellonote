# Changelog

All notable changes to HelloNote will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-19

### Added
- Initial release of HelloNote
- Admin-only notes management interface
- Create, read, update, and delete (CRUD) operations for notes
- Custom database table for storing notes (`wp_hellonote_notes`)
- AJAX-powered interface for dynamic operations
- Security features:
  - Capability checks (`manage_options` required)
  - Nonce verification for CSRF protection
  - Input sanitization for all user data
  - Output escaping for XSS prevention
  - Prepared SQL statements for injection prevention
- Modern PSR-4 autoloading with Composer
- Object-oriented architecture with separation of concerns
- Responsive admin interface with clean design
- Translation-ready with `hellonote` text domain
- Automatic timestamp tracking (created_at, updated_at)
- Inline editing functionality
- Delete confirmation dialog
- Success/error message notifications

### Technical Details
- WordPress 5.8+ compatibility
- PHP 7.4+ requirement
- PSR-4 namespace: `HelloNote`
- Database table: `wp_hellonote_notes`
- Admin menu icon: `dashicons-edit`
- Text domain: `hellonote`

### Security
- All operations restricted to administrators only
- CSRF protection on all forms and AJAX requests
- SQL injection prevention using `$wpdb` prepared statements
- XSS prevention with proper output escaping
- Direct file access protection

### File Structure
```
hellonote/
├── hellonote.php          # Main plugin bootstrap
├── composer.json          # Composer configuration
├── src/                   # PHP classes (PSR-4)
│   ├── Database.php
│   ├── Plugin.php
│   ├── Menu.php
│   ├── Page.php
│   └── Ajax.php
├── assets/                # Frontend assets
│   ├── css/admin.css
│   └── js/admin.js
└── languages/             # Translations
    └── hellonote.pot
```

### Known Limitations
- No pagination (all notes load at once)
- No search or filter functionality
- No categories or tags
- Plain textarea (no rich text editor)
- No export functionality
- No user attribution tracking

## [Unreleased]

### Planned Features
- Pagination for better performance with many notes
- Search and filter capabilities
- Rich text editor integration (TinyMCE or Gutenberg blocks)
- Note categories and tags
- User attribution (track who created each note)
- Export functionality (CSV, JSON, PDF)
- Import functionality
- Note sharing between administrators
- File attachments
- Note templates
- Color coding or priority levels
- Archive/unarchive functionality
- Trash/restore functionality
- Activity log
- REST API endpoints
- WP-CLI commands

---

## Version History

- **1.0.0** - 2025-11-19: Initial release

---

## Upgrade Notes

### From Future Versions
*(No upgrade path yet - this is the first version)*

---

## Links

- [Homepage](https://gobird.io)
- [Author](https://gobird.io)

---

**Note**: This changelog follows [Keep a Changelog](https://keepachangelog.com/) format and uses [Semantic Versioning](https://semver.org/).
