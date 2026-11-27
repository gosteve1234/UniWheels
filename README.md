# UniWheels Motors — User Manual

> **Disclaimer:** This website and all vehicle listings were created for educational purposes as part of a final project. Listings may be simulated and do not represent actual vehicles or a real car dealership. Prices, availability, specifications, and contact details are subject to change and should not be considered final. No real purchase or financial transaction is intended.

---

## Table of Contents
1. [Introduction](#introduction)
2. [System Minimum Requirements](#system-minimum-requirements)
3. [Installation Guide](#installation-guide)
4. [Usage Instructions](#usage-instructions)
5. [Frequently Asked Questions (FAQs)](#frequently-asked-questions-faqs)
6. [Development Team](#development-team)

---

## Introduction
**UniWheels Motors** is a car-listing and reservation website developed as a final project. It helps customers browse available vehicles and view their details, while providing administrators with tools to manage the site.

Features such as reservation holds, booking confirmation popups, detailed vehicle history, photo magnification, and an editable content management system are available in the current version.

---

## System Minimum Requirements

| Hardware / Software | Minimum Requirements | Recommended Requirements |
| :--- | :--- | :--- |
| **Operating System** | Windows 10, macOS, or a supported Linux distribution | Currently supported version of operating system |
| **Processor** | Dual-core processor, 2 GHz or equivalent | Quad-core processor or better |
| **Memory (RAM)** | 4 GB | 8 GB or more |
| **Storage** | At least 5 GB of free space for project and local server tools | 10 GB or more |
| **Display Resolution** | 1366 x 768 | 1920 x 1080 |

---

## Installation Guide

Follow these step-by-step instructions to set up the project locally:

1. **Install XAMPP**
   - Download and install XAMPP from the official Apache Friends website.

2. **Copy Project Files**
   - Place the UniWheels project folder into XAMPP's `htdocs` directory:
     - **Windows:** `C:\xampp\htdocs\`

3. **Start Local Server Services**
   - Open the XAMPP Control Panel and start both **Apache** and **MySQL** services.

4. **Create & Import Database**
   - Open `http://localhost/phpmyadmin` in your browser.
   - Create the database required by the project, select it, and import the provided `.sql` file.

5. **Configure Database Connection**
   - Update the project's database connection file with the database name and your local MySQL username and password.

6. **Access UniWheels**
   - In your browser, visit `http://localhost/<project-folder>/` using the actual project folder name.

---

## Usage Instructions

### For Customers
- **Browse:** Open the website to browse vehicle listings.
- **View Details:** Select a vehicle to view the information provided.
- **Inquire / Reserve:** Use available contact, inquiry, or booking options to submit a reservation.

### For Administrators
- **Sign In:** Sign in to the admin area using administrator credentials.
- **Manage Content:** Use available controls to manage listings or review inquiries.
- **Verify Updates:** Confirm any changes appear correctly on the public website.

---

## Frequently Asked Questions (FAQs)

1. **Why can't I see or book a vehicle?**
   - The vehicle may be unavailable, or the version may not support online booking. Contact the site administrator for assistance.

2. **How do I update a vehicle's information?**
   - Use the admin tools if available. Otherwise, an authorized administrator will need to update project data.

3. **Is reservation available?**
   - Yes, you can submit a reservation in the current version, with additional booking features planned for future updates.

4. **Are the vehicle listings real?**
   - The vehicle listings are real; however, they change over time and are based on their masterlist as of November 6, 2026.

---

## Development Team

- **Avelino, Ciedee Jyrho** — Back-end Developer
- **Guardapies, Karl Andrei** — Full-Stack Developer
- **Robante, Roylyn James** — Front-End Developer / Database Manager
- **Watimar, Jhon Patrick** — Project Manager
