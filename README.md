### Documentation

The script is a PHP command-line application called "phpMyFireWall." It provides functionalities related to iptable linux based firewall management. 
Here is an overview of the script's functionality and usage:

1. The script starts with shebang (`#!/usr/bin/php -q`) to specify the PHP interpreter.
2. The `error_reporting` function is called to set the error reporting level to `E_ERROR`, which hides notices and warnings.
3. The configuration file (`config.php`) is included to load necessary settings.
4. The environment variable `PATH` is set using `putenv` to include the `$SYSPATHS` value from the configuration file.
5. The kernel version is obtained by executing the command `uname -r`.
6. The logged-in user is obtained using the `whoami` command.
7. Command-line arguments are parsed and stored in the `$args` variable.
8. If the `$args` variable is empty or contains `-h` or `--help`, usage instructions are displayed.
9. The `--check` argument checks the system for compatibility with phpMyFireWall:
   - It verifies that the script is running with root privileges.
   - It checks the kernel version (requires version 2.6).
   - It checks the existence of iptables and modprobe executables.
   - The results of each check are displayed.
   - If all checks pass, a file named "STATUS" is created with the content "OK"; otherwise, "STATUS" is created with the content "FALSE."
10. The `--install` argument installs phpMyFireWall:
    - It reads the content of the "STATUS" file.
    - If the content is "OK," the installation process begins.
    - The `/usr/local/phpmyfirewall` directory is created.
    - The contents of the `src` directory are copied to `/usr/local/phpmyfirewall`.
    - Ownership and permissions are set for the copied files.
    - The content of the "SETUP" file is displayed.
    - A message indicating the completion of the installation is shown.
11. The `--uninstall` argument uninstalls phpMyFireWall:
    - A message indicating the uninstallation process starts is displayed.
    - The `firewall.php` script is executed with the `--stop` argument.
    - The `/usr/local/phpmyfirewall` directory and the "STATUS" file are removed.
    - A message indicating the completion of the uninstallation process is shown.

### Usage

To use the phpMyFireWall script, run it from the command line with one of the following arguments:

- `--install`: Installs phpMyFireWall if the system passes the compatibility check. Follow the displayed instructions during the installation process.
- `--check`: Checks the system for compatibility with phpMyFireWall and displays the results of each check.
- `--uninstall`: Uninstalls phpMyFireWall and removes the installation directory and related files.

For example:

```shell
php phpMyFireWall.php --install
php phpMyFireWall.php --check
php phpMyFireWall.php --uninstall
```

Please note that the script assumes that the necessary configuration file (`config.php`) and other required files are present in the correct locations.
