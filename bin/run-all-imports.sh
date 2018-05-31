#!/bin/sh
CURRENT_PATH=$(cd $(dirname "$0"); pwd)
/bin/sh $CURRENT_PATH/clear-classes.sh
/bin/sh $CURRENT_PATH/import-field-collection.sh
/bin/sh $CURRENT_PATH/import-definition.sh
/bin/sh $CURRENT_PATH/import-customlayout.sh
/bin/sh $CURRENT_PATH/import-bricks.sh
/bin/sh $CURRENT_PATH/import-staticdata.sh
/bin/sh $CURRENT_PATH/import-customsql.sh
