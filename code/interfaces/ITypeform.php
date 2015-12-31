<?php

interface ITypeform
{
    
    public function getTypeformUid();

    public function getLastTypeformImportedTimestamp();

    public function updateLastTypeformImportedTimestamp();

    public function updateTypeformStats($stats);
}
