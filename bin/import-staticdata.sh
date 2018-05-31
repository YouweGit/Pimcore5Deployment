#!/bin/sh
CURRENT_PATH=$(cd $(dirname "$0"); pwd)
php ${CURRENT_PATH}/migration.php -a import-staticdata $*
