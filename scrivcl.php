#!/usr/bin/env php
<?php
/**
 * @file scrivfilecl.php
 * Commandline conversion
 */

// Terminate immediately unless invoked as a command line script
if (PHP_SAPI != 'cli') {
  die('scrivcl must be run from the command line.');
}


require_once('scrivmsg.class.php');
require_once('scrivprocess.class.php');


$filetype = $argv[3]; // 'word' or 'scrivener'

$ScrivProcess = new ScrivProcess($filetype);

$docName = $argv[4];
$author = $argv[5];
$ScrivProcess->doc_title = $docName;
$ScrivProcess->doc_author = $author;

$convertpath = $argv[2];
// TODO make the directory if it does not exist?
$ScrivProcess->set_converted_path($convertpath);

// Path to the file being converted
$filepath = $argv[1];
$ScrivProcess->convert_file($filepath);


// Copy the scrivstrap.js file to the converted directory.
$file = './js/scrivstrap.js';
$oldumask = umask(0);
mkdir($convertpath.'/js', 0777);
umask($oldumask);

if (!copy($file, $convertpath.'/js/scrivstrap.js')) {
  echo "failed to copy $file...\n\n";
}

// Copy the docboot.css file to the converted directory.
$file = './css/docboot.css';
$oldumask = umask(0);
mkdir($convertpath.'/css', 0777);
umask($oldumask);

if (!copy($file, $convertpath.'/css/docboot.css')) {
  echo "failed to copy $file...\n\n";
}


echo"done";