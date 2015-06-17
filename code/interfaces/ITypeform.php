<?php

interface ITypeform {
	
	function getTypeformUid();

	function getLastTypeformImportedTimestamp();

	function updateLastTypeformImportedTimestamp();

	function updateTypeformStats($stats);
}