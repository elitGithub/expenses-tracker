# Project Name

## Expense Tracker

Welcome to the installation and operational guide for your system. This README provides all necessary steps to get your system up and running, whether you're deploying on Apache or Nginx, ensuring you meet all pre-installation conditions, and guiding you through both automatic and manual installation processes.

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

- Web server: Apache or Nginx
- PHP version 7.4.33 or newer
- Database system: MySQL, PostgreSQL, or MariaDB
- Necessary PHP extensions: [list extensions]

## Installation Instructions

### For Apache Users

1. **Place the Project Folder**: Copy the project folder to your Apache server's web directory, typically `/var/www/html`.

2. **Configure `.htaccess`**: Ensure the `.htaccess` file within your project directory is set to redirect requests appropriately to your root index file. Example configuration:

    ```apacheconf
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [L]
    ```

### For Nginx Users

1. **Configuration File**: Utilize the `nginx.conf` example provided in the `install` directory of the project to configure your site.

2. **FastCGI Parameters**: Use the `fastcgi_params` file also located in the `install` directory to ensure proper handling of PHP files. An example Nginx server block might look like:

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

Change the ownership of the `config` folder and its subdirectories to the web server user (apache/nginx) or create the required directories:

```plaintext
/config/config
/config/data
/config/logs
/config/user
/config/user/images
/config/user/attachments
```
### Pre-Installation Conditions
* Ensure your database system (MySQL, PostgreSQL, MariaDB) is installed.
* Create a database user with permissions to create databases and tables.
* For security, use a separate database user for the system with only read and write permissions to the specified database and tables.

### Installation

### Automatic Installation
Access your site (e.g., http://yourdomain.com/) and follow the on-screen instructions to configure the system.

### Manual Installation

1. Create a Database: Name it expense_tracker or another name of your choosing.
2. Create Tables: Refer to the placeholder section for table creation scripts (as tables are still to be defined).
