start cmd /c launch_script.bat
ping 192.0.2.2 -n 1 -w 5000 > nul
launch_backup_script.bat