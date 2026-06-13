# Modyssey - MVC Web Platform for Game Modification Management and Sharing

## Introduction and Context
Modyssey is a web application developed as part of the Systems of Multimedia on the Internet (Sistemas de Multimédia na Internet - SMI) academic curriculum. The project consists of a dedicated community hub for game modification creators (modders) and players, providing tools for publishing, version tracking, subscription, and structured sharing of game modifications (mods).

The development of this platform focused on building a dynamic, interactive, and secure system, applying software engineering best practices for the web with a clear separation of concerns.

---

## Objectives and Scope
The primary objective of Modyssey was the implementation of a robust web application using native PHP, without the assistance of commercial MVC frameworks. This constraint was established to master the manual implementation of architectural design patterns, authentication flows, access control mechanisms, and network communication protocols.

The scope of this project includes:
* Designing a clean, modular, and extensible Model-View-Controller (MVC) architecture from scratch.
* Creating an automated web installer to facilitate deployments in both production and local development environments.
* Developing user interaction features, such as subscription systems and automated notifications via email.
* Integrating third-party APIs for metadata enhancement and providing visual analytic dashboards to monitor platform activity.

---

## System Architecture and Design Decisions

### The Model-View-Controller (MVC) Pattern
The application was structured following the MVC design pattern to ensure modularity and ease of maintenance:
* **Core**: Contains the engine of the application, including dynamic routing, translation loading (internationalization), database access abstraction (PDO wrapper), and generic helper classes for validation and file uploading.
* **Models**: Encapsulate the business logic and interact directly with the MySQL relational database schema.
* **Views**: User interface templates using dynamic layouts and pure PHP to render data on the server-side.
* **Controllers**: Act as intermediaries that intercept HTTP requests, validate user authorization, invoke the necessary model operations, and determine which view to render.

### Relational Database Design
The relational database schema was designed to enforce referential integrity and query efficiency. Key highlights include:
* A many-to-many relationship mapping between modifications and categories, ensuring precise classification.
* Version tracking supported by foreign keys with cascading deletions (ON DELETE CASCADE) to guarantee historical data consistency when parent entities are removed.
* Isolation of sensitive environment settings by dynamically reading and writing configuration XML files inside the gitignored `config/configuracoes/` directory.

---

## Implemented Features

### Automatic Web Installer (`setup.php`)
A dynamic web setup wizard was designed to simplify the initial deployment process:
* Validates connections to the MySQL server.
* Automatically executes the database schema SQL script (`database-script.sql`).
* Configures SMTP email server credentials and stores them securely in local XML configuration files.
* Automates the creation of the primary Administrator account and hashes the corresponding credentials.

### Role-Based Access Control (RBAC)
The platform implements a Role-Based Access Control system to protect application routes based on four user roles:
* **Guest**: Restricted to public viewing, searching, and registration features.
* **User**: Authorized to publish new modifications, manage their releases, upload new versions, and subscribe to updates.
* **Sympathizer**: Dedicated supporter tier with tailored interface options.
* **Admin**: Possesses full administrative rights, including user moderation and global system configuration.

### Incremental Version Tracking and Release Management
Unlike generic file hosting solutions, Modyssey supports historical version tracking. Modders can publish multiple releases of their modifications (e.g., `v1.0`, `v1.1`) along with release notes (changelogs). The platform automatically serves the latest stable release for download while preserving access to older versions.

### Email Notifications via Sockets
Users can subscribe to updates for specific games or categories. The application utilizes an SMTP client wrapper that establishes raw TCP socket streams to automatically send HTML-formatted email alerts to subscribers whenever new modifications are published.

### Analytical Dashboard and API Integration
The public RAWG Video Games Database API was integrated into the game management workflow, allowing administrators to search and automatically fetch cover images and game metadata. Additionally, the dashboard page displays graphical analysis of monthly upload trends, top downloaded mods, and active users using Chart.js.

---

## Acquired Skills and Academic Takeaways
Developing Modyssey allowed the consolidation of several advanced competencies in modern web development:

### Native MVC Framework Construction
Developing a routing engine and request-response pipeline from scratch provided a deep understanding of how HTTP requests, routing rules, session management, and state are handled at a low level in PHP.

### Applied Web Security
Real-world security concepts were implemented throughout the application:
* Password encryption using the robust `bcrypt` hashing algorithm.
* Generation and validation of account activation tokens sent via email verification links.
* Protection against automated brute force and bot registrations using custom CAPTCHA validation.
* Configuration of `.htaccess` rules to prevent direct HTTP access to system directories and private XML configuration files.

### Integration of Web Services and Networking Protocols
Consuming RESTful APIs using PHP's cURL extension, along with raw socket-based SMTP implementations, provided hands-on experience in integrating distributed web services and working with network protocols.

---

## System Requirements and Installation

### Minimum Requirements
* Web Server: Apache 2.4+ with the `mod_rewrite` module enabled.
* PHP: Version 8.0 or higher (required extensions: `pdo_mysql`, `simplexml`, `openssl`).
* Database: MySQL 5.7+ or MariaDB.

### Setup Steps

1. Clone the repository into your local web server root directory:
   ```bash
   git clone https://github.com/Ruben23Z/Modyssey.git
   ```

2. Verify that appropriate write permissions are granted to the upload and local configuration directories.

3. Open your browser and navigate to the web installer:
   ```text
   http://localhost/Modyssey/public/setup.php
   ```

4. Input your MySQL database connection credentials, SMTP details (e.g., your Gmail account and app password), and your desired Administrator account credentials.

5. Submit the form to complete the database setup and start using the platform.
