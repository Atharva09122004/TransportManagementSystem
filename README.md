# Transport Management System

Transport Management System is a comprehensive web application designed to streamline the operations of a transport company. It handles vehicle management, driver assignments, trip planning, and passenger tracking with an intuitive interface.

## Features

- **Dashboard**: Overview of total vehicles, drivers, and active trips.
- **Vehicle Management**: Add, view, and manage vehicle details (license, insurance, maintenance).
- **Driver Management**: Add, view, and manage driver details (license, contact, availability).
- **Trip Management**:
  - Create and schedule trips.
  - Assign drivers and vehicles to trips.
  - Track trip status (Scheduled, In Progress, Completed).
- **User Roles**:
  - **Admin**: Full access to all features.
  - **User**: Can view trips and manage their profile.
- **Authentication**: Secure login system with role-based access control.

## Tech Stack

- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL

## Installation

### Prerequisites

- XAMPP (Apache + MySQL)
- PHP 7.4 or higher

### Steps

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/adilkhan0610/TransportManagementSystem.git
    ```

2.  **Set up the database**:
    - Open phpMyAdmin.
    - Create a new database named `transport_db`.
    - Import the `transport_db.sql` file located in the `database` folder.

3.  **Configure the database connection**:
    - Open `config.php` in the root directory.
    - Update the database credentials if necessary:
      ```php
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "transport_db";
      ```

4.  **Run the application**:
    - Start the Apache server in XAMPP.
    - Open your browser and navigate to:
      ```
      http://localhost/TransportManagementSystem/index.php
      ```

## Usage

### Login

- **Admin Login**:
  - Username: `admin`
  - Password: `admin123`

- **User Login**:
  - Username: `user`
  - Password: `user123`

### Navigation

- Use the sidebar to navigate between Dashboard, Vehicles, Drivers, and Trips.
- Click the "Add" buttons to create new records.
- Use the "Edit" and "Delete" buttons in the tables to manage existing records.

## File Structure

```
TransportManagementSystem/
├── admin/              # Admin-specific pages
├── assets/             # CSS, JS, and Images
├── config/             # Configuration files
├── database/           # Database files
├── includes/           # Reusable components
├── index.php           # Login page
├── transport_db.sql    # Database schema
└── ...                 # Other PHP files
```

## Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request.