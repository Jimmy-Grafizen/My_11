<?php

require 'PHPExcel.php';
require_once 'PHPExcel/IOFactory.php';
$path = "file.xls";
$objPHPExcel = PHPExcel_IOFactory::load($path);
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    echo $worksheetTitle = $worksheet->getTitle();
}