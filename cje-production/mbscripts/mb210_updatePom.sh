#!/bin/bash -x

#*******************************************************************************
# Copyright (c) 2018 IBM Corporation and others.
#
# This program and the accompanying materials
# are made available under the terms of the Eclipse Public License 2.0
# which accompanies this distribution, and is available at
# https://www.eclipse.org/legal/epl-2.0/
#
# SPDX-License-Identifier: EPL-2.0
#
# Contributors:
#     Kit Lo - initial API and implementation
#*******************************************************************************

if [ $# -ne 1 ]; then
  echo USAGE: $0 env_file
  exit 1
fi

source $WORKSPACE/cje-production/scripts/common-functions.shsource
source $1

mkdir $WORKSPACE/cje-production/tmp

cd $WORKSPACE/cje-production/gitCache/eclipse.platform.releng.aggregator
mvn --update-snapshots org.eclipse.tycho:tycho-versions-plugin:1.3.0:update-pom \
  -Dmaven.repo.local=$LOCAL_REPO \
  -Djava.io.tmpdir=$WORKSPACE/cje-production/tmp \
  -DaggregatorBuild=true \
  -DbuildTimestamp=$TIMESTAMP \
  -DbuildType=$BUILD_TYPE \
  -DbuildId=$BUILD_ID \
  -Declipse-p2-repo.url=NOT_FOR_PRODUCTION_USE

RC=$?
if [[ $RC != 0 ]]
then
  echo "ERROR: tycho-versions-plugin:update-pom returned non-zero return code: $RC" >&2
else
  changes=$( git status --short -uno | cut -c4- )
  if [ -z "$changes" ]; then
    echo "INFO: No changes in pom versions" >&2
    RC=0
  else
    echo "INFO: Changes in pom versions: $changes" >&2
    RC=0
  fi
fi