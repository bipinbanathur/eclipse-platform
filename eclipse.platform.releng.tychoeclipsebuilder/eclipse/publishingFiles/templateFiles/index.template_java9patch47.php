<?php
# Begin: page-specific settings.
$pageTitle    = "Eclipse Project Downloads";
$pageKeywords = "eclipse,project,plug-ins,plugins,java,ide,swt,refactoring,free java ide,tools,platform,open source,development environment,development,ide";
$pageAuthor   = "David Williams and Christopher Guindon";

//ini_set("display_errors", "true");
//error_reporting (E_ALL);

$expectedtestConfigs=0;
$testConfigs = array();

if (array_key_exists("SERVER_NAME", $_SERVER)) {
    $servername = $_SERVER["SERVER_NAME"];
    if ($servername === "build.eclipse.org") {
         $clickthroughstr="";
      }
      else {
          $clickthroughstr="download.php?dropFile=";

      }
}
else {
    $servername = "localhost";
    $clickthroughstr="";
}

include_once("buildproperties.php");
include_once("utilityFunctions.php");

// global variables
$expectedTestConfigs=array();
$testResults = array();
$testResultsSummaryFiles=array();

$streamArr = explode(".", $STREAM);
$STREAM_MAJOR = $streamArr[0];
$STREAM_MINOR = $streamArr[1];
$STREAM_SERVICE = $streamArr[2];

ob_start();

/*
DL.thin.header.php.html was original obtained from

wget https://eclipse.org/eclipse.org-common/themes/solstice/html_template/thin/header.php

and then that file modified to suit our needs.
Occasionally, our version should be compared to the "standard" to see if anything has
changed, in the interest of staying consistent.

See https://eclipse.org/eclipse.org-common/themes/solstice/docs/

 */

require("DL.thin.header.php.html");

?>


<?php if (! isset ($BUILD_FAILED) ) { ?>

<aside class="col-md-6" id="leftcol" style="margin-top:20px;" >
<ul class="ul-left-nav fa-ul hidden-print" style="text-color:black; background-color:#EFEBFF; background-size:contain; background-clip:border-box; border-color: black; font-size:12px; font-weight:bold; padding:2px; line-height:1; border-radius: 1;  margin:20px 3px 80px 3px">
<li><a href="#Repository">Eclipse p2 Repository (patch only)</a></li>
<li><a href="#ZippedRepo">Zipped repository (patch only)</a></li>
<li><a href="#JDTCORE">JDT Core Batch Compiler</a></li>
</ul>
</aside>

<!-- end 'not build failed' -->
<?php } ?>

<div>
<h1>Eclipse <?php echo $STREAM; ?> <?php echo $BUILD_TYPE_NAME; ?> Build: <?php echo $BUILD_ID; ?> </h1>
<p style="padding-bottom: 1em">This page provides access to the various deliverables of Eclipse Platform Project.</p>
<p>This page has a patch feature that provides an implementation of JDT that supports Java 9. This is an implementation
of an early-draft specification developed under the Java
Community Process (JCP) and is made available for testing and evaluation purposes
only. The code is not compatible with any specification of the JCP. For more information on our early Java 9 work,
see the <a href="https://wiki.eclipse.org/Java9">Eclipse wiki page on that topic</a>.</p>
<p>This patch is for the Neon (4.7) stream of Eclipse.</p>
<?php
if (file_exists("pom_updates/index.html")) {
  echo "<h2><a href=\"pom_updates/\">POM updates made</a></h2>";
}
// check if test build only, just to give warning of oversite.
// see bug 404545
if (isset($testbuildonly) && ($testbuildonly)) {
  echo "<h2>Test-Build-Only flag found set. Input was not tagged.<h2>\n";
}

// $NEWS_ID needs to be added to buildproperties.php, such as $NEWS_ID="4.5/M4";
// Once ready to display it.
if (isset ($NEWS_ID)) {
  echo "<a href=\"http://www.eclipse.org/eclipse/news/${NEWS_ID}/\">New and Noteworthy</a><br>\n";
}
// linkToAcknowledgements is a pure "marker file"
if (file_exists("linkToAcknowledgements")) {
  echo "<a href=\"http://www.eclipse.org/eclipse/development/acknowledgements_${BUILD_ID}.php\">Acknowledgments</a><br>\n";
}
// linkToReadme is a pure marker file
if (file_exists("linkToReadme")) {
  echo "<a href=\"http://www.eclipse.org/eclipse/development/readme_eclipse_${BUILD_ID}.php\">Eclipse Project ${BUILD_ID} Readme</a><br>\n";
}

if (isset ($BUILD_FAILED) ) {
  echo "<h2>Build Failed</h2><p>See <a href=\"buildlogs.php\">logs</a>.</p>\n";
  $PATTERN='!(.*)(/buildlogs/)(.*)!';
  $result = preg_match($PATTERN, $BUILD_FAILED, $MATCHES);
  // cheap short cut, since we expect only 1 such file
  $summaryFile=glob("buildFailed-*");
  if ($result !== FALSE) {
    $SPECIFIC_LOG=$MATCHES[3];
    echo "<p>Specifically, see <a href=\"buildlogs/$SPECIFIC_LOG\">the log with errors</a>, \n";
    echo "or a <a href=\"$summaryFile[0]\">summary</a>. <br /> \n";
    echo "Or see traditional <a href=\"testResults.php\">Compile Logs</a> (if any).</p>\n";
  }


}
else {
?>

</div>

<div id="midcolumn">

<h3>Logs and Test Links</h3>

<?php

  // build notes are put at the top of the list under the assumption if there is something
  // there, then it it pretty important for everyone to read. Such as "this build does not export" or
  // something like that.
  if (file_exists("buildnotes/")) {
      $fileArray=glob("buildnotes/buildnotes_*.html");
      if (count($fileArray) > 0) {
          echo "<li><a href=\"buildNotes.php\">View build notes for the current build.</a></li>";
      }
  }

  // for current (modern) builds, test results are always in
  // 'testresults'. That directory only exists after first results
  // have finished and been "published".
  if (file_exists("testresults")) {
    $testResultsDirName="testresults";
  } elseif (file_exists("results")) {
    $testResultsDirName="results";
  } else {
    $testResultsDirName="";
  }


  $boxes=calcTestConfigsRan($testResultsDirName);
  if ($boxes < 0 ) {
    $boxesDisplay = 0;
  } else {
    $boxesDisplay = $boxes;
  }

  //  echo "<ul class='midlist'>";
  echo "<ul>";
  //  We will always display link to logs (as normal link, not using color:inherit;)
  echo "<li>View the <a title=\"Link to logs.\" href=\"testResults.php\">logs for the current build</a>.</li>\n";

  // This section if for overall status if anything failed, overall is failed
  // -3 is special code meaning no testResults directory exists yet.
  if ($boxes == -3)   {
    $testResultsStatus = "pending";
  } else {
    /* since boxes is not -3, there must be at least one */
    $totalFailed = 0;
    $expectedBoxes = count($expectedTestConfigs);
    foreach ($expectedTestConfigs as $config) {

      if (isset($testResults[$config])) {
        $testRes = $testResults[$config];
        $failed = $testRes['failCount'];
        $totalFailed = $totalFailed + $failed;
      }
    }
    if ($totalFailed == 0 && $boxes == $expectedBoxes) {
      $testResultsStatus = "success";
    } elseif ($totalFailed == 0 && $boxes < $expectedBoxes) {
      $testResultsStatus = "inProgress";
    } elseif ($totalFailed > 0 && $boxes > 0) {
      $testResultsStatus = "failed";
    } else {
      // This is some sort of programming error?
      // Don't think we should get to here?
      // Will flag as "unknown" but not sure how to convey that ....
      // would only be useful if debugging.
      $testResultsStatus = "unknown";
    }
  }

  if (file_exists("overrideTestColor")) {
    $linkColor='text-success';
  }
  else {
    if ($testResultsStatus === "failed") {
      /* note we don't override  'inherit' cases, just 'failed'. */
      if (file_exists("overrideTestColor")) {
        $linkColor='text-success';
      } else {
        $linkColor = 'text-danger';
      }
    } elseif ($testResultsStatus === "success") {
      $linkColor='text-success';
    } elseif ($testResultsStatus === "pending") {
      $linkColor='text-muted';
    } elseif ($testResultsStatus === "inProgress") {
      $linkColor='text-muted';
    }
  }

if (! isset($PATCH_BUILD)) {
  if ($testResultsStatus == "pending")   {
    echo "<li>Integration and unit tests are pending.</li>\n";
  } else {
    echo "<li>View the <a  class=\"${linkColor}\" title=\"Link test results.\" href=\"testResults.php\">integration and unit test results for the current build.</a></li>\n";
  }

  /* performance tests line item */
  $generated=file_exists("performance/global_fp.php");
  if (file_exists("performance/performance.php") && $generated) {
    echo "<li>View the <a href=\"performance/performance.php\">performance test results</a> for the current build.</li>\n";
  } else {
    echo "<li>Performance tests are pending.</li>\n";
  }
}
  echo "</ul>\n";

if (! isset($PATCH_BUILD)) {
  echo "<h3>Summary of Unit Tests Results</h3>";
  echo "<table class=\"testTable\">\n";
  echo "<caption>\n";
  echo "<p>".$boxesDisplay." of ".count($expectedTestConfigs)." integration and unit test configurations are complete.</p> \n";
  if (file_exists("testNotes.html")) {
    $my_file = file_get_contents("testNotes.html");
    echo $my_file;
  }
  echo "</caption> \n";
  echo "<tr><th style=\"width:40%\">Tested Platform</th><th>Failed</th><th>Passed</th><th>Total</th><th>Test&nbsp;Time&nbsp;(s)</th></tr>\n";

  foreach ($expectedTestConfigs as $config) {

    if (isset($testResults[$config])) {
      $testRes = $testResults[$config];
      $failed = $testRes['failCount'];
      $passed = $testRes['passCount'];
      $total = $failed + $passed;
      $duration = $testRes['duration'];
      if (file_exists("overrideTestColor")) {
        $linkColor='text-success';
      }
      else {
        if ($failed > 0) {
          /* note we don't override  'inherit' cases, just 'failed'. */
          if (file_exists("overrideTestColor")) {
            $linkColor='text-success';
          } else {
            $linkColor = 'text-danger';
          }
        } else {
          $linkColor='text-success';
        }
      }
      echo "<tr>\n";
      echo "<td style=\"text-align:left\">\n";
      echo "<a class=\"${linkColor}\" href=\"testResults.php\">".$config."</a>";
      echo "</td>\n";
      echo "<td>$failed</td><td>$passed</td><td>$total</td><td>$duration</td>\n";
      echo "</tr>\n";
    }
    else {
      /* Yes, all configs intentionally links, since all go to the same place, but if no results yet, would not look like one. */
      $linkColor = 'text-muted';
      echo "<tr>\n";
      echo "<td style=\"text-align:left\">\n";
      echo "<a class=\"${linkColor}\" href=\"testResults.php\">".$config."</a>";
      echo "</td>\n";
      echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>\n";
      echo "</tr>\n";
    }
  }
  echo "</table>\n";

}
?>

  <h3>Related Links</h3>
  <ul class="midlist">
    <li><a href="https://www.eclipse.org/eclipse/development/plans/eclipse_project_plan_4_5.xml#target_environments">Target Platforms and Environments</a></li>
    <li><a href="directory.txt">View the Git repositories used for the current build.</a></li>
    <li><a href="gitLog.txt">Git log.</a></li>
    <li><a href="http://wiki.eclipse.org/Platform-releng/How_to_check_integrity_of_downloads">How to verify a download.</a></li>
  </ul>
<?php

  $sums512file="checksum/eclipse-$BUILD_ID-SUMSSHA512";
  $sums512file_asc=$sums512file.".asc";

  if ((file_exists($sums512file)) && (file_exists($sums512file_asc))) {
    echo "<p style=\"text-indent: 3em;\"><a href=\"$sums512file\">SHA512 Checksums for $BUILD_ID</a>&nbsp;(<a href=\"$sums512file.asc\">GPG</a>)</p>";
  } else if (file_exists($sums512file)) {
    echo "<p style=\"text-indent: 3em;\"><a href=\"$sums512file\">SHA512 Checksums for $BUILD_ID</a>";
  }
?>
<?php
  # place holder: we don't currently produce these reports, and
  # when we do, will need some work here.
  # FWIW, we may want to construct elaborate query into CGit for this,
  # even though that'd be elaborate, would get user to an area where
  # they coudl tweak query, if desired?
  if (file_exists("report.txt")) {
    echo "<p><a href=\"report.txt\">Report of changes</a> from previous build.</p>";
  }
?>

</div> <!-- end midcolumn -->

<?php
  include("dropSectionUtils.php");
  include("computeRepoURLs.php");
?>
 <!-- main download section -->
<div class="dropSection">
<h3 id="Repository">Eclipse p2 Repository&nbsp;<a href="details.html#Repository"><i class="fa fa-info-circle">&nbsp;</i></a></h3>

<?php startTable(); ?>

<?php
    $STREAM_REPO_NAME=computeSTREAM_REPO_NAME();
    $STREAM_REPO_URL=computeSTREAM_REPO_URL();
    $BUILD_REPO_NAME=computeBUILD_REPO_NAME();
    $BUILD_REPO_URL=computeBUILD_REPO_URL();
  if ((file_exists("$relativePath3/updates/".$STREAM_REPO_NAME)) || (file_exists("$relativePath4/updates/".$STREAM_REPO_NAME))) {
    echo "<tr><td> \n";
    echo "To update your Eclipse installation to this development stream, you can use the software repository at<br />\n";
    echo "&nbsp;&nbsp;<a href=\"$STREAM_REPO_URL\">$STREAM_REPO_URL</a><br />\n";
    echo "</td></tr> \n";
  }
  if ((file_exists("$relativePath3/updates/"."$BUILD_REPO_NAME")) || (file_exists("$relativePath4/updates/"."$BUILD_REPO_NAME")) ) {
    echo "<tr><td> \n";
    echo "To update your build to use this specific build, you can use the software repository at<br />\n";
    echo "&nbsp;&nbsp;<a href=\"$BUILD_REPO_URL\">$BUILD_REPO_URL</a><br />\n";
    echo "</td></tr> \n";
  }
?>
</table>

<?php if (isset($PATCH_BUILD)) { ?>
    <h3 id="ZippedRepo">Zipped Repository for offline use.</h3>
    <?php startTable(); ?>
    <tr>
       <?php columnHeads(); ?>
    </tr>
    <td><img src = "repo.gif" alt="Zipped Repo" />Patch, in zipped repo</td>
    <?php genLinks("${PATCH_BUILD}-${BUILD_ID}-repository.zip"); ?>
    </tr>
    </table>
<?php } ?>


<?php if (! isset($PATCH_BUILD)) { ?>
<h3 id="EclipseSDK">Eclipse SDK&nbsp;<a href="details.html#EclipseSDK"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>

<?php startTable(); ?>
<tr>
   <?php columnHeads(); ?>
</tr>

%sdk%

</table>

<h3 id="JUnitPlugin">Tests and Testing Framework&nbsp;<a href="details.html#JUnitPlugin"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%tests%
</table>

<h3 id="ExamplePlugins">Example Plug-ins&nbsp;<a href="details.html#ExamplePlugins"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%example%
</table>

<h3 id="RCPRuntime">RCP Runtime Binary&nbsp;<a href="details.html#RCPRuntime"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%rcpruntime%
</table>

<h3 id="RCPSDK">RCP SDK&nbsp;<a href="details.html#RCPSDK"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%rcpsdk%
</table>

<!--
<h3 id="DeltaPack">DeltaPack&nbsp;<a href="details.html#DeltaPack"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%deltapack%
</table>
-->

<h3 id="PlatformRuntime">Platform Runtime Binary&nbsp;<a href="details.html#PlatformRuntime"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%runtime%
</table>

<h3 id="JDTRuntime">JDT Runtime Binary&nbsp;<a href="details.html#JDTRuntime"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%jdt%
</table>

<h3 id="JDTSDK">JDT SDK &nbsp;<a href="details.html#JDTSDK"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%jdtsdk%
</table>

<?php } ?>

<h3 id="JDTCORE">JDT Core Batch Compiler &nbsp;<a href="details.html#JDTCORE"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%jdtc%
</table>

<?php if (! isset($PATCH_BUILD)) { ?>
<h3 id="PDERuntime">PDE Runtime Binary&nbsp;<a href="details.html#PDERuntime"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%pde%
</table>

<h3 id="PDESDK">PDE SDK&nbsp;<a href="details.html#PDESDK"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%pdesdk%
</table>

<h3 id="CVSRuntime">CVS Client Runtime Binary&nbsp;<a href="details.html#CVSRuntime"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%cvs%
</table>

<h3 id="CVSSDK">CVS Client SDK&nbsp;<a href="details.html#CVSSDK"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%cvssdk%
</table>

<h3 id="SWT">SWT Binary and Source&nbsp;<a href="details.html#SWT"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%swt%
</table>

<h3 id="org.eclipse.releng">org.eclipse.releng.tools plug-in&nbsp;<a href="details.html#org.eclipse.releng"><i class="fa fa-info-circle">&nbsp;</i></a>
</h3>
<?php startTable(); ?>
<tr>
  <?php columnHeads(); ?>
</tr>
%relengtools%
</table>
<?php } ?>
<?php } ?>
</div> <!-- end dropsection -->
</div> <!-- close div classs=container -->
</main> <!-- close main role="main" element -->
</body>
</html>
<?php
  $html = ob_get_clean();

  #echo the computed content
  echo $html;
?>

