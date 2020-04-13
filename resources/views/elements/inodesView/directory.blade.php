@extends('omen::elements.inodesView.inode')


@section('classType'. $id, 'mdi-folder')

@if($id == 'root')

@section('figureType'.$id, 'figureDirectory Root')
@section('name'. $id, __('omen::Parent'))
@section('path'. $id, $path)

@else

@section('figureType'.$id, 'figureDirectory')
@section('name'. $id, $inode->getName())
@section('path'. $id, base64_encode($inode->getFullPath()))

@section('date'. $id, $inode->getDateFormated())
@section('visibility'. $id, $inode->getVisibility())

@endif
