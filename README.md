# Pigeon Lineage & Loft Management System

A comprehensive web-based platform built with **Laravel 13**, **Bootstrap 5**, and **jQuery** designed for pigeon breeders, racers, and loft managers to maintain pedigrees, medical logs, and daily loft operations.

---

## Key Features

* **Lineage & Pedigree Tracking:** Interactive multi-generation pedigree trees, parent matching (sire/dam), and complete offspring tracking.
* **Auto-Banding & Profiles:** Automatic ring number generation, breed classification (pure vs. cross), and multi-photo storage (profile, eye-sign, wing pattern, awards).
* **Health & Medical Logs:** Digital vaccination schedules, booster due alerts, and illness/quarantine recovery tracking.
* **Flight & Race Performance:** Training logs, race results, speed calculations (meters per minute), and distance milestones.
* **Multi-User & Loft Management:** Role-based access control (Admin / Member), bird limit caps, customizable metadata tags (breeds, colors, statuses), and instant ring/bloodline search.

---

## Tech Stack

* **Backend:** PHP 8.3+, Laravel 13.x
* **Frontend:** Blade, HTML5, CSS3, Bootstrap 5, jQuery
* **Database:** SQLite (default for development) / MySQL

---

## Quick Setup (GitHub Codespaces or Local)

### 1. Clone & Install Dependencies
```bash
git clone [https://github.com/your-username/pigeon-management-system.git](https://github.com/your-username/pigeon-management-system.git)
cd pigeon-management-system
composer install
npm install
