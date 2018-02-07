#!/usr/bin/env bash

# This file is executed before the packaging of the eclipse-junit-tests jar
# to retrieve files that are required within the jar but would be duplicated
# if permanently stored within the eclipse-junit-tests directory

JUNIT_SCRIPTS_PATH="src/main/scripts"

PRODUCTION_SCRIPTS_PATH="../../production/testScripts/configuration/sdk.tests/testScripts"

cp $PRODUCTION_SCRIPTS_PATH/test.xml 		$JUNIT_SCRIPTS_PATH/test.xml
cp $PRODUCTION_SCRIPTS_PATH/runtests 		$JUNIT_SCRIPTS_PATH/runtests
cp $PRODUCTION_SCRIPTS_PATH/runtests.bat 	$JUNIT_SCRIPTS_PATH/runtests.bat
cp $PRODUCTION_SCRIPTS_PATH/runtests.sh 	$JUNIT_SCRIPTS_PATH/runtests.sh
cp $PRODUCTION_SCRIPTS_PATH/runtestsmac.sh 	$JUNIT_SCRIPTS_PATH/runtestsmac.sh