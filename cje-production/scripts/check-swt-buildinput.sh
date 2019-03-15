#!/bin/bash
#*******************************************************************************
# Copyright (c) 2017 IBM Corporation and others.
#
# This program and the accompanying materials
# are made available under the terms of the Eclipse Public License 2.0
# which accompanies this distribution, and is available at
# https://www.eclipse.org/legal/epl-2.0/
#
# SPDX-License-Identifier: EPL-2.0
# 
# Contributors:
#     Sravan Lakkimsetti - initial API and implementation
#*******************************************************************************

if [ $# -ne 1 ]; then
  echo USAGE: $0 env_file
  exit 1
fi

if [ ! -r "$1" ]; then
  echo "$1" cannot be read
  echo USAGE: $0 env_file
  exit 1
fi

source "$1"

pushd $BUILD_ROOT/gitCache/eclipse.platform.releng.aggregator/eclipse.platform.swt
commit=$(git log --pretty=oneline -1|cut -d' ' -f2-)
popd

if [[ $commit == v[0-9][0-9][0-9][0-9]* ]]; then
  echo "SWT build input successful"
  exit 0
else
  echo "SWT build input failed"
  exit 1
fi
