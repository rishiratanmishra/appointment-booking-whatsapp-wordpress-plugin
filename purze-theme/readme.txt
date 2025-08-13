=== Purze Cleaning Services Theme ===
Contributors: yourbrand
Requires at least: 6.0
Tested up to: 6.x
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: service, business, leads, ajax forms

== Description ==
A clean, modern WordPress theme tailored for service businesses. Features a hero "Service Request" form with AJAX submission, lead storage, admin notifications, and auto-replies.

== Installation ==
1. Zip the theme folder and upload via Appearance → Themes → Add New → Upload.
2. Activate "Purze Cleaning Services Theme".
3. Set a static Front page (Pages → Add New → Home) and assign the "Front Page" template.
4. Set the menu under Appearance → Menus and assign to Primary.

== Theme Settings → Leads & Email ==
Visit the admin menu: Theme Settings.
- Notification Emails: Comma-separated recipients for admin notifications.
- From Name / From Email: Used only for theme emails.
- Auto-Reply Subject / Body (HTML): Supports shortcodes {name}, {email}, {phone}, {service}, {message}, {site_name}.
- Service Options: Comma list of label|value pairs.
- Data consent note: Optional text shown under the hero form.
- Google Sheets: Placeholder toggle and config (no live sync yet).

== Shortcodes ==
- [theme_hero_form] — Renders the hero Service Request form.
- [theme_contact_form] — Renders a minimal contact form.

== Database ==
On theme activation, table {prefix}theme_leads is created with fields: id, name, email, phone, service, message, status, created_at.

== Performance ==
- No jQuery on frontend; vanilla JS only.
- Scripts are deferred.
- Unminified and minified assets included.

== Accessibility ==
- Proper labels, aria-live regions, focus handling.

== Testing Checklist ==
- Required field errors appear client & server side.
- Valid submission stores in DB and sends admin & auto-reply emails.
- Dropdown services reflect settings edits.
- From Name/Email appear correctly.
- Works on mobile (≤375px) and desktop (≥1440px).
- No console errors, passes basic Lighthouse checks.

== Support ==
GPL software provided as-is. Submit issues via your repository.