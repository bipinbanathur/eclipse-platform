<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
//ini_set("display_errors", "true");
//error_reporting (E_ALL);
include_once("utilityFunctions.php");
include ('buildproperties.php');
include ('testConfigs.php');

if (array_key_exists("SERVER_NAME", $_SERVER)) {
  $servername = $_SERVER["SERVER_NAME"];
  if ($servername === "build.eclipse.org") {
    $imagesource="http://download.eclipse.org/eclipse.org-common/themes/Phoenix/images";
    $csssource="http://download.eclipse.org/eclipse.org-common//themes/Phoenix/css";
    $appsource="/home/data/httpd/download.eclipse.org/eclipse.org-common/system";
    $clickthroughstr="";
  }
  else {
    $imagesource="../../../eclipse.org-common/stylesheets";
    $csssource="../../../eclipse.org-common/stylesheets";
    $appsource="../../../eclipse.org-common/system";
    $clickthroughstr="download.php?dropFile=";

  }
}
else {
  $servername = "localhost";
  $imagesource="http://download.eclipse.org/eclipse.org-common/themes/Phoenix/images";
  $csssource="http://download.eclipse.org/eclipse.org-common//themes/Phoenix/css";
  $appsource="NONE";
  $clickthroughstr="";
}

echo "<head>".PHP_EOL;

echo "<title>Test Results for $BUILD_ID</title>".PHP_EOL;
?>



    <style type="text/css">
      <!--
      P {text-indent: 30pt; margin: inherit}
      tr:hover td { background-color: #CCCCFF; }
      -->
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="Eclipse Foundation, Inc." />
    <meta name="keywords" content="eclipse,project,plug-ins,plugins,java,ide,swt,refactoring,free java ide,tools,platform,open source,development environment,development,ide" />
    <link rel="stylesheet" type="text/css" href="<?php echo $csssource;?>/visual.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo $csssource;?>/layout.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo $csssource;?>/print.css" media="print" />
<script type="text/javascript">
//<![CDATA[
  sfHover = function() {
    var sfEls = document.getElementById("leftnav").getElementsByTagName("li");
    for (var i=0; i<sfEls.length; i++) {
      sfEls[i].onmouseover=function() {
        this.className+=" sfhover";
      }
      sfEls[i].onmouseout=function() {
        this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
      }
    }
  }
if (window.attachEvent) window.attachEvent("onload", sfHover);
//]]>
  </script>
</head>
<body>

  <?php if (! isset ($BUILD_FAILED) ) { ?>

  <div id="leftcol">
    <ul id="leftnav">
      <li><a href="#Logs">Logs</a></li>
      <li><a href="#UnitTest">Unit Test Results</a></li>
      <li><a href="#PluginsErrors">Plugins Containing Compile Errors</a></li>

    </ul>

  </div>
  <!-- end 'not build failed' -->

<?php }

echo "<div id=\"midcolumn\">".PHP_EOL;

echo "<h2>Test Results for <a href=\"../".$BUILD_ID."\">".$BUILD_ID."</a></h2>".PHP_EOL;
echo "<div class=\"homeitem3col\">".PHP_EOL;
echo "<h3 id=\"Logs\"> Logs for <a href=\"../".$BUILD_ID."\">".$BUILD_ID."</a></h3>".PHP_EOL;
echo "<ul>";


if (file_exists("buildlogs/reporeports/index.html")) {

?>
        <li>
        <a href="buildlogs/reporeports/index.html"><b> Repository Reports </b></a>
        </li>
        <?php }
           if (file_exists("buildlogs/errors-and-moderate_warnings.html")) {
         ?>
        <li>
        <a href="buildlogs/errors-and-moderate_warnings.html"><b> Repository Reports (Experimental) </b></a>
        </li>
        <?php } ?>

        <li>
        <a href="logs.php#javadoc"><b> Javadoc Logs </b></a>
        </li>
        <li> <a href="logs.php#console"><b> Console Output Logs </b></a>
        </li>
        <li> <a href="buildlogs.php"><b>Release engineering build logs</b></a>
        </li>
<?php
  echo " <li><a href=\"apitools/analysis/html/index.html\"><b>API Tools Version Verification Report</b></a>";
  echo "  This tool verifies the versions of the plugins against Eclipse ${API_PREV_REF_LABEL}.&nbsp;&nbsp;
  Exclusions are listed in <a href=\"http://git.eclipse.org/c/platform/eclipse.platform.releng.aggregator.git/tree/eclipse.platform.releng.tychoeclipsebuilder/eclipse/apiexclude/exclude_list_external.txt?h=$BRANCH\">.../apiexclude/exclude_list_external.txt</a>.</li> ";
?>

<?php
  $deprecationFilename="apitools/deprecation/apideprecation.html";
  if (file_exists($deprecationFilename)) {
    echo " <li><a href=\"$deprecationFilename\"><b>API Tools Deprecation Report</b></a>";
    echo "  This tool generates a report for API deprecated since ${API_PREV_REF_LABEL}.</li> ";
  }
  else {
    echo "  <li>No deprecation report. Nothing deprecated since ${API_PREV_REF_LABEL}.</li>";
  }
?>

<?php
  // have removed coverage measurements for now
  // echo " <li><a href=\"coverage.php\"><b>JaCoCo code coverage report</b></a></li>";
?>

<?php
  $freezeFilename="apitools/freeze_report.html";
  if (file_exists($freezeFilename)) {
    echo "<li><a href=\"$freezeFilename\"><b>API Tools Post-API Freeze Report</b></a>&nbsp;&nbsp;";
    echo "This report describes API changes since ${API_FREEZE_REF_LABEL}.  Exclusions are listed in <a href=\"http://git.eclipse.org/c/platform/eclipse.platform.releng.aggregator.git/tree/eclipse.platform.releng.tychoeclipsebuilder/eclipse/apiexclude/exclude_list.txt?h=$BRANCH\">.../apiexclude/exclude_list.txt</a>.</li>";
  }
  else {
    echo "  <li>No freeze report. Only generated in main stream after M6.</li>";
  }
?>

<?php
  echo " <li><a href=\"apitools/apifilters-$BUILD_ID.zip\"><b>Zip of .api_filters files used in the build</b></a></li>";
?>
<?php

  echo"<li>eclipse.platform.releng.aggregator: $BRANCH (branch or hash: $EBUILDER_HASH)</li> ";
  echo"<li>org.eclipse.releng.basebuilder (only used to start unit tests): $BASEBUILDER_TAG</li> ";

?>
<?php
  echo "<li>\n";
  $generated=file_exists("performance/global_fp.php");
  if (file_exists("performance/performance.php") && $generated) {
    echo "View the <a href=\"performance/performance.php\">performance test results</a> for the current build.\n";
  } else {
    echo "Performance tests are pending.\n";
  }
  echo "</li>\n";
  echo "</ul>\n";
?>
        </div>

        <div class="homeitem3col">
<?php
  echo "<h3 id=\"UnitTest\"> Unit Test Results for <a href=\"../$BUILD_ID\">$BUILD_ID</a></h3>".PHP_EOL;
?>

          <p>The unit tests are run on the <a href="https://hudson.eclipse.org/shared/view/Eclipse%20and%20Equinox/">shared Hudson instance</a>.</p>

          <p>The table shows the unit test results for this build on the platforms
          tested. You may access the test results page specific to each
          component on a specific platform by clicking the cell link.
          Normally, the number of errors is indicated in the cell.
          A "-1" or "DNF" means the test "Did Not Finish" for unknown reasons
          and hence no results page is available. In that case,
          more information can sometimes be found in
          the <a href="logs.php#console">console logs</a>.</p>
<?php
  if (file_exists("testNotes.html")) {
    $my_file = file_get_contents("testNotes.html");
    echo $my_file;
  }
?>

<?php
  $width=90;
  $half= $width / 2;
  $ncolumns=count($expectedTestConfigs);
          /*
          unsure if 'percent' can be "real" number, or if must be integer?
          if needs to be integer, use ($a - ($a % $b)) / $b;
           */
  $colWidth=$half / $ncolumns;
  echo "<table width='".$width."%' border='1' bgcolor='#EEEEEE' rules='rows' align='center'>\n";
  echo "<tr bgcolor='#9999CC'>\n";
  echo "<th rowspan='2' width='".$half."%' align='center'> org.eclipse <br /> Test Bundles </th>\n";
  echo "<th colspan='".($ncolumns + 1)."' align='center'> Test Configurations (Hudson Job/os.ws.arch/VM) </th></tr>\n";
  echo "<tr bgcolor='#9999CC'>\n";

  foreach ($expectedTestConfigs as $column)
  {
    echo "<th width='".$colWidth."%'>". computeDisplayConfig($column) . "</th>\n";
  }
  echo "</tr>\n";
  
  if (file_exists("testResultRows.html")) {
    include "testResultsRows.html";
  } else {
    include "testResultsRowsPending.html";
  }
  echo "</table>\n";
  
?>
</div>
<?php
if (file_exists("compilerSummary.html")) {
    include "compilerSummary.html";
} else {
    include "compilerSummaryPending.html";
}
?>

      </div>
    </body>
  </html>
