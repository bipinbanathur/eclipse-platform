#!/bin/bash -x

#*******************************************************************************
# Copyright (c) 2019 IBM Corporation and others.
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

source $CJE_ROOT/scripts/common-functions.shsource
source $1
source $CJE_ROOT/scripts/build-functions.shsource

if [ -z $PATCH_BUILD ]; then
  fn-gather-sdk $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
  fn-gather-platform $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
  fn-gather-swt-zips $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
  fn-gather-test-zips $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
fi
fn-gather-repo $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
fn-gather-ecj-jars $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
fn-gather-buildnotes $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
fn-gather-artifactcomparisons $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory
if [ -z $PATCH_BUILD ]; then
  launcherJar=$(fn-basebuilder-launcher $CJE_ROOT/$BASEBUILDER_DIR)
  fn-slice-repos $BUILD_ID $CJE_ROOT/$AGG_DIR $buildDirectory $launcherJar
fi
