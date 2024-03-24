# Project Name

## Expense Tracker

Welcome to the installation and operational guide for your system.
This README provides all necessary steps to get your
system up and running, whether you're deploying on Apache or Nginx, ensuring you meet all pre-installation conditions,
and guiding you through both automatic and manual installation processes.

## Table of Contents

- [License Agreement](#license-agreement)
- [Prerequisites](#prerequisites)
- [Installation Instructions](#installation-instructions)
    - [Apache Setup](#for-apache-users)
    - [Nginx Setup](#for-nginx-users)
    - [Directory Ownership](#directory-ownership)
- [Pre-Installation Conditions](#pre-installation-conditions)
- [Installation](#installation)
    - [Automatic Installation](#automatic-installation)
    - [Manual Installation](#manual-installation)

## License Agreement

This software is provided under a specific license
that restricts usage to individuals or organizations
that have purchased the system directly from the developer or through an authorized reseller.
Unauthorized use, copying, distribution,
or modification of this software is strictly prohibited and may be punishable by law.

## Prerequisites

Before you begin the installation process, ensure your system meets the following requirements:

- **Web server:** Apache or Nginx
- **PHP version:** 7.4.33 or newer.
  Ensure that your PHP installation meets the minimal requirements to use the latest features and
  security updates.

- **Database system:** Choose one of the following, based on your project needs:
    - `mysqli`: PHP mysqli extension for MySQL v5.7/ MariaDB v10 / Percona Server v8 / Galera Cluster v4 for MySQL.
      Suitable for relational data storage, including user permissions.
    - `pdo`: PHP Data Objects (PDO) extension supports multiple databases (MySQL, PostgreSQL, SQLite, etc.). Versatile
      for any relational database management system.
    - `pgsql`: PHP extension for PostgreSQL v10 or later. Ideal for applications requiring advanced database features.
    - `sqlite3`: PHP extension for SQLite 3. Lightweight, suitable for smaller projects or as a development database.
    - `sqlsrv`: PHP extension for Microsoft SQL Server 2016 or later. Suitable for applications integrated into
      Microsoft ecosystems.
    - `oci8`: PHP extension for Oracle Database v21c or later. Powerful for enterprise-level applications requiring
      Oracle DB.
    - `ibm_db2`: PHP extension for IBM DB2 v7.1 or later. Suitable for enterprise applications that rely on IBM
      databases.

- **Cache system:** Choose one of the following caching mechanisms to enhance performance:
    - `redis`: PHP Redis extension for working with Redis, a fast, in-memory data store. Excellent for caching user
      permissions for quick access.
    - `memcached`: PHP memcached extension for interfacing with Memcached, an in-memory key-value store. Good for
      caching frequently accessed data like user permissions.
    - `apcu`: PHP APCu extension provides user cache for variables stored in memory. Suitable for caching small datasets
      like user permissions without distributed caching.

- **Necessary PHP extensions:** Ensure the following extensions are installed and enabled in your PHP environment:
    - `curl`: For fetching data from external services.
    - `fileinfo`: To determine file types for uploaded files.
    - `filter`: For data validation and sanitization.
    - `gd`: Required for image processing tasks.
    - `json`: Essential for working with JSON data format.
    - `sodium`: For modern cryptography.
    - `xml`: Needed for XML parsing and generation.
    - `zip`: For working with zip archives.

## Installation Instructions

### For Apache Users

1. **Place the Project Folder**: Copy the project folder to your Apache server's web directory,
   typically `/var/www/html`.

2. **Configure `.htaccess`**: Ensure the `.htaccess` file within your project directory is set to redirect requests
   appropriately to your root index file. Example configuration:

    ```apacheconf
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [L]
    ```

### For Nginx Users

1. **Configuration File**: Utilize the `nginx.conf` example provided in the `install` directory of the project to
   configure your site.

2. **FastCGI Parameters**: Use the `fastcgi_params` file also located in the `install` directory to ensure proper
   handling of PHP files. An example Nginx server block might look like:

    ```nginx
    server {
        listen 80;
        server_name yourdomain.com;
        root /path/to/your/project;

        index index.php index.html index.htm;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include /path/to/your/project/install/fastcgi_params;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }
    ```

### Directory Ownership

For the application to function correctly and securely, certain directories must be writable by the webserver user, and
others must have full access set appropriately. Please follow the guidelines below to set up your directory permissions
and ownership:

**Config Directory Writability**
The `config/` directory and its subdirectories must be writable by the webserver user.
This is crucial for the
application's configuration files to be updated and managed properly.
Change the ownership of the `config` folder and
its subdirectories to the web server user (commonly `www-data` for Apache on Ubuntu systems, `apache` on CentOS,
or `nginx` if using Nginx).
Use the following command, adjusting it to fit your web server's user:

   ```bash
   sudo chown -R webserver_user:webserver_group /path/to/your/project/config
   ```

Replace webserver_user and webserver_group with your web server's actual user and group names,
and /path/to/your/project with the actual path to your project.
For the config directory and its subdirectories:

```plaintext
/config/config
/config/data
/config/logs
/config/user
/config/user/images
/config/user/attachments
```

Storage and Public Directories Accessibility
The storage/ and public/ directories must be set as fully accessible.
These directories are used for storing potentially dangerous items like images, uploaded files, etc.,
and thus need to be correctly secured but accessible by the application.
To ensure full accessibility while maintaining security, set the permissions as follows:

```
sudo chmod -R 775 /path/to/your/project/storage
sudo chmod -R 775 /path/to/your/project/public
```

This setting allows the web server and the group members to read, write,
and execute files in these directories, while others can only read and execute them.
Ensure that the web server user is a member of the group owning these directories.

Note: Always replace /path/to/your/project with the actual path to your project directory,
and adjust user and group names according to your server's configuration.
It's crucial to maintain the balance between accessibility for the application to function and security
to protect sensitive data and prevent unauthorized access.

### Pre-Installation Conditions

* Ensure your database system (MySQL, PostgreSQL, MariaDB) is installed.
* Create a database user with permissions to create databases and tables.
* For security, use a separate database user for the system with only read and write permissions to the specified
  database and tables.

### Installation

### Automatic Installation

Access your site (e.g., http://yourdomain.com/) and follow the on-screen instructions to configure the system.

### Manual Installation

1. Create a Database: Name it expense_tracker or another name of your choosing.
2. Create Tables: Refer to the placeholder section for table creation scripts (as tables are still to be defined).
