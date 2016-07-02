<?php

SortByMedal('medal_results.tsv');


function SortByMedal($file) {
	$tsv  = array();
	$fp   = fopen($file, "r");
	 
	while (($data = fgetcsv($fp, 0, "\t")) !== FALSE) {
	  $tsv[] = $data;
	}
	fclose($fp);

	//国名、金メダル、銀メダル、銅メダル、それぞれの配列を作成
	foreach($tsv as $key=>$row) {
		$country[$key] = $row[0];
		$gold[$key] = $row[1];
		$silver[$key] = $row[2];
		$bronze[$key] =  $row[3];
	}

	//array_multisortで配列並び替え
	array_multisort($gold, SORT_DESC, $silver, SORT_DESC, $bronze, SORT_DESC, $country, SORT_ASC, $tsv);

	$fileName = 'medal_results_sorted.tsv';

	header('Content-Type: application/x-csv');
	header("Content-Disposition: attachment; filename=$fileName");

	$fp = fopen('php://output', 'w');

	//並び替えた配列をmedal_results_sorted.tsvに出力
	foreach ($tsv as $row) {
	    fputcsv($fp, $row, "\t");
	}

	fclose($fp);
}


