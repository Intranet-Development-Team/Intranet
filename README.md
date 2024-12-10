# Intranet
Intranet is an online information exchange platform for students.
## Features
- User system with roles and self-moderation
- Assignments system
- Blog system
- Calendar system
- Mailing system
- File resources system
##  Usage
### Direct Download
The code inside `htdocs` is almost ready to use on Linux and Windows PHP servers: Firstly, simply download the content in `htdocs` to your webroot. Then, change the `SITE_DOMAIN` constant in `CoreLibrary/CoreFunctions.php` to the domain of your site.
### Clone
You can clone the whole repository on to your server and set the `htdocs` directory to be the webroot.
```
git clone https://github.com/Intranet-Development-Team/Intranet.git
```
> Note that `https` is required by default. Set in root `.htaccess`.
#### Recommended PHP Configuration
The below configurations are not required but can be set to enhance the server.
- Memory size: `memory_limit = 8M`
- Script execution time: `max_execution_time = 30`
- Php upload limit: `upload_max_filesize = 10M`