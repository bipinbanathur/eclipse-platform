#!/usr/bin/env bash
#*******************************************************************************
# Copyright (c) 2016 IBM Corporation and others.
#
# This program and the accompanying materials
# are made available under the terms of the Eclipse Public License 2.0
# which accompanies this distribution, and is available at
# https://www.eclipse.org/legal/epl-2.0/
#
# SPDX-License-Identifier: EPL-2.0
#
# Contributors:
#     David Williams - initial API and implementation
#*******************************************************************************

export DROP_ID=$1
DL_LABEL=$2
REPO_SITE_SEGMENT=$3
HIDE_SITE=$4

function usage ()
{
  printf "\n\tUsage: %s DROP_ID DL_LABEL REPO_SITE_SEGMENT HIDE_SITE" $(basename $0) >&2
  printf "\n\t\t%s\t%s" "DROP_ID " "such as I20121031-2000." >&2
  printf "\n\t\t%s\t%s" "DL_LABEL " "such as 4.4M3." >&2
  printf "\n\t\t%s\t%s" "REPO_SITE_SEGMENT " "such as 4.4milestones, 4.4, etc." >&2
  printf "\n\t\t%s\t%s" "HIDE_SITE " "true or false." >&2
}

if [[ -z "${DROP_ID}" || -z "${DL_LABEL}" || -z "${REPO_SITE_SEGMENT}" || -z "${HIDE_SITE}" ]]
then
  printf "\n\n\t%s\n\n" "ERROR: arguments missing in call to $( basename $0 )." >&2
  usage
  exit 1
fi


DL_SITE_ID=${DL_TYPE}-${DL_LABEL}-${BUILD_TIMESTAMP}

BUILDMACHINE_SITE=${BUILDMACHINE_BASE_SITE}/${DROP_ID}

export DLMACHINE_BASE_SITE=/home/data/httpd/download.eclipse.org/eclipse/updates/${REPO_SITE_SEGMENT}
# just in case first time
echo -e "\n\tJust in case first time, we will 'mkdir -p ' for \n\t$DLMACHINE_BASE_SITE\n"
mkdir -p $DLMACHINE_BASE_SITE

export DLMACHINE_SITE=${DLMACHINE_BASE_SITE}/${DL_SITE_ID}
# just in case first time
echo -e "\n\tJust in case first time, we will 'mkdir -p ' for \n\t$DLMACHINE_SITE\n"
mkdir -p $DLMACHINE_SITE

source ${PROMOTE_IMPL}/promoteUtilities.shsource
# Better to use "new" copy to find Eclipse?
#findEclipseExe ${DL_SITE_ID}
# or "old, existing one? (I think "old" one, since in theory we would not have to copy that part of site to new area.
findEclipseExe ${DROP_ID}
RC=$?
if [[ $RC == 0 ]]
then
  ${PROMOTE_IMPL}/addRepoProperties.sh ${BUILDMACHINE_SITE} ${REPO_SITE_SEGMENT} ${DL_SITE_ID}
else
  echo "ERROR: could not run add repo properties."
  exit $RC
fi

source ${PROMOTE_IMPL}/createXZ.shsource
createXZ ${BUILDMACHINE_SITE}
RC=$?
if [[ $RC != 0 ]]
then
  echo "ERROR: could not create XZ compressed metadata. Return code: $RC."
  exit $RC
fi

printf "\n\t%s\n" "rsync build machine repo site, to downloads repo site."
# remember, need trailing slash since going from existing directories
# contents to new directories contents
rsync -vr "${BUILDMACHINE_SITE}/"  "${DLMACHINE_SITE}"


