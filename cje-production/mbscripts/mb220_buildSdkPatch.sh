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

cd $WORKSPACE/cje-production/gitCache/eclipse.platform.releng.aggregator
mvn clean verify -DskipTests=true -Pbree-libs \
  -Dtycho.debug.artifactcomparator \
  -Dcbi.jarsigner.continueOnFail=true \
  -Djgit.dirtyWorkingTree=error \
  -Dmaven.repo.local=$LOCAL_REPO \
  -Djava.io.tmpdir=$WORKSPACE/cje-production/tmp \
  -DaggregatorBuild=true \
  -DbuildTimestamp=$TIMESTAMP \
  -DbuildType=$BUILD_TYPE \
  -DbuildId=$BUILD_ID \
  -Declipse-p2-repo.url=NOT_FOR_PRODUCTION_USE