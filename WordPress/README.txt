
1. Install WordPress and get it working

2. Ensure you have a working version of civicrm (for drupal)

3. make a directory: wp-content/plugins/civicrm

4. make a directory: wp-content/files and give the webserver read/write access to it

5. create a symlink from: wp-content/plugins/civicrm/civicrm TO the root of your civicrm directory

6. copy CIVICRM_ROOT/WordPress/civicrm.php to wp-content/plugins/civicrm

7. Create a civicrm.settings.php file here: wp-content/plugins/civicrm

8. Make sure your resource url is: http://wp/wp-content/plugins/civicrm/civicrm/
