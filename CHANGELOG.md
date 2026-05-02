# Changelog

All notable changes to NoDB-DomainPark will be documented in this file.

## [1.0.0] - 2026-05-02

### Initial Release
- File-based domain parking system with zero database dependency
- SHA256 password encryption
- Admin panel with login, domain management, settings, and friendship links
- Visitor tracking with click count and user agent records
- Support for domain redirect (301/302) and display modes
- Root domain extraction supporting various subdomain formats
- Bilingual documentation (English/Chinese)

### Added
- **setup.php**: Installation wizard with auto-generated password (username + YYMMDD)
- **index.php**: Frontend domain parking handler with type-based response logic
- **adm/login.php**: Admin login with session management and brute-force protection (5 attempts/day limit)
- **adm/dm.php**: Domain management (add/edit/delete), click statistics, visit records modal
- **adm/check.php**: Password/domain settings and friendship links management
- **inc/inc-sha.php**: SHA256 encryption utility

### Changed
- **setup.php**: Admin Domain field auto-fills with current visiting domain
- **setup.php**: Reinstallation now cleans old `inc/domain.php` and all files in `data/` directory
- **adm/login.php**: Password verification changed to direct SHA256 hash comparison (fixed login failure after password change)
- **adm/dm.php**: Domain status type preserves original value when editing (no longer resets to 1)
- **adm/dm.php**: Added top navigation menu (Domain Management / Settings / Friendship Links / Logout)
- **adm/dm.php**: Fixed visit records modal - implemented AJAX handler to load and display useragent data in table format
- **adm/dm.php**: Fixed edit form data loading - about, url, userdata fields now correctly populate via HTML data attributes
- **adm/dm.php**: Added Refresh button next to "Existing Domains" heading
- **adm/dm.php**: Domain names rendered as clickable links opening in new tab
- **adm/dm.php**: Pending status highlighting - yellow border + light background on Status dropdown with orange prompt text when editing a Pending domain; auto-removes when user changes status
- **adm/check.php**: Added "New Password" input field; leave empty to keep current password unchanged
- **adm/check.php**: Old password verification unified to SHA256 hash comparison only (removed plaintext fallback)
- **adm/check.php**: Added single link entry form (Link Text + Link URL) that auto-formats as `<li><a href="..." target="_blank">...</a></li>` and appends to existing links
- **adm/check.php**: Separated Settings and Friendship Links into different views via URL parameters (`?set=on` / `?links=on`); navigation links updated accordingly
- **index.php**: Same-IP deduplication - if current IP matches the last useragent record's IP, click count does not increment and no duplicate record is added
- **index.php**: Added `getRealIp()` function to retrieve real visitor IP behind Cloudflare/CDN proxies (prioritizes `HTTP_CF_CONNECTING_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_REAL_IP`)
- **index.php**: Fixed line breaks in About field - now properly renders newline characters as `<br>` tags in frontend display
- **README.md**: Merged chinese.txt and english.txt into bilingual README with Requirements section (PHP 5.4.0+, Cloudflare country code support, no database/extensions needed)
- **All PHP files**: Added author attribution comment header

### Documentation
- Installation step #6: Reinstallation instructions
- Feature #5: Admin-bound domain can also be added to parking list without conflict
- Note #4: Recommend keeping original author attribution comments
- Created bilingual README.md with directory structure tree
- Created LICENSE (MIT)
- Created CHANGELOG.md

### Technical Details
- **Minimum PHP Version**: 5.4.0+
- **Required Extensions**: None (uses only built-in PHP functions)
- **Storage**: JSON flat files in `data/` directory
- **Encryption**: SHA256 via PHP `hash()` function

---

## Author
- **max-godman** - max_godman@foxmail.com
- GitHub: https://github.com/max-godman

