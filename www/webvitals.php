<?php
// Copyright 2020 Catchpoint Systems Inc.
// Use of this source code is governed by the Polyform Shield 1.0.0 license that can be
// found in the LICENSE.md file.
//$REDIRECT_HTTPS = true;
include 'common.inc';

$headless = false;
if (GetSetting('headless')) {
    $headless = true;
}
// load the secret key (if there is one)
$secret = GetServerSecret();
if (!isset($secret))
    $secret = '';
$url = '';
if (isset($req_url)) {
  $url = htmlspecialchars($req_url);
}
$placeholder = 'Enter a Website URL';
$profile_file = __DIR__ . '/settings/profiles_webvitals.ini';
if (file_exists(__DIR__ . '/settings/common/profiles_webvitals.ini'))
  $profile_file = __DIR__ . '/settings/common/profiles_webvitals.ini';
if (file_exists(__DIR__ . '/settings/server/profiles_webvitals.ini'))
  $profile_file = __DIR__ . '/settings/server/profiles_webvitals.ini';
$profiles = parse_ini_file($profile_file, true);
?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>WebPageTest - Website Performance and Optimization Test</title>
        <?php $gaTemplate = 'Main'; include ('head.inc');?>
        <style>
        #vitals-content {
          width: 100%;
        }
        #test_box-container {
          margin-bottom: 2em;
        }
        </style>
    </head>
    <body class="home">
            <?php
            $tab = 'Home';
            include 'header.inc';
            if (!$headless) {
            ?>
            <h1 class="attention">Run a Core Web Vitals Test</h1>

            <form name="urlEntry" id="urlEntry" action="/runtest.php" method="POST" enctype="multipart/form-data" onsubmit="return ValidateInput(this)">

            <?php
            echo '<input type="hidden" name="vo" value="' . htmlspecialchars($owner) . "\">\n";
            if( strlen($secret) ){
              $hashStr = $secret;
              $hashStr .= $_SERVER['HTTP_USER_AGENT'];
              $hashStr .= $owner;

              $now = gmdate('c');
              echo "<input type=\"hidden\" name=\"vd\" value=\"$now\">\n";
              $hashStr .= $now;

              $hmac = sha1($hashStr);
              echo "<input type=\"hidden\" name=\"vh\" value=\"$hmac\">\n";
            }
            ?>


            <div id="test_box-container">
                <ul class="ui-tabs-nav">
                    <li class="analytical_review">
                      <a href="/">
                        <?php echo file_get_contents('./images/icon-advanced-testing.svg'); ?>Advanced Testing</a>
                    </li>
                    <li class="vitals ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
                      <a href="#">
                        <?php echo file_get_contents('./images/icon-webvitals-testing.svg'); ?>Web Vitals</a>
                    </li>
                    <li class="easy_mode">
                      <a href="/easy">
                        <?php echo file_get_contents('./images/icon-simple-testing.svg'); ?>Simple Testing</a>
                    </li>
                    <li class="visual_comparison">
                      <a href="/video/">
                        <?php echo file_get_contents('./images/icon-visual-comparison.svg'); ?>Visual Comparison
                      </a></li>
                    <li class="traceroute">
                      <a href="/traceroute">
                        <?php echo file_get_contents('./images/icon-traceroute.svg'); ?>Traceroute
                      </a></li>
                </ul>
                <div id="analytical-review" class="test_box">
                    <ul class="input_fields">
                        <li>
                        <label for="url" class="vis-hidden">Enter URL to test</label>
                        <?php
                            if (isset($_REQUEST['url']) && strlen($_REQUEST['url'])) {
                                echo "<input type='text' name='url' id='url' inputmode='url' placeholder='$placeholder' value='$url' class='text large' autocorrect='off' autocapitalize='off' onkeypress='if (event.keyCode == 32) {return false;}'>";
                            } else {
                                echo "<input type='text' name='url' id='url' inputmode='url' placeholder='$placeholder' class='text large' autocorrect='off' autocapitalize='off' onkeypress='if (event.keyCode == 32) {return false;}'>";
                            }
                        ?>
                            <input type="submit" name="submit" value="Start Test &#8594;" class="start_test">
                      </li>
                        <li>
                            <label for="webvital_profile">Test Configuration:</label>
                            <select name="webvital_profile" id="webvital_profile" onchange="profileChanged()">
                                <?php
                                if (isset($profiles) && count($profiles)) {
                                  foreach($profiles as $name => $profile) {
                                    $selected = '';
                                    if ($name == $_COOKIE['wvProfile'])
                                      $selected = 'selected';
                                    echo "<option value=\"$name\" $selected>{$profile['label']}</option>";
                                  }
                                  if (isset($lastGroup))
                                      echo "</optgroup>";
                                }
                                ?>
                            </select>
                        </li>
                        <li id="description"></li>
                    </ul>
                </div>
            </div>


            </form>

            <?php
            } // $headless
            ?>
          <iframe id="vitals-content" frameBorder="0" scrolling="no" height="4050" src="https://www.product.webpagetest.org/second"></iframe>
          <?php
          //include(__DIR__ . '/include/home-subsections.inc');
          //include('footer.inc'); 
          ?>
        </div>
        <?php
        if (!isset($site_js_loaded) || !$site_js_loaded) {
          echo "<script type=\"text/javascript\" src=\"{$GLOBALS['cdnPath']}/js/site.js?v=" . VER_JS . "\"></script>\n";
          $hasJquery = true;
        }
        ?>

        <script type="text/javascript">
        <?php
          echo "var profiles = " . json_encode($profiles) . ";\n";
        ?>
        var wptStorage = window.localStorage || {};

        var profileChanged = function() {
          var sel = document.getElementById("profile");
          var txt = document.getElementById("description");
          var profile = sel.options[sel.selectedIndex].value;
          var description = "";
          if (profiles[profile] !== undefined) {
            var d = new Date();
            d.setTime(d.getTime() + (365*24*60*60*1000));
            document.cookie = "wvProfile=" + profile + ";" + "expires=" + d.toUTCString() + ";path=/";
            if (profiles[profile]['description'] !== undefined)
              description = profiles[profile]['description'];
          }
          txt.innerHTML = description;
        };
        profileChanged();
        </script>
        <script type="text/javascript" src="<?php echo $GLOBALS['cdnPath']; ?>/js/test.js?v=<?php echo VER_JS_TEST;?>"></script>
    </body>
</html>
