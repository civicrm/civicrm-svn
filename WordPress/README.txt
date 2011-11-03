
1. Install WordPress and get it working

2. Ensure you have a working version of civicrm (for drupal), just ensure DAO's etc are generated

3. make a directory: wp-content/plugins/civicrm  and give the webserver read/write access to it

4. make a directory: wp-content/plugins/files and give the webserver read/write access to it

5. create a symlink from: wp-content/plugins/civicrm/civicrm TO the root of your civicrm directory

6. copy CIVICRM_ROOT/WordPress/civicrm.php to wp-content/plugins/civicrm

7. Go to http://<site url>/wp-admin/options-general.php?page=civicrm-settings and configure CiviCRM
