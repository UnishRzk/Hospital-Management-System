<div align="center">

# 🏥 SwasthyaTrack — Hospital Management System

**A comprehensive, open-source Hospital Management System built with PHP & MySQL.**

[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-88%25-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/Database-MySQL%208.0-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Deploy-Docker-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![GitHub Stars](https://img.shields.io/github/stars/UnishRzk/swasthyatrack-hospital-management-system?style=flat&logo=github&label=Stars&color=yellow)](https://github.com/UnishRzk/swasthyatrack-hospital-management-system/stargazers)

<br/>

![SwasthyaTrack Landing Page](docs/screenshots/landing-page.png)

<p><em>Streamline patient registration, appointments, bed management, medical records, and prescriptions — all from one platform.</em></p>

[Getting Started](#-quick-start) · [Features](#-features) · [Screenshots](#-screenshots) · [Documentation](#-documentation)

</div>

---

## Why SwasthyaTrack?

|                            |                                                                          |
| -------------------------- | ------------------------------------------------------------------------ |
| **Multi-Role Dashboards**  | Dedicated interfaces for Patients, Doctors, Nurses, and Admins           |
| **Appointment Scheduling** | Book, reschedule, and cancel appointments with real-time status tracking |
| **Bed Management**         | Reserve, occupy, and release beds with live status updates               |
| **Digital Prescriptions**  | Doctors create prescriptions; patients view them instantly               |
| **Medical Report Uploads** | Secure PDF uploads by nurses and patients                                |
| **Secure Authentication**  | Hashed passwords, role-based access control, and session management      |
| **Docker Ready**           | One command deployment with Docker Compose                               |
| **Clean & Extensible**     | Well-structured LAMP codebase — easy to learn, fork, and extend          |

---

## 🚀 Quick Start

### Option A: Docker (Recommended)

```bash
# 1. Clone the repository
git clone https://github.com/UnishRzk/swasthyatrack-hospital-management-system.git
cd swasthyatrack-hospital-management-system

# 2. Build and start all services
docker compose up --build -d
```

This spins up three containers automatically:

| Service         | URL                                            | Purpose           |
| --------------- | ---------------------------------------------- | ----------------- |
| **Application** | [http://localhost:8080](http://localhost:8080) | Apache + PHP app  |
| **phpMyAdmin**  | [http://localhost:8081](http://localhost:8081) | Database admin UI |
| **MySQL**       | `localhost:3306`                               | Database server   |

#### 3. Import the database

1. Open **phpMyAdmin** at [http://localhost:8081](http://localhost:8081)
2. Log in with:

   | Field    | Value   |
   | -------- | ------- |
   | Server   | `mysql` |
   | Username | `root`  |
   | Password | `root`  |

3. Create a new database named **`swasthyatrack`**
4. Select the `swasthyatrack` database → **Import** → choose [`database/swasthyatrack.sql`](database/swasthyatrack.sql) → **Go**

> **Note:** The Docker Compose file creates a default database called `testdb`. The application expects `swasthyatrack` (already configured in [`src/config/db.php`](src/config/db.php)), so you must create it manually via phpMyAdmin. Alternatively, change `MYSQL_DATABASE` in `docker-compose.yml` from `testdb` to `swasthyatrack` before running `docker compose up` to skip this step.

#### 4. Verify

```bash
docker ps
```

You should see three containers running: `apache_server`, `mysql_db`, and `phpmyadmin`.

Open [http://localhost:8080](http://localhost:8080) and you're good to go! 🎉

### Option B: XAMPP

<details>
<summary>Click to expand XAMPP instructions</summary>

1. Copy the project folder into your XAMPP web root:
   - **Windows:** `C:\xampp\htdocs\`
   - **Linux:** `/opt/lampp/htdocs/`

2. Start **Apache** and **MySQL** from the XAMPP Control Panel.

3. Open [phpMyAdmin](http://localhost/phpmyadmin), create a database called `swasthyatrack`, and import [database/swasthyatrack.sql](database/swasthyatrack.sql).

4. Update your PHP database config:

   | Parameter | Value           |
   | --------- | --------------- |
   | Host      | `localhost`     |
   | Port      | `3306`          |
   | User      | `root`          |
   | Password  | _(empty)_       |
   | Database  | `swasthyatrack` |

5. Open the app at: `http://localhost/swasthyatrack-hospital-management-system/src`

> **Tip:** If port `3306` is already in use, change the MySQL port in XAMPP and update your DB config to match.

</details>

---

## 🔑 Demo Login Credentials

> **Try it instantly!** Use these credentials after importing the database to explore every role.

### Quick Access (one account per role)

| Role        | Username     | Password   |
| ----------- | ------------ | ---------- |
| **Admin**   | `admin`      | `admin`    |
| **Doctor**  | `jpark`      | `Med@2900` |
| **Nurse**   | `schaudhary` | `Med@3012` |
| **Patient** | `unishrzk`   | `unish123` |

<details>
<summary><strong>🧑‍⚕️ All Doctor Accounts (16 available)</strong></summary>

<br/>

| #   | Username    | Password   |
| --- | ----------- | ---------- |
| 1   | `jpark`     | `Med@2900` |
| 2   | `lneupane`  | `Med@7789` |
| 3   | `nshrestha` | `Med@3456` |
| 4   | `apoudel`   | `Med@5678` |
| 5   | `sgurung`   | `Med@6789` |
| 6   | `pjoshi`    | `Med@7890` |
| 7   | `mrai`      | `Med@9012` |
| 8   | `kacharya`  | `Med@2234` |
| 9   | `pkarki`    | `Med@4456` |
| 10  | `sumanadh`  | `Med@1234` |
| 11  | `mthompson` | `Med@2566` |
| 12  | `liwei`     | `Med@2677` |
| 13  | `sjohnson`  | `Med@2788` |
| 14  | `rkoirala`  | `Med@2345` |
| 15  | `aiyer`     | `Med@2455` |
| 16  | `epetrova`  | `Med@3011` |

</details>

> ⚠️ **Note:** These are demo credentials for local testing only. Always change default passwords before any public deployment.

## ✨ Features

### Patient Portal

- Register a new account with secure credential storage
- Book, edit, or cancel appointments by selecting doctor, date, and time slot
- Reserve and cancel hospital beds (status: **Empty** → **Reserved** → **Occupied** / **Out of Order**)
- Upload personal medical reports (PDF) and view nurse-uploaded reports
- View digital prescriptions issued after consultations

### Doctor Dashboard

- View all scheduled appointments with status tracking
- Mark appointments as **Completed**, **Booked**, or **Cancelled**
- Write and submit digital prescriptions (medicines, dosages, instructions)

### Nurse Dashboard

- View all doctor-assigned appointments and update statuses
- Upload patient medical reports with metadata, securely linked to patient records

### Administrator Dashboard

- **User Management** — Add, update, delete users; assign roles; reset passwords
- **Appointment Management** — View, update, or cancel appointments hospital-wide
- **Bed Management** — Add/remove beds; update bed details and status

---

## 🛠️ Tech Stack

| Layer                | Technology                      |
| -------------------- | ------------------------------- |
| **Runtime**          | Linux (Ubuntu/WSL2)             |
| **Containerization** | Docker & Docker Compose         |
| **Web Server**       | Apache HTTP Server              |
| **Backend**          | PHP                             |
| **Database**         | MySQL 8.0                       |
| **DB Admin**         | phpMyAdmin                      |
| **Frontend**         | HTML5, CSS3, Vanilla JavaScript |

> Built entirely on the **LAMP stack** — no external JS/CSS frameworks required. All tools are open-source and well-documented.

---

## 🏗️ Architecture

SwasthyaTrack follows a **three-tier client–server architecture**:

<details>
<summary><strong>📎 View full architecture diagram</strong></summary>

![System Architecture](docs/diagrams/architecture.png)

</details>

---

## 🗄️ Database Schema

![Database Schema](docs/diagrams/schema.jpg)

### Core Tables

| Table                                        | Purpose                                                                  |
| -------------------------------------------- | ------------------------------------------------------------------------ |
| `users`                                      | Login credentials and role (`patient`, `doctor`, `nurse`, `admin`)       |
| `doctors` / `patients` / `nurses` / `admins` | Role-specific profiles linked via `user_id` (1:1)                        |
| `appointments`                               | Patient–doctor bookings with status (`Booked`, `Cancelled`, `Completed`) |
| `doctor_education`                           | Multiple education records per doctor (1:many)                           |
| `beds`                                       | Bed availability, type, and status tracking                              |
| `prescriptions`                              | Uploaded report/prescription file metadata per user                      |

## 📐 System Design

<details>
<summary><strong>Use Case Diagram</strong></summary>

![Use Case Diagram](docs/diagrams/usecase-diagram.png)

</details>

<details>
<summary><strong>DFD — Level 0</strong></summary>

![DFD Level 0](docs/diagrams/dfd-level0.png)

</details>

<details>
<summary><strong>DFD — Level 1</strong></summary>

![DFD Level 1](docs/diagrams/dfd-level1.png)

</details>

<details>
<summary><strong>Physical DFD</strong></summary>

![Physical DFD](docs/diagrams/physical-dfd.png)

</details>

<details>
<summary><strong>Entity Relationship Diagram</strong></summary>

![ER Diagram](docs/diagrams/ER.png)

</details>

---

## 📸 Screenshots

#### Patient Dashboard

![Patient Dashboard](docs/screenshots/patient-dashboard.png)

#### Book Bed

![Book Bed](docs/screenshots/book-bed.png)

#### My Appointments

![My Appointments](docs/screenshots/my-appointments.png)

<details>
<summary><strong>🖥️ View all screenshots</strong></summary>

#### Landing Page

![Landing Page](docs/screenshots/landing-page1.png)

#### Login

![Login Page](docs/screenshots/login.png)

#### Register

![Register Page](docs/screenshots/register.png)

#### Select Doctors

![Select Doctors](docs/screenshots/select-doctors.png)

#### Book Appointment

![Book Appointment](docs/screenshots/book-appointment.png)

#### Prescription View

![Prescription](docs/screenshots/prescription.png)

#### Admin Dashboard

![Admin Dashboard](docs/screenshots/admin-panel.png)

#### Doctor Dashboard

![Doctor Dashboard](docs/screenshots/doctor-dashboard.png)

</details>

---

## 🧪 Testing

SwasthyaTrack was validated through both **unit testing** and **system testing**.

| Type               | Scope                                             | Details                                                                            |
| ------------------ | ------------------------------------------------- | ---------------------------------------------------------------------------------- |
| **Unit Testing**   | Individual modules (signup, login, booking, etc.) | Tested with predefined inputs; expected vs. actual outcomes compared               |
| **System Testing** | End-to-end workflows                              | Covered signup → login → appointment booking → bed reservation → prescription flow |

All core functionalities passed as expected. Error handling was validated throughout the application flow.

📄 **Full test cases and results:** [docs/Testing/Testing.pdf](docs/Testing/Testing.pdf)

---

## 📖 Documentation

| Resource          | Link                                                     |
| ----------------- | -------------------------------------------------------- |
| Project Report    | [docs/project-report.pdf](docs/project-report.pdf)       |
| Testing Document  | [docs/Testing/Testing.pdf](docs/Testing/Testing.pdf)     |
| Database SQL Dump | [database/swasthyatrack.sql](database/swasthyatrack.sql) |

---

## 🤝 Contributing

This project was developed as an academic project and is **not actively maintained**.  
Contributions, pull requests, and feature requests are **not being accepted** at this time.

You are welcome to **fork** and modify the project for your own learning or use under the terms of the license.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

<div align="center">

**If you find this project useful, consider giving it a ⭐ — it helps others discover it!**

Made with ❤️ by [UnishRzk](https://github.com/UnishRzk)

</div>
