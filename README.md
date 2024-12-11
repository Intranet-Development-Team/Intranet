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
> Note that `https` is required by default. Set in root `.htaccess`.
#### Recommended PHP Configuration
The below configurations are not required but can be set to enhance the server.
- Memory size: `memory_limit = 8M`
- Script execution time: `max_execution_time = 30`
- Php upload limit: `upload_max_filesize = 10M`
### Download
The code inside `htdocs` is almost ready to use on PHP servers: Firstly, simply download the content in `htdocs` to your webroot or clone the repository. Then, change the `SITE_DOMAIN` constant in `CoreLibrary/CoreFunctions.php` to the domain of your site.
## Fundamentals