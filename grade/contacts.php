<?php

require_once "../lib/header.php";
require_once "misc.php";
use Goutte\Client;

line_out("Grading PHP-Intro Contacts Application");

$url = getUrl('http://www.php-intro.com/exam/mid-f13-contacts');
//$url = getUrl('');
$grade = 0;

error_log("Contacts ".$url);
line_out("Retrieving ".htmlent_utf8($url)."...");
flush();

$client = new Client();

$crawler = $client->request('GET', $url);

// Yes, one gigantic unindented try/catch block
$passed = 0;
$titlepassed = true;
try {

$html = $crawler->html();
togglePre("Show retrieved page",$html);

$retval = checkTitle($crawler);
if ( $retval !== true ) {
    error_out($retval);
    $titlepassed = false;
}

line_out("Looking for Add New link.");
$link = $crawler->selectLink('Add New')->link();
$url = $link->getURI();
line_out("Retrieving ".htmlent_utf8($url)."...");

$crawler = $client->request('GET', $url);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;

// Add new fail
line_out("Looking for the form with a 'Add New' submit button");
$form = $crawler->selectButton('Add New')->form();
line_out("-- this autograder expects the form field names to be:");
line_out("-- name, address, phone, and zip");
line_out("-- if your fields do not match these, the next tests will fail.");
line_out("Causing Add error, leaving phone and zip blank.");
$form->setValues(array("name" => "Sarah", "address" => "Anytown", "phone" => "", "zip" => ""));
$crawler = $client->submit($form);
$passed++;

$html = $crawler->html();
togglePre("Show retrieved page",$html);
checkPostRedirect($client);

line_out("Expecting 'Bad value for name, address, phone or zip'");
if ( strpos(strtolower($html), 'bad value') !== false ) {
    $passed++;
} else {
    error_out("Could not find 'Bad value for name, address, phone or zip'");
}

line_out("Looking for Add New link.");
$link = $crawler->selectLink('Add New')->link();
$url = $link->getURI();
line_out("Retrieving ".htmlent_utf8($url)."...");

$crawler = $client->request('GET', $url);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;
line_out("Looking for the form with a 'Add New' submit button");
$form = $crawler->selectButton('Add New')->form();
$name = 'Sarah'.sprintf("%03d",rand(1,100));
$address = rand(1,100);
$phone = rand(1,100);
line_out("Entering name=$name, address=$address, phone=$phone");
$form->setValues(array("name" => $name, "address" => $address, "phone" => $phone, "zip" => "12345"));
$crawler = $client->submit($form);
$passed++;

$html = $crawler->html();
togglePre("Show retrieved page",$html);
checkPostRedirect($client);

line_out("Looking '$name' entry");
$pos = strpos($html, $name);
$pos2 = strpos($html, "edit.php", $pos);
$body = substr($html,$pos,$pos2-$pos);
# echo "body=",htmlentities($body);
line_out("Looking for address=$address and phone=$phone");
if ( strpos($body,''.$address) < 1 || strpos($body,''.$phone) < 1 ) {
    error_out("Could not find address=$address and phone=$phone");
} else {
    $passed++;
}

line_out("Looking for edit.php link associated with '$name' entry");
$pos3 = strpos($html, '"', $pos2);
$editlink = substr($html,$pos2,$pos3-$pos2);
line_out("Retrieving ".htmlent_utf8($editlink)."...");

$crawler = $client->request('GET', $editlink);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;

line_out("Looking for the form with a 'Update' submit button");
$form = $crawler->selectButton('Update')->form();
$address = rand(1,100);
$phone = rand(1,100);
line_out("Editing name=$name, address=$address, phone=$phone");
$form->setValues(array("name" => $name, "address" => $address, "phone" => $phone, "zip" => "12345"));
$crawler = $client->submit($form);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;
checkPostRedirect($client);

// Delete...
line_out("Looking '$name' entry");
$pos = strpos($html, $name);
$pos2 = strpos($html, "delete.php", $pos);
$body = substr($html,$pos,$pos2-$pos);
# echo "body=",htmlentities($body);
line_out("Looking for address=$address and phone=$phone");
if ( strpos($body,''.$address) < 1 || strpos($body,''.$phone) < 1 ) {
    error_out("Could not find address=$address and phone=$phone");
} else {
    $passed++;
}

line_out("Looking for delete.php link associated with '$name' entry");
$pos3 = strpos($html, '"', $pos2);
$editlink = substr($html,$pos2,$pos3-$pos2);
line_out("Retrieving ".htmlent_utf8($editlink)."...");

$crawler = $client->request('GET', $editlink);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;

// Do the Delete
line_out("Looking for the form with a 'Delete' submit button");
$form = $crawler->selectButton('Delete')->form();
$crawler = $client->submit($form);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;
checkPostRedirect($client);

line_out("Making sure '$name' has been deleted");
if ( strpos($html,$name) > 0 ) {
    error_out("Entry '$name' not deleted");
} else {
    $passed++;
}

line_out("Cleaning up old Sarah records...");
while (True ) {
    $pos = strpos($html, 'Sarah');
    if ( $pos < 1 ) break;
    $pos2 = strpos($html, "delete.php", $pos);
    if ( $pos2 < 1 ) break;
    $pos3 = strpos($html, '"', $pos2);
    if ( $pos3 < 1 ) break;
    $editlink = substr($html,$pos2,$pos3-$pos2);
    line_out("Retrieving ".htmlent_utf8($editlink)."...");

    $crawler = $client->request('GET', $editlink);
    $html = $crawler->html();
    togglePre("Show retrieved page",$html);
    $passed++;

    // Do the Delete
    line_out("Looking for the form with a 'Delete' submit button");
    $form = $crawler->selectButton('Delete')->form();
    $crawler = $client->submit($form);
    $html = $crawler->html();
    togglePre("Show retrieved page",$html);
    $passed++;
    checkPostRedirect($client);
}

line_out("Testing for HTML injection (proper use of htmlentities)...");
line_out("Looking for Add New link.");
$link = $crawler->selectLink('Add New')->link();
$url = $link->getURI();
line_out("Retrieving ".htmlent_utf8($url)."...");

$crawler = $client->request('GET', $url);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;

line_out("Looking for the form with a 'Add New' submit button");
$form = $crawler->selectButton('Add New')->form();
$name = '<b>Sarah</b>'.sprintf("%03d",rand(1,100));
$address = rand(1,100);
$phone = rand(1,100);
line_out("Entering name=$name, address=$address, phone=$phone");
$form->setValues(array("name" => $name, "address" => $address, "phone" => $phone, "zip" => "12345"));
$crawler = $client->submit($form);
$passed++;

$html = $crawler->html();
togglePre("Show retrieved page",$html);
checkPostRedirect($client);

if ( strpos($html, ">Sarah") > 0 ) {
    error_out("Found HTML Injection");
    throw new Exception("Found HTML Injection");
} else if ( strpos($html, "&gt;Sarah") > 0 ) {
    $passed+=2;
    line_out("Passed HTML Injection test");
} else {
    error_out("Cannot find name on page");
}

$pos = strpos($html,"Sarah");
$pos2 = strpos($html, "delete.php", $pos);
line_out("Looking for delete.php link associated with 'Sarah' entry");
$pos3 = strpos($html, '"', $pos2);
$editlink = substr($html,$pos2,$pos3-$pos2);
line_out("Retrieving ".htmlent_utf8($editlink)."...");

$crawler = $client->request('GET', $editlink);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;

if ( strpos($html, ">Sarah") > 0 ) {
    error_out("Found HTML Injection");
    throw new Exception("Found HTML Injection");
} else if ( strpos($html, "&gt;Sarah") > 0 ) {
    $passed+=2;
    line_out("Passed HTML Injection test");
} else {
    error_out("Cannot find name on page");
}

// $passed+=2;

line_out("Looking for the form with a 'Delete' submit button");
$form = $crawler->selectButton('Delete')->form();
$crawler = $client->submit($form);
$html = $crawler->html();
togglePre("Show retrieved page",$html);
$passed++;
checkPostRedirect($client);


} catch (Exception $ex) {
    error_out("The autograder did not find something it was looking for in your HTML - test ended.");
    error_log($ex->getMessage());
    error_log($ex->getTraceAsString());
    $detail = "This indicates the source code line where the test stopped.\n" .
        "It may not make any sense without looking at the source code for the test.\n".
        'Caught exception: '.$ex->getMessage()."\n".$ex->getTraceAsString()."\n";
    togglePre("Internal error detail.",$detail);
}

$perfect = 26;
$score = $passed * (1.0 / $perfect);
if ( $score < 0 ) $score = 0;
if ( $score > 1 ) $score = 1;
$scorestr = "Score = $score ($passed/$perfect)";
if ( $penalty === false ) {
    line_out("Score = $score ($passed/$perfect)");
} else {
    $score = $score * (1.0 - $penalty);
    line_out("Score = $score ($passed/$perfect) penalty=$penalty");
}

if ( ! $titlepassed ) {
    error_out("These pages do not have proper titles so this grade is not official");
    return;
}

if ( $score > 0.0 ) testPassed($score);

