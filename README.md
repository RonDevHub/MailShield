# 🛡️ MailShield - E-Mail Protection Service

<div align="center">
  
![Created](https://mini-badges.rondevhub.de/forgejo/RonDevHub/MailShield/created-at/*/*/de) ![GitHub Repo stars](https://mini-badges.rondevhub.de/forgejo/RonDevHub/MailShield/lastcommit/*/*/de) ![GitHub Repo stars](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/stars/*/*/de) ![GitHub Repo stars](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/issues/*/*/de) ![GitHub Repo language](https://mini-badges.rondevhub.de/forgejo/RonDevHub/MailShield/language/*/*/de) ![GitHub Repo license](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/license/*/*/de) ![GitHub Repo release](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/release/*/*/de) ![GitHub Repo release](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/forks/*/*/de) ![GitHub Repo downlods](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/downloads/*/*/de) ![GitHub Repo stars](https://mini-badges.rondevhub.de/github/RonDevHub/MailShield/watchers) [![Docker Publish](https://github.com/RonDevHub/MailShield/actions/workflows/docker-publish.yml/badge.svg?branch=main)](https://github.com/RonDevHub/MailShield/actions/workflows/docker-publish.yml)

[![Buy me a coffee](https://mini-badges.rondevhub.de/icon/cuptogo/Buy_me_a_Coffee-c1d82f-222/social "Buy me a coffee")](https://www.buymeacoffee.com/RonDev)
[![Buy me a coffee](https://mini-badges.rondevhub.de/icon/cuptogo/ko--fi.com-c1d82f-222/social "Buy me a coffee")](https://ko-fi.com/U6U31EV2VS)
[![Sponsor me](https://mini-badges.rondevhub.de/icon/hearts-red/Sponsor_me/social "Sponsor me")](https://github.com/sponsors/RonDevHub)
[![Pizza Power](https://mini-badges.rondevhub.de/icon/pizzaslice/Buy_me_a_pizza/social "Pizza Power")](https://www.paypal.com/paypalme/Depressionist1/4,99)
[![Bitcoin Power](https://mini-badges.rondevhub.de/icon/bitcoin/Bitcoin-ff7b00/social/-666666 "Bitcoin Power")](https://btc-sharer.s3cr.net/v/Vv7pQfYHW3HDqOkKujhGo8DOokNoA9FD_v3pyzFLMHZKR1gyTFJRQ1A5RWZmM09hTjI5SFZsY2ZlQThGWVZPazBnbHczaTJ6UzVWZVVGcnYwMWEr)
</div>
<hr>

MailShield is a minimalist, resource-efficient PHP application designed to hide email addresses behind encrypted links and a Cloudflare Turnstile check. It’s the perfect solution for sharing email addresses on public websites without being targeted by scraper bots.

### ✨ Features
- **Zero-Tracking:** No cookies, no analytics.
- **Secure:** AES-256-GCM encryption for all stored data.
- **Bot Protection:** Integrated Cloudflare Turnstile (Captcha alternative).
- **Lightweight:** Optimized for small home labs (minimal RAM usage).
- **Modern UI:** Built with Tailwind CSS, including Dark Mode support.
- **Multilingual:** Automatically detects German and English.

### 🚀 Deployment (Docker/Portainer)
Use this stack configuration for Portainer.
```
services:
  mailshield:
    image: ghcr.io/rondevhub/mailshield:latest
    container_name: mailshield
    restart: unless-stopped
    environment:
      # Generate a secure key (e.g., openssl rand -base64 32)
      - APP_KEY=base64:GeneriereMichBitteNeu12345678901234567=
      - https://mailshield.deine-domain.de
      # Admin Access
      - ADMIN_SITE=false
      - ADMIN_USER=admin
      - ADMIN_PASS=admin123
      # Cloudflare Turnstile
      - CF_SITE_KEY=1x00000000000000000000AA
      - CF_SECRET_KEY=1x0000000000000000000000000000000AA
      - DB_PATH=/var/www/html/data/mailshield.sqlite
    ports:
      - "8080:80"
    volumes:
      - ./data:/var/www/html/data
```
### Created with ❤️ by RonDevHub