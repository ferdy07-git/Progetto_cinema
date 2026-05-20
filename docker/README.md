# 🐳 Run Progetto Cinema inside Docker

This directory contains everything you need to containerize and run the **Progetto Cinema** application locally inside isolated containers.

---

## 📂 Directory Layout

```text
docker/
├── docker-compose.yml   # Multi-container service definitions (web, db)
├── php/
│   └── Dockerfile       # Custom PHP image instructions (includes mysqli & pdo_mysql)
├── www/                 # 👈 PUT YOUR CINEMA FILES INSIDE THIS FOLDER
└── README.md            # You are here!
```

---

## 🚀 Quick Start Guide

### Step 1: Copy/Move files to the `www/` folder
Copy the entire contents of the cinema project (including `homepage.php`, `database/`, `login/`, `img/`, `struttura/`, `utils/`) into the `docker/www/` folder.

Your `docker/www/` folder should look like this:
```text
docker/www/
├── database/
├── img/
├── login/
├── struttura/
├── utils/
├── homepage.php
└── ... (other files)
```

---

### Step 2: Configure Database Connections for Docker
In a standard Docker network, containers communicate with each other using their service names defined in `docker-compose.yml`. 
Therefore, inside Docker, your web application must connect to the database container using **`db`** as the host address instead of `localhost`.

Modify `docker/www/database/connessione.php` to use the host `"db"`:
```php
<?php
// Changed host from "localhost" to "db" for Docker compatibility
$conn = new mysqli("db", "root", "", "");
$sql = "CREATE DATABASE IF NOT EXISTS cinema";
$conn->query($sql);
$sql = "USE cinema";
$conn->query($sql);
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}
?>
```

---

### Step 3: Spin Up Containers
Open a terminal in the `docker` directory and run:

```bash
docker compose up --build -d
```

This will:
1. Build the custom PHP image with the MySQLi extension.
2. Initialize the MySQL server.
3. Start the Apache server mapping `./www` live.

---

### Step 4: Access the Application
Once the containers are up and running, you can access the website via your browser:

*   **🎬 Cinema Web App**: [http://localhost:8080](http://localhost:8080)

---

## 🛠️ Connecting with MySQL Workbench
Since the database container exposes port `3306` to your host machine, you can connect directly using **MySQL Workbench** with the following credentials:

*   **Connection Method**: Standard (TCP/IP)
*   **Hostname**: `127.0.0.1` (or `localhost`)
*   **Port**: `3306`
*   **Username**: `root`
*   **Password**: *(Leave empty / No password)*
*   **Default Schema**: `cinema`

---

## 📊 Database Seeding & Initialization
When you first access the homepage ([http://localhost:8080](http://localhost:8080)), the page automatically connects to `database/connessione.php` to initialize the `cinema` database, and `database/database.php` runs to dynamically build all tables and seed the initial rows from `cinema_database.sql`.

---

## 🛑 Stopping the Services
To stop and remove the active containers, run:
```bash
docker compose down
```
*(Your database records will persist inside the created Docker Volume `db_data` even after stopping the containers!)*
