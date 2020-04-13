@php
$topLevel = true;
if(! empty(array_filter(explode('/', $path)))) $topLevel = false;
$path = dirname($path);
@endphp

@if(!$topLevel)
@include(sprintf('omen::elements.inodesView.%s', 'directory'), ['id' => 'root', 'path' => $path, 'inodeType' => 'directory'])
@endif

@foreach($inodes as $fullPath => $inode)
@include(sprintf('omen::elements.inodesView.%s', $inode->getType()), ['id' => sha1($loop->count.$fullPath), 'inodeType' => $inode->getType()])
@endforeach
