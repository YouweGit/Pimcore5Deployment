PIMCORE DEPLOYMENT EXTENSION
----------------------------

Version: Pimcore 5
 
Note: NOT compatible with Pimcore versions under 5

Developed by: Youwe (Manea, Roelf)




Excerpt
-------

* Always developing your pimcore projects locally, and deploying them to servers afterwards like a real pro?
* Don't like having to manually save all the object classes on every server after deploying changed classes?
* Like to work more efficiently and professionally with pimcore?

... then this extension is for you!



Description
-----------

The pimcore deployment extension as the following general functionalities:

* Provide a way to do migrations of the pimcore object classes data structure
* Provide a way to keep tables with static data synced on the server
* Import some custom static sql files on deployment
* Provide a way to migrate static document data to the server (work in progress)



Usage and examples
------------------

After changing or adding a pimcore object class, use the following command line command to export the updated
definitions:

Export all pimcore classes:

    ./htdocs/plugins/Deployment/cli/export-definition.sh
    
Export some selected pimcore classes:
    
    ./htdocs/plugins/Deployment/cli/export-definition.sh -c product,persom

When the project has been set up on a new dev system, or the project has been deployed to a server. Use the following
command to have pimcore update the object class related files and database structure:

Import all json definitions:

    ./htdocs/plugins/Deployment/cli/import-definition.sh

Import some selected json definitions:

    ./htdocs/plugins/Deployment/cli/import-definition.sh -c product,persom
    
Drop all the views (and tables that should be views!) in the database. Typically done before a complete import-definition.

Drop all:
    
    ./htdocs/plugins/Deployment/cli/drop-views.sh
    
Drop selected (by name):
    
    ./htdocs/plugins/Deployment/cli/drop-views.sh -c product,persom
    
Drop selected (by id):
    
    ./htdocs/plugins/Deployment/cli/drop-views.sh -i 2,5,6

Clear the classes table in the database. Can be used when the class ids in the exported definition
mismatch the ones already in the database. Use with care.
    
    ./htdocs/plugins/Deployment/cli/clear-classes.sh

If some tables contain static data which is actually managed whilst developing, and are not supposed to be
altered by the client on the server, you can use the static data exporter/importers:

Configure which tables are static using the extras->extensions->deployment-configuration in pimcore. Run 
this command on your dev station after altering the table data:

    ./htdocs/plugins/Deployment/cli/export-staticdata.sh
    
Run this command (automatically) on the server after deployment. Warning: this will completely replace all
data in the tables:
    
    ./htdocs/plugins/Deployment/cli/import-staticdata.sh
    

Import all custom layouts json definitions:

    ./htdocs/plugins/Deployment/cli/import-customlayouts.sh

Export all custom layouts json definitions:

    ./htdocs/plugins/Deployment/cli/export-customlayouts.sh


Import all custom sql files:

    ./htdocs/plugins/Deployment/cli/import-customsql.sh




Deployment to server
--------------------

When deploying your project to a server, the deployment script would typically execute these commands after
deploying the updated code:

./vendor/youwe/pimcore5deployment/bin/clear-classes.sh
./vendor/youwe/pimcore5deployment/bin/import-field-collection.sh
./vendor/youwe/pimcore5deployment/bin/import-definition.sh
./vendor/youwe/pimcore5deployment/bin/import-customlayout.sh
./vendor/youwe/pimcore5deployment/bin/import-bricks.sh
./vendor/youwe/pimcore5deployment/bin/import-field-collection.sh
./vendor/youwe/pimcore5deployment/bin/import-staticdata.sh
./vendor/youwe/pimcore5deployment/bin/import-customsql.sh

NOTE: the import-field-collection is in twice, because the definition and field collection refer to each other. Its very
important to run it twice and in this order!
    
    
Initial (first-time) deployment to server [ not relevant until content-migration works ]
----------------------------------------------------------------------------------------

Be careful not to install this plugin on your server, because it would generate a pimcore table for itself using
an improvised ID. Rather follow this route:

* On your development machine, enable the plugin and install the plugin (from the Extras -> Extensions menu).
* This will generate a pimcore data table called DeploymentDataMigration
* After this is done, use the export-definition command to export all pimcore data table definitions to json files.
* Now you will run the import-definition command on the server and all the data tables will be generated from json files.
* The plugin will enable itself.
* By creating the data tables from the json files, the DeploymentDataMigration datatable is also created.
* This means the plugin is installed.


Update system.php and config.php from ini files [ not upgraded to pimcore 5 - not sure if necessary ]
-----------------------------------------------------------------------------------------------------
System.php can be update with:
```
    ./plugins/PimcoreDeployment/cli/update-mysql-credentials.sh --mysql-credentials-path [PATH_TO_INI_FILE] 
```

The ini file structure that we expect is: 
```
 * mysql_hostname=[HOSTNAME]
 * mysql_port=[PORT]
 * mysql_database=[DATABASE_NAME]
 * mysql_user=[DATABASE_USERNAME]
 * mysql_password=[DATABASE_PASSWORD]
 
```

Cache.php can be update with:
```
    ./plugins/PimcoreDeployment/cli/update-redis-credentials.sh --redis-credentials-path [PATH_TO_INI_FILE] 
```
The ini file structure that we expect is: 
```
 * redis_hostname=[HOSTNAME]
 * redis_port=[PORT]
 * redis_database=[DATABASE]
 
```
Keep in mind that because a lot of environments do not have cache.php
the command will create a default one for you and update its values from ini file


Troubleshooting
---------------

Before importing the definitions, you might need to set the correct permissions, in order for this script to be able to
write to the definition files. In case of local development, a low security solution like the following could be used:

    sudo chmod -R 777 .


Installation
------------

Plugin can be installed through composer. Add json to your composer.json:

    composer require youwe/pimcore5deployment 
    
 
Plugin development
------------------

To create a new version, check out the master branch somewhere and go:

    git tag
    git tag 0.xxx         (minor update = latest tag + 0.1)
    git push origin --tags


