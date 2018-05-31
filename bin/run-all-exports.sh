#!/bin/sh
CURRENT_PATH=$(cd $(dirname "$0"); pwd)
/bin/sh $CURRENT_PATH/export-field-collection.sh
/bin/sh $CURRENT_PATH/export-definition.sh
/bin/sh $CURRENT_PATH/export-customlayout.sh
/bin/sh $CURRENT_PATH/export-bricks.sh
/bin/sh $CURRENT_PATH/export-staticdata.sh
/bin/sh $CURRENT_PATH/export-customsql.sh
