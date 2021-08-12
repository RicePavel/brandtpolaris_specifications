<?php

set_time_limit(1000);

// PHP Simple HTML DOM Parser
require('simple_html_dom.php');
require('PHPExcel.php');

$bom = "\xEF\xBB\xBF";
$header = "Название в Brandpolaris;Цена на Brandpolaris;Модельный год\n";
$tofile = $header;

// rzr ------------------------------------------------------------------------------------------------------------------------------


// rzr 2021
/*
$result = file_get_contents('https://www.brandtpolaris.ru/technique/rzr/new/');
$html = str_get_html($result);
$array = getParsingResult($html, '2021');
$resultArray = $array['resultArray'];
$headArray = $array['headArray'];
*/
$headArray = [];
$headArray[] = 'name';
$headArray[] = 'price';
$resultArray = getParsingResultNew('https://www.brandtpolaris.ru/technique/rzr/new/', '2021', $headArray);
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/rzr/new/', '2020', $headArray));

$sheet = array(
    $headArray
);

foreach ($resultArray as $row) {
	$rowArray = array();
	foreach($headArray as $specName) {
		$rowArray[] = $row[$specName];
	}
	$sheet[] = $rowArray;
}

$doc = new PHPExcel();
$doc->setActiveSheetIndex(0);
$doc->getActiveSheet()->fromArray($sheet, null, 'A1');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="your_name.xls"');
header('Cache-Control: max-age=0');
$writer = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
$writer->save('test.xls');




//$html->clear(); 

function getParsingResult($html, $year) {
	$headArray = [];
	$headArray[] = 'name';
	$headArray[] = 'price';

	$resultArray = [];
	foreach($html->find('ul.models li') as $element) {
		$name = '';
		$price = '';
		$newArray = [];
		foreach($element->find('a.header_name_item') as $a) {
			$name = $year . " " . trim($a->title);
			$href = $a->href;
			$newArray['name'] = $name;
			$detailResult = file_get_contents('https://www.brandtpolaris.ru' . $href);
			$detailHtml = str_get_html($detailResult);
			foreach($detailHtml->find('table.table_char tr') as $tr) {
				$classes = explode(' ', $tr->getAttribute('class'));
				if (!in_array("table_title", $classes)) {
					$tr_1 = $tr->find('td')[0];
					$tr_2 = $tr->find('td')[1];
					$specName = $tr_1->plaintext;
					$specValue = $tr_2->plaintext;
					if (!in_array($specName, $headArray)) {
						$headArray[] = $specName;
					}
					$newArray[$specName] = $specValue;
				} 
			}
			$detailHtml->clear(); 
			unset($detailHtml);
		}
		foreach($element->find('span.cost strong') as $span) {
			$price = $span->plaintext;
			$price = mb_substr($price, 0, mb_strlen($price) - 2);
		}
		$newArray['price'] = $price;
		$resultArray[] = $newArray;
	}
	
	return ['resultArray' => $resultArray, 'headArray' => $headArray];;
}



function getParsingResultNew($url, $year, &$headArray) {
	$result = file_get_contents($url);
	$html = str_get_html($result);

	$resultArray = [];
	foreach($html->find('ul.models li') as $element) {
		$name = '';
		$price = '';
		$newArray = [];
		foreach($element->find('a.header_name_item') as $a) {
			$name = $year . " " . trim($a->title);
			$href = $a->href;
			$newArray['name'] = $name;
			$detailResult = file_get_contents('https://www.brandtpolaris.ru' . $href);
			$detailHtml = str_get_html($detailResult);
			foreach($detailHtml->find('table.table_char tr') as $tr) {
				$classes = explode(' ', $tr->getAttribute('class'));
				if (!in_array("table_title", $classes)) {
					$tr_1 = $tr->find('td')[0];
					$tr_2 = $tr->find('td')[1];
					$specName = $tr_1->plaintext;
					$specValue = $tr_2->plaintext;
					if (!in_array($specName, $headArray)) {
						$headArray[] = $specName;
					}
					$newArray[$specName] = $specValue;
				} 
			}
			$detailHtml->clear(); 
			unset($detailHtml);
		}
		foreach($element->find('span.cost strong') as $span) {
			$price = $span->plaintext;
			$price = mb_substr($price, 0, mb_strlen($price) - 2);
		}
		$newArray['price'] = $price;
		$resultArray[] = $newArray;
	}
	$html->clear(); 
	
	return $resultArray;
}


unset($html);
return;