# Intranet
Intranet is an online information exchange platform for students.
## Features
- User system with roles and self-moderation
- Assignments system
- Blog system
- Calendar system
- Mailing system
- File resources system
- Wiki-like content editing systems
## Usage
### Requirements
- PHP Version >= 8.0
- Extension GD enabled
> Note that `https` is required by default. Set in root `.htaccess`. Delete it if you do not have TLS/SSL.
#### Recommended PHP Configuration
The below configurations are not required but can be set to enhance the server.
- Memory size: `memory_limit = 8M`
- Script execution time: `max_execution_time = 30`
- Php upload limit: `upload_max_filesize = 10M`
### Download
The code inside `htdocs` is almost ready to use on PHP servers: Firstly, simply download the content in `htdocs` to your webroot or clone the repository. Then, change the `SITE_DOMAIN` constant in `CoreLibrary/CoreFunctions.php` to the domain of your site.

You may also update your site's name at `SITE_NAME` constant in `CoreLibrary/CoreFunctions.php` and icon at `Complements/img/icon.png`.
## Fundamentals
### Core
Files in `CoreLibrary` are used to include and not being run from web requests. 

`CoreFunctions.php` is the core of Intranet, containing the error handling system, user system and some core functions. It should be generally included in all pages that accept web requests. When a user is logged in, a `Session` object can be created and used to carry out user and session actions.
### User
Users are stored under `Login/Accounts/`. Each directory represents a user with its name representing the username. Initially, only three files: `password.txt`, `role.txt` and `pfp.txt` are present and storing the *SHA3-512 hashed password*, *JSON-encoded roles* and *profile picture*.

Upon the first login, `firstlogin.txt` will be created after the user changes their password. Then, `birthday.txt` and `electives.txt` are created after user setting their birthday and elective subjects.

Files like `notifications.txt` will be created by the system with its first push notification. Directories like `Drafts`, `Logins`, `Mails` are alike.

After starting the operation of Intranet, you should not delete or modify any user's username as it may cause the system to throw `UnknownUsernameException` while reading user-related content.

Most preset `.txt` files storing the content of Intranet can be initially empty (0 in length). Exceptions are `pfp.txt` and `password.txt` in user directories. Making them empty may result in error.
### Frontend
The frontend is designed and programmed with Bootstrap 5.3, Bootstrap Icons 1.11.3, jQuery 3.6.4 and jQuery UI 1.13.2.
