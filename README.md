# Socius

🇮🇹 [Leggi in italiano](README.it.md)

**Open source association management system — self-hosted, installable like WordPress.**

Socius is a web application for managing membership organizations. Designed to run on any standard PHP hosting with no special server requirements.

## Features

- Member registry with permanent member number and annual membership card
- Annual renewal workflow with configurable dates and automatic reminders
- Online payments via PayPal and Satispay (webhook-based reconciliation)
- Member profile page (visible to admins and the member themselves)
- Event management with public landing pages
- Assembly and board meeting minutes (semi-automatic, export to PDF/DOCX)
- Multi-user with roles: super admin, admin, secretary, member
- GDPR consent management
- Full audit log
- Multi-language interface (Italian and English included)
- Multi-theme support (UIkit default, Bootstrap and Tailwind ready)
- Import members from CSV/Excel
- Automatic email and WhatsApp notifications

## Requirements

Same as WordPress:
- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.6+
- Any standard shared hosting

## Installation

1. Download the latest release from the Releases page
2. Upload to your server document root
3. Visit your-domain.com/install and follow the guided setup wizard
4. Remove or block the /install folder after setup

## URL Structure

Socius works out of the box on any hosting with no server configuration needed:

```
your-domain.com/members.php
your-domain.com/member.php?id=1
your-domain.com/member-edit.php?id=1
```

No mod_rewrite or Nginx rules required.

## Themes

The active theme is configurable from the back-end settings panel.

| Theme | Status |
|---|---|
| UIkit 3 | Default |
| Bootstrap 5 | Placeholder — contributions welcome |
| Tailwind CSS | Placeholder — contributions welcome |

To create a new theme copy `public/themes/uikit/`, rename it, and replace
the HTML with your framework of choice.

## Languages

Italian and English are included. To add a new language:
1. Copy `lang/en/`
2. Rename the folder with the ISO language code (e.g. `lang/de/`)
3. Translate the values in each PHP file
4. The language appears automatically in the back-end selector

## Contributing

- Translate the interface into a new language
- Build a new theme (Bootstrap, Tailwind, or anything else)
- Report bugs via Issues
- Submit pull requests

All modified versions distributed to others must be released under GPL v3.

## License

GNU General Public License v3.0
