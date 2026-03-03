# 🏠 University Hostel Management System

A full-stack web application designed to streamline university hostel operations. This system provides role-based dashboards and automation for booking, room allocation, payments, inventory, and student management.

---

## 📌 Features

### 🔐 Authentication & User Management
* **Secure Access**: Robust login and registration system.
* **Role-Based Access Control (RBAC)**: Custom dashboards for Students, Provosts, Administrative Officers, Assistant Registrars, and House Tutors.

### 🎓 Student Module
* **Room Management**: Online room booking, selection, and automated allocation.
* **Financials**: Digital payment portal with automated receipt generation.
* **Support**: Complaint submission and tracking system.
* **Logistics**: Check-in and Check-out management.

### 👔 Administrative Modules
* **Provost**: Application approval/rejection, occupancy monitoring, and fee structure definition.
* **Administrative Officer**: Payment verification, inventory management (furniture/appliances), and maintenance scheduling.
* **Assistant Registrar**: Student registration approval and document verification.
* **House Tutor**: Floor-wise student monitoring and attendance/roll-call system.

---

## 🛠 Tech Stack

* **Frontend**: HTML, CSS, JavaScript (Bootstrap/TailwindCSS UI).
* **Backend**: PHP.
* **Database**: MySQL.
* **Communication**: AJAX for seamless API calls.

---

## 📂 Project Structure

```text
hostel-management/
├── assets/             # CSS, JS, Images
├── includes/           # DB connection, header/footer, auth
├── templates/          # Login, Register, Dashboards
├── modules/            # Booking, Payment, Inventory, Complaints
├── api/                # PHP APIs for AJAX calls
├── sql/                # Database schema
└── index.php           # Landing page
