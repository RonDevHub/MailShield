<h1 align="center">
  <img src="https://mail-shield.net/img/icon.png" height="50"><br>
  MailShield<br><img src="https://mail-shield.net/badge">
</h1>

<p align="center">
  <em>E-Mail Protection Service. Secure your inbox. Protect your identity.</em>
</p>

<div align="center">

![Created](https://mini-badges.rondev.de/forgejo/RonDevHub/MailShield/created-at/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/forgejo/RonDevHub/MailShield/lastcommit/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/MailShield/stars/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/MailShield/issues/*/*/en) ![GitHub Repo language](https://mini-badges.rondev.de/forgejo/RonDevHub/MailShield/language/*/*/en) ![GitHub Repo license](https://mini-badges.rondev.de/github/RonDevHub/MailShield/license/*/*/en) ![GitHub Repo release](https://mini-badges.rondev.de/github/RonDevHub/MailShield/release/*/*/en) ![GitHub Repo release](https://mini-badges.rondev.de/github/RonDevHub/MailShield/forks/*/*/en) ![GitHub Repo downlods](https://mini-badges.rondev.de/github/RonDevHub/MailShield/downloads/*/*/en) ![GitHub Repo stars](https://mini-badges.rondev.de/github/RonDevHub/MailShield/watchers) [![status-badge](https://ci.commitcloud.net/api/badges/4/status.svg)](https://ci.commitcloud.net/repos/4) 

[![Buy me a coffee](https://mini-badges.rondev.de/icon/cuptogo/Buy_me_a_Coffee-c1d82f-222/for-the-badge "Buy me a coffee")](https://www.buymeacoffee.com/RonDev)
[![Buy me a coffee](https://mini-badges.rondev.de/icon/cuptogo/ko--fi.com-c1d82f-222/for-the-badge "Buy me a coffee")](https://ko-fi.com/U6U31EV2VS)
[![Pizza Power](https://mini-badges.rondev.de/icon/paypal/PayPal/for-the-badge "Pizza Power")](https://www.paypal.com/donate/?hosted_button_id=PWY939TPCQ3RA)
[![Bitcoin Power](https://mini-badges.rondev.de/icon/bitcoin/Bitcoin-ff7b00/for-the-badge/-666666 "Bitcoin Power")](https://btc-sharer.s3cr.net/v/Vv7pQfYHW3HDqOkKujhGo8DOokNoA9FD_v3pyzFLMHZKR1gyTFJRQ1A5RWZmM09hTjI5SFZsY2ZlQThGWVZPazBnbHczaTJ6UzVWZVVGcnYwMWEr)
</div>
<hr>

MailShield is a minimalist, resource-efficient PHP application designed to hide email addresses behind encrypted links and a CAPTCHA check. Choose either Cloudflare Turnstile or self-hosted Cap.

### ✨ Features
- **Zero-Tracking:** No cookies, no analytics.
- **Secure:** AES-256-GCM encryption for all stored data.
- **Bot Protection:** Cloudflare Turnstile or self-hosted Cap CAPTCHA.
- **Lightweight:** Optimized for small home labs (minimal RAM usage).
- **Modern UI:** Built with Tailwind CSS, including Dark Mode support.
- **Multilingual:** Automatically detects German and English.
- **Badge:** Use a badge to promote your protection

## Database Support

MailShield supports:

- SQLite (default, zero config)
- PostgreSQL (recommended for production)

Switch via environment variables.

### 🚀 Deployment (Docker/Portainer)
Use this stack configuration for Portainer.
```
services:
  mailshield:
    image: ghcr.io/rondev/mailshield:latest
    container_name: mailshield
    restart: unless-stopped
    environment:
      # Generate a secure key (e.g., openssl rand -base64 32)
      - APP_KEY=base64:GeneriereMichBitteNeu12345678901234567=
      - APP_URL=https://mailshield.deine-domain.de
      # Admin Access
      - ADMIN_SITE=false
      - ADMIN_USER=admin
      - ADMIN_PASS=admin123
      # CAPTCHA: enable exactly one provider
      - CF_ENABLED=true
      - CF_SITE_KEY=1x00000000000000000000AA
      - CF_SECRET_KEY=1x0000000000000000000000000000000AA
      - CAP_ENABLED=false
      - CAP_URL=https://captcha.example.com
      - CAP_SITE_KEY=your-cap-site-key
      - CAP_SECRET_KEY=your-cap-secret-key
      - DB_PATH=/var/www/html/data/mailshield.sqlite
    ports:
      - "8080:80"
    volumes:
      - ./data:/var/www/html/data
```

To use Cap instead of Turnstile, set `CF_ENABLED=false` and `CAP_ENABLED=true`. Set `CAP_URL` to the base URL of your Cap instance and provide its site and secret keys. Set both providers to `false` to disable CAPTCHA checks. Enabling both providers (or leaving required settings empty for an enabled provider) displays a configuration error and blocks form submissions.

### Created with ❤️ by RonDevHub