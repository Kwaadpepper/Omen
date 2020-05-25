@extends('omen::elements.inodesView.inode')

@php
$name = $inode->getName();
$extension = $inode->getExtension();
$extensionClass = sprintf('ext-%s', $inode->getFileType());

if(empty($name)) {
$name = ".$extension";
$extension = 'hidden';
$extensionClass .= ' dotStartFile';
}

$inodeFileTypeMdiClass = [
null => 'mdi-folder-outline',
'archive' => 'mdi-zip-box-outline',
'video' => 'mdi-file-video-outline',
'audio' => 'mdi-file-image-outline',
'image' => 'mdi-file-image-outline',
'pdf' => 'mdi-file-pdf-outline',
'text' => 'mdi-note-text-outline',
'file' => 'mdi-file-outline',
'writer' => 'mdi-file-word-outline',
'calc' => 'mdi-file-excel-outline',
'impress' => 'mdi-file-powerpoint-outline',
'diskimage' => 'mdi-disc',
'executable' => 'mdi-console'
];

$mediaElementMimeTypeSupport = [
// Video support
'.ogv' => 'video/ogg',
'.mp4' => 'video/mp4',
'.webm' => 'video/webm',
'.ogv' => 'video/ogv',

// Audio support
'.mp3' => 'audio/mp3',
'.oga' => 'audio/oga',
'.ogg' => 'audio/ogg',
'.wav' => 'audio/wav',

// For HLS support
'.m3u8' => 'applicationx-mpegURL',
'.m3u8' => 'vnd.apple.mpegURL',
'.ts' => 'video/MP2T',

// For M(PEG)-DASH support
'application/dash+xml' => '.mpd'
];

$view = false;

if(in_array($inode->getFileType(), [
'image', 'text', 'pdf', 'writer', 'calc', 'impress'
])) $view = true;

// Contain a supported media element (sound or video)
if(!empty(array_intersect($inode->getPossibleMimeTypesFromFileName(), $mediaElementMimeTypeSupport))) $view = true;

@endphp

@section('figureType'.$id, 'figureFile')

@section('name'. $id, $name)

@section('extension'. $id, $extension)

@section('extensionClass' . $id, "$extensionClass draggable-source")

@section('classType'. $id, $inodeFileTypeMdiClass[$inode->getFileType()] ?? 'mdi-file-outline')

@section('path'. $id, base64_encode($inode->getFullPath()))

@section('size'. $id, $inode->getSize())
@section('date'. $id, $inode->getDateFormated())
@section('visibility'. $id, $inode->getVisibility())
