#!/bin/bash

# Notify the user about the deprecated script.
echo -e "\033[31m" >&2
echo -e "=================================================================================================" >&2
echo -e "Please note: This script is no longer supported, please use the CLI application to install i-doit" >&2
echo -e "=================================================================================================" >&2
echo -e "\033[39m\n" >&2
echo -e "In order to install i-doit via console you can use the 'install' command of our CLI application." >&2
echo -e "Please run \033[33mphp console.php install\033[39m and follow the instructions.\n" >&2
echo -e "After installing i-doit please make sure to create your first tenant by running" >&2
echo -e "\033[33mphp console.php tenant-create\033[39m - after this step i-doit is ready to be used!" >&2

exit 0
