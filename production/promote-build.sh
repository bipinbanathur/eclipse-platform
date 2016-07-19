#!/usr/bin/env bash
#*******************************************************************************
# Copyright (c) 2016 IBM Corporation and others.
# All rights reserved. This program and the accompanying materials
# are made available under the terms of the Eclipse Public License v1.0
# which accompanies this distribution, and is available at
# http://www.eclipse.org/legal/epl-v10.html
#
# Contributors:
#     David Williams - initial API and implementation
#*******************************************************************************

# Utility to trigger the promotion of build. This utility just creates
# a file to be executed by cron job. The actual promotion is done by files
# in sdk directory of build machine. This "cron job approach" is required since
# a different user id must promote things to "downloads". The promotion scripts
# also trigger the unit tests on Hudson.

function usage ()
{
  printf "\n\n\t%s\n" "promote-build.sh env_file"
}

source "$1" 2>/dev/null
# To allow this cron job to work from hudson, or traditional crontab
if [[ -z "${WORKSPACE}" ]]
then
  export UTILITIES_HOME=/shared/eclipse
else
  export UTILITIES_HOME=${WORKSPACE}/utilities/production
fi

#TODO: Should we make use of "UTILITIES_HOME" here?
if [[ -z ${SCRIPT_PATH} ]]
then
  SCRIPT_PATH=${PWD}
fi
echo -e "\n\t[DEBUG] SCRIPT_PATH in promote-build.sh: $SCRIPT_PATH"
source $SCRIPT_PATH/build-functions.shsource

# the cron job must know about and use the queueLocation
# to look for its promotions scripts. (i.e. implicit tight coupling)
queueLocation=/shared/eclipse/promotion/queue



# directory should normally exist -- best to create first, with committer's ID --
# but in case not
mkdir -p "${queueLocation}"
#env > env.txt

if [[ -z ${STREAM} || -z ${BUILD_ID} ]]
then
  echo "ERROR: This script requires STREAM and BUILD_ID"
  exit 1
fi

scriptName=promote-${STREAM}-${BUILD_ID}.sh
if [[ "${testbuildonly}" == "true" ]]
then
  # allows the "test" creation of promotion script, but, not have it "seen" be cron job
  scriptName=TEST-$scriptName
fi

# if EBUILDER_HASH is not defined, assume master, so order of following parameters are maintained.
if [[ -z "${EBUILDER_HASH}" ]]
then
  EBUILDER_HASH=master
fi

# Here is command for promotion:

${UTILITIES_HOME}/sdk/promotion/syncDropLocation.sh $STREAM $BUILD_ID $EBUILDER_HASH $BUILD_ENV_FILE

# we do not promote equinox, if BUILD_FAILED since no need.
# we also do not promote if Patch build or Y-build or experimental (since, to date, those are not "for" equinox). 
if [[ -z "${BUILD_FAILED}" &&  $BUILD_TYPE =~ [IMN] ]]
then

  # the cron job must know about and use this same
  # location to look for its promotions scripts. (i.e. tight coupling)
  promoteScriptLocationEquinox=/shared/eclipse/equinox/promotion/queue

  # Directory should normally exist -- best to create with committer's ID before hand,
  # but in case not.
  mkdir -p "${promoteScriptLocationEquinox}"

  equinoxPostingDirectory="$BUILD_ROOT/siteDir/equinox/drops"
  eqFromDir=${equinoxPostingDirectory}/${BUILD_ID}
  eqToDir="/home/data/httpd/download.eclipse.org/equinox/drops/"

  # Note: for proper mirroring at Eclipse, we probably do not want or need to
  # maintain "times" on build machine, but let them take times at time of copying.
  # If it turns out to be important to maintain times (such as ran more than once,
  # to pick up a "more" output, such as test results, then add -t to rsync
  # Similarly, if download server is set up right, it will end up with the
  # correct permissions, but if not, we may need to set some permissions first,
  # then use -p on rsync

  # Here is content of promotion script (note, use same ptimestamp created above):
  echo "#!/usr/bin/env bash" >  ${promoteScriptLocationEquinox}/${scriptName}
  echo "# promotion script created at $ptimestamp" >  ${promoteScriptLocationEquinox}/${scriptName}
  echo "rsync --times --omit-dir-times --recursive \"${eqFromDir}\" \"${eqToDir}\"" >> ${promoteScriptLocationEquinox}/${scriptName}

  # we restrict "others" rights for a bit more security or safety from accidents
  chmod -v ug=rwx,o-rwx ${promoteScriptLocationEquinox}/${scriptName}

else
  echo "Did not create promote script for equinox since BUILD_FAILED"
fi

echo "normal exit from promote phase of $(basename $0)"

exit 0

