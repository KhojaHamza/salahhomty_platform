# 🏛️ Integrated Municipal Complaint Management System

A centralized web platform that lets citizens report urban issues (damaged roads, waste management, street lighting, etc.), track their resolution in real time, and helps municipal staff handle complaints efficiently and transparently.

> 🎓 **Academic project** – End-of-Year Project (PFA), Software Engineering – ISG Sousse
> 👤 **Author:** Hamza Khouja

---

## 📖 Table of Contents

- [Context & Problem](#-context--problem)
- [Project Goals](#-project-goals)
- [Actors & Roles](#-actors--roles)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Usage](#-usage)
- [Non-Functional Requirements](#-non-functional-requirements)
- [Development Methodology](#-development-methodology)
- [What I Learned](#-what-i-learned)
- [Roadmap](#-roadmap)
- [Author](#-author)

---

## 🌍 Context & Problem

Traditional ways of reporting urban problems (phone calls, postal mail, in-person visits) often lead to bureaucratic delays, lost information, and a lack of transparency. Citizens rarely know what happened to their report — a "black box" effect that causes frustration and civic disengagement.

Municipal agents, on the other hand, deal with:

- **Fragmented data** – reports arrive through many channels with no unified database.
- **Geographic imprecision** – problems are hard to locate accurately.
- **Inefficient resource allocation** – urgent tasks are difficult to prioritize.

This project answers these issues with a single, centralized platform covering the full life cycle of a complaint: **report → assignment → resolution → feedback**.

## 🎯 Project Goals

This project is an **MVP (Minimum Viable Product)** that automates the complaint life cycle:

- **Citizen empowerment** – a user-friendly interface to submit detailed reports with photos and precise geolocation.
- **Operational efficiency** – municipal agents receive, categorize, and update complaint statuses in real time.
- **Accountability** – clear traceability from submission to resolution and citizen evaluation.

## 👥 Actors & Roles

Access is controlled through **Role-Based Access Control (RBAC)**.

| Role | Description | Main capabilities |
|------|-------------|-------------------|
| **Citizen** | End user who reports issues | Manage profile, submit complaints (title, description, category, location, photos), track status, rate the service once resolved |
| **Municipal Agent** | Operational user in the field | View assigned complaints, update status (Pending / In progress / Resolved), add technical notes, confirm completion |
| **Administrator** | System supervisor | Manage users and roles, manage complaint categories, assign complaints to agents, monitor dashboards and reports |

## ✨ Features

- 🔐 Secure authentication and role-based access
- 📝 Complaint submission with title, description, and category
- 📍 Geolocation (latitude / longitude) of the reported issue
- 📷 Photo upload as visual evidence
- 🔄 Real-time status tracking (Pending → In progress → Resolved)
- ⭐ Citizen rating after resolution
- 🗂️ Category management (roads, sanitation, street lighting, …)
- 👷 Complaint assignment to agents by expertise or area
- 📊 Admin dashboard with statistics (resolution time, satisfaction)

> ℹ️ *Adjust this list to match exactly what is implemented in your version.*

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Front-end | HTML5, CSS3, JavaScript |
| Back-end | PHP |
| Database | MySQL  |
| Local server | XAMPP |
| Version control | Git & GitHub |

## 📁 Project Structure



```
SalahHomty/
│
├── actions/                      # Traitement des actions et des requêtes
│   ├── auto_assign.php
│   ├── cancel_request.php
│   ├── service_requests.php
│   ├── services.php
│   ├── submit_comment.php
│   └── submit_report.php
│
├── admin/                        # Interface et fonctionnalités de l'administrateur
│   ├── agents.php
│   ├── api_comments.php
│   ├── api_logs.php
│   ├── api_services.php
│   ├── auth_check.php
│   ├── delete_comment.php
│   ├── index.php
│   ├── reports.php
│   └── services.php
│
├── agent/                        # Interface et fonctionnalités des agents
│   ├── api.php
│   ├── citizenrequests.php
│   ├── index.php
│   ├── profile.php
│   └── reports.php
│
├── assets/                       # Ressources statiques
│   ├── css/
│   │   ├── agent_dashboard.css
│   │   ├── helppage.css
│   │   ├── history.css
│   │   ├── signin.css
│   │   └── style.css
│   |
│   │   
│   │
│   └── js/
│       └── agentdashboard_script.js
│
├── auth/                         # Authentification et gestion des comptes
│   ├── forgot_password.php
│   ├── google_callback.php
│   ├── google_oauth.php
│   ├── login.php
│   ├── logout.php
│   ├── migration.php
│   └── signup.php
│
├── config/                       # Configuration
│   ├── database.php
│   └── google.php
│
├── includes/                     # Classes et composants réutilisables
│   └── EmailService.php
│
├── templates/                    # Modèles d'e-mails
│   └── emails/
│       ├── base_email.php
│       ├── cancel_service.php
│       ├── confirm_claim.php
│       ├── new_claim.php
│       └── new_service.php
│
├── user/                         # Interface et fonctionnalités des citoyens (vide)
│
├── .gitignore
├── index.php                     # Point d'entrée principal
└── workspace.code-workspace      # Configuration de l'espace VS Code
```

## 🚀 Installation

### Prerequisites

- PHP 7.4+ (or later)
- MySQL / MariaDB
- A local web server (XAMPP, WAMP, Laragon, …)
- Git

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/<your-username>/<your-repo>.git
   ```

2. **Move the project** into your server's web root (e.g. `htdocs` for XAMPP).

3. **Create the database**
   - Open phpMyAdmin
   - Create a database, e.g. `complaints_db`
   - Import the SQL file (e.g. `database/schema.sql`)

4. **Configure the database connection** in `config/db.php`:
   ```php
   $host = "localhost";
   $dbname = "complaints_db";
   $user = "root";
   $password = "";
   ```

5. **Start Apache and MySQL**, then open:
   ```
   http://localhost/<project-folder>/
   ```

## 💡 Usage

1. **Citizen:** register, log in, submit a complaint with photos and location, then follow its status.
2. **Administrator:** log in, create categories, validate users, and assign complaints to agents.
3. **Agent:** log in, review assigned complaints, update their status, and add notes.
4. **Citizen:** once the complaint is resolved, rate the service.

## 📐 Non-Functional Requirements

Design targets for the system:

- **Security:** authenticated access, RBAC, HTTPS/TLS in transit, encrypted sensitive data, GDPR-aware design
- **Performance:** pages loading in under 2 seconds, support for 100+ concurrent users, image compression
- **Availability:** target uptime of 99.5%, ACID-compliant database, backup and recovery
- **Usability:** responsive design (desktop, tablet, mobile), WCAG-oriented, multilingual (Arabic, French, English)
- **Maintainability:** modular architecture, documented code, easy to extend

## 🔄 Development Methodology

The project follows an agile approach inspired by **Scrum**: short sprints, incremental delivery, and continuous improvement through sprint planning, daily syncs, reviews, and retrospectives.

## 🎓 What I Learned

This is **the first website I have ever built**, and it taught me far more than just syntax. Through this project I:

- Understood the **full life cycle of a software project**: from analyzing a real-world problem and writing specifications to design, development, testing, and publishing.
- Learned to **translate requirements into features** using actors, use cases, and functional vs. non-functional requirements.
- Learned how to **organize my code**: separating concerns, splitting files by role and responsibility, and reusing components instead of repeating code.
- Gained hands-on experience with **full-stack development**: HTML/CSS/JS on the client side, PHP on the server side, and a relational database in between.
- Practiced **database design** (users, roles, complaints, categories, statuses) and its relationship with application logic.
- Understood core **security principles**: authentication, role-based access, and protecting user data.
- Discovered the value of **Git and GitHub** for versioning and sharing my work.
- Learned to **debug**, to read documentation, and to improve my work step by step.

This project is the foundation on which I am building my next ones, and it made me confident about pursuing a career in software engineering.



## 👤 Author

**Hamza Khouja**


---

⭐ If you find this project interesting, feel free to give it a star!
