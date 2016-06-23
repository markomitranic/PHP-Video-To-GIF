<?php

// Author Marko Mitranic
// https://github.com/markomitranic/
// version 0.1 | 24.06.2016
// Requires ffmpeg to be installed on the system. Requires LIBASS add-on
// brew install ffmpeg --with-libass

if (isset($_GET['video']) && isset($_GET['captionID'])) {
	echo createGifFromVideo($_GET['video'], $_GET['captionID']); // video filename, caption number
	die;
} else {
	die('false');
}



// This function is dangerous!!!
// All gifs need to be deleted first.
// It can take over 15 minutes for it to finish.
// Only way to track the progress is to check if DIE is echoed.
// recreateALL();
function recreateALL() {
	$json_url = "json.json";
	$json = file_get_contents($json_url);
	$data = json_decode($json, TRUE);

	foreach ($data as $key => $value) {
		$inner = $key;
		foreach ($data[$key] as $key => $value) {
			createGifFromVideo($inner, $key);
		}
	}
	echo 'DIE';
}


// PROCEDURAL LIBRARY STARTS HERE

function createGifFromVideo($video, $captionID) {

	$gif = $video . '_' . $captionID . '.gif';


	if (!file_exists('build/' . $gif)) {
		$videoWithCaption = addCaption($video, getCaption($video, $captionID));
		$gifSrc = videoToGif($videoWithCaption, $gif);
		return 'build/'. $gifSrc; // Print the new gif url
	} else {
		return 'build/' . $gif;
	}
}

// FFMPEG address in system
function ffmpeg($command) {
	return '/usr/local/bin/ffmpeg ' . $command;
}


// Small video name without extension and the caption ID
function videoToGif($video, $gif) {

	// ffmpeg -i small.mp4 -b 2048k small.gif
	shell_exec(ffmpeg('-i ' . $video . ' build/' . $gif)); // Create GIF from Video
	unlink($video);

	return $gif;
}

function getCaption($video, $id) {
	$json_url = "json.json";
	$json = file_get_contents($json_url);
	$data = json_decode($json, TRUE);

	return $data[$video][$id];
}

function addCaption($video, $caption) {
	$subtitle = createSubtitle($caption);
	$newVideo = str_replace(".ass", ".mov", $subtitle);

	// ffmpeg -i video.avi -vf subtitles=subtitle.srt out.avi
	$output = shell_exec(ffmpeg('-i assets/'. $video .'.mov -vf ass='. $subtitle .' '. $newVideo .' 2>&1'));
	unlink($subtitle);

	return $newVideo;
}

function createSubtitle($text) {
	// Set up the basics
	$name = uniqid() . '.ass';
	$path = 'build/tmp/';

	// Check if temporary file exists.
	if (file_exists($path . $name)) {
		$name = uniqid();
	}

	// Copy subtitle template
	copy('assets/subtitle.ass', $path . $name);

	$file = fopen($path . $name, 'a');
	    fwrite($file, $text);
	fclose($file);
	chmod($path . $name, 0644);

	// Return the subtitle name so we can delete the file later
	return $path . $name;
}