<?php

set_time_limit(1000);

// PHP Simple HTML DOM Parser
require('simple_html_dom.php');
require('PHPExcel.php');

$bom = "\xEF\xBB\xBF";
$header = "Название в Brandpolaris;Цена на Brandpolaris;Модельный год\n";
$tofile = $header;

$models = array();
$handle = fopen("models.txt", "r");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        $models[] = trim($buffer);
    }
    if (!feof($handle)) {
        echo "Ошибка: fgets() неожиданно потерпел неудачу\n";
    }
    fclose($handle);
}

//var_dump($models);

// rzr ------------------------------------------------------------------------------------------------------------------------------
 
$headArray = [];
$headArray[] = 'name';
$headArray[] = 'price';
$headArray[] = 'image';
$resultArray = getParsingResultNew('https://www.brandtpolaris.ru/technique/rzr/new/', '2021', $headArray, $models);
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/rzr/2020/', '2020', $headArray, $models));

// ranger 
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/ranger/new/', '2021', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/ranger/2020/', '2020', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/ranger/2019/', '2019', $headArray, $models));

// general
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/general/new/', '2021', $headArray, $models));

// квадроциклы
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/atv/new/', '2021', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/atv/2020/', '2020', $headArray, $models));
//$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/atv/2014/', '2014', $headArray, $models));

// ace
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/ace/new1/', '2021', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/ace/2017/', '2017', $headArray, $models));

// снегоходы 
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/new/', '2022', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/2021/', '2021', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/2020/', '2020', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/2019/', '2019', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/2018/', '2018', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/2017/', '2017', $headArray, $models));
$resultArray = array_merge($resultArray, getParsingResultNew('https://www.brandtpolaris.ru/technique/snowmobile/2015/', '2015', $headArray, $models));

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
$writer->save('specifications.xls');




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



function getParsingResultNew($url, $year, &$headArray, $models) {
	$result = file_get_contents($url);
	$html = str_get_html($result);

	$resultArray = [];
	foreach($html->find('ul.models li') as $element) {
		$name = '';
		$price = '';
		$image = '';
		$newArray = [];
		$a = $element->find('a.header_name_item')[0];
			$name = trim($year . " " . trim($a->title));
			
			//echo '<br/>';
			//var_dump($name);
			//echo '<br/>';
			
			if (!in_array($name, $models)) {
				continue;
			}
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
				
				$imgElement = $detailHtml->find('.promo img');
				if ($imgElement) {
					$image = 'https://www.brandtpolaris.ru' . $imgElement[0]->src;
				}
				
				$detailHtml->clear(); 
				unset($detailHtml);
			
		
		foreach($element->find('span.cost strong') as $span) {
			$price = $span->plaintext;
			$price = mb_substr($price, 0, mb_strlen($price) - 2);
		}
		$newArray['price'] = $price;
		$newArray['image'] = $image;
		$resultArray[] = $newArray;
	}
	$html->clear(); 
	
	return $resultArray;
}


unset($html);
return;