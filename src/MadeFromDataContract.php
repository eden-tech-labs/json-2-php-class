<?php


namespace App\Tools\ETL;


interface MadeFromDataContract
{
    static function make($data);
}