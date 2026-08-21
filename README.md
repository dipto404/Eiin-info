# 🚀 iOS Ultra-Transparent AJAX Personnel Portal

An advanced, high-performance web dashboard for fetching, filtering, and visualizing educational institute and personnel data via internal APIs. Designed with a high-tech **Cyberpunk/Matrix aesthetic** merged with modern **iOS Glassmorphism**.

![Version](https://img.shields.io/badge/version-6.0.0-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.x-777BB4.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

---

## ✨ Key Features

- 🎭 **Cyberpunk & iOS Glass UI:** Hacker-style decrypting loader animation, continuous matrix rain background, and dynamic text-scramble effects.
- ⚡ **Anti-Cache API Requests:** Bypasses API caching mechanisms using dynamic timestamps (`microtime`), randomized User-Agents, and strict cURL header mutations.
- 🖼️ **Automated Profile Image Mapping:** Automatically merges employee data from primary nodes with secondary external APIs via string normalization.
- 🔍 **Real-Time Client-Side Filter:** Instantly search through loaded personnel by Name, Mobile Number, Designation, or Subject without extra server load.
- 📊 **Analytics & Quick Overview:** Instant metrics including total staff count, institute details, contact info, and unique job positions.
- 📸 **One-Click Export:** Download the generated dashboard as a high-resolution PNG image (via `html2canvas`) or print/save to clean PDF.

---

## 🛠️ Tech Stack & Requirements

- **Language:** PHP 7.4 or higher
- **Required PHP Extensions:** `curl`, `json`
- **Frontend Framework:** Tailwind CSS (via CDN)
- **Typography & Icons:** Google Fonts (`Fira Code`)
- **Libraries:** [html2canvas v1.4.1](https://cdnjs.com/libraries/html2canvas)

---

## 🚀 Deployment Guide

### Option 1: Shared Hosting (cPanel / Hostinger / Namecheap)
1. Upload your primary PHP script as `index.php` into your server's `public_html` directory.
2. Ensure `cURL` is enabled in your PHP settings (`php.ini`).
3. Upload this `README.md` file into the root folder.
4. Visit your website domain in any modern web browser.

### Option 2: Docker / VPS / Cloud Instance (Ubuntu/Nginx/Apache)
1. Clone the repository to your server:
   ```bash
   git clone [https://github.com/your-username/your-repo-name.git](https://github.com/your-username/your-repo-name.git)
   cd your-repo-name

