<?php

if (isset($_GET['video']) && isset($_GET['captionID'])) {
	echo createGifFromVideo($_GET['video'], $_GET['captionID']); // video filename, caption number
	die;
} else {
	die('false');
}



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
	$newVideo = str_replace(".srt", ".mov", $subtitle);

	// ffmpeg -i video.avi -vf subtitles=subtitle.srt out.avi
	shell_exec(ffmpeg('-i assets/'. $video .'.mov -vf subtitles='. $subtitle .' '. $newVideo));
	unlink($subtitle);

	return $newVideo;
}

function createSubtitle($text) {
	// Set up the basics
	$name = uniqid() . '.srt';
	$path = 'build/tmp/';

	// Check if temporary file exists.
	if (file_exists($path . $name)) {
		$name = uniqid();
	}

	// Copy subtitle template
	copy('assets/title.srt', $path . $name);

	$file = fopen($path . $name, 'a');
	    fwrite($file, $text);
	fclose($file);
	chmod($path . $name, 0644);

	// Return the subtitle name so we can delete the file later
	return $path . $name;
}