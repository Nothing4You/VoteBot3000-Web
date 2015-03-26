<?php
require_once('src/MySQL.class.php');

$db = new MySQL();
$dayDbFormat = date("Y-m-d");
$data = $db->Select("datensaetze",array("tag"=>$dayDbFormat));

if($data)
{

	$ort = array(1=>0,2=>0,3=>0,4=>0);
	$zeit = array(1200=>0,1215=>0,1230=>0);

	foreach($data as $vote)
	{
		if($vote['ort']>0)
		{
			if(isset($ort[$vote['ort']])) $ort[$vote['ort']]++;
			else $ort[$vote['ort']] = 1;
			
			if(isset($zeit[$vote['zeit']])) $zeit[$vote['zeit']]++;
			else $zeit[$vote['zeit']] = 1;
		}
	}

	ksort($ort);
	ksort($zeit);

	$ortDataString = implode(',',$ort);
	$zeitDataString = implode(',',$zeit);

	sort($ort);
	sort($zeit);

	$first = true;
	foreach($ort as $key=>$o)
	{
		if($first)
		{
			$value = $o;
			$ortWinner = $key;
			$first = false;
			
		}
		else
		{
			if($value==$o)
			{
				$ortUnentschieden = true;
			}
			else
			{
				$ortUnentschieden = false;
			}
			break;
		}
	}

	if($ortUnentschieden)
	{
		$ortString = "";
	}
	else 
	{
		switch($ortWinner)
		{
			case 1:
				$ortString = "in der L'Osteria";
				break;
			case 2:
				$ortString = "in der Kantine";
				break;
			case 3:
				$ortString = "in der Rohmühle";
				break;
			case 4:
				$ortString = "im Paten";
				break;
		}
	}

	$first = true;
	foreach($zeit as $key=>$z)
	{
		if($first)
		{
			$value = $z;
			$zeitWinner = $key;
			$first = false;
		}
		else
		{
			if($value==$z)
			{
				$zeitUnentschieden = true;
			}
			else
			{
				$zeitUnentschieden = false;
			}
			break;
		}
	}

	if($zeitUnentschieden)
	{
		$zeitString = "";
	}
	else 
	{
		switch($zeitWinner)
		{
			case '1200':
				$zeitString = "um 12:00 Uhr";
				break;
			case '1215':
				$zeitString = "um 12:15 Uhr";
				break;
			case '1230':
				$zeitString = "um 12:30 Uhr";
				break;
		}
	}
}
else
{
	$ortDataString = "0,0,0,0";
	$zeitDataString = "0,0,0";
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		
		<title>Heute | Webstats Mittagessen</title>
		
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>
		<script src="js/jquery.min.js"></script>
		<script src="js/Chart.min.js"></script>
		<link rel="stylesheet" href="css/style.css" />
	</head>
	<body>
		<div class="only-chart-container">
			<div class="title">
				Mittagessen am <?php echo date("d.m.Y"); ?>
			</div>
			<div class="canvases">
				<canvas id="today" class="chart"></canvas>
				<canvas id="when" class="chart"></canvas>
			</div>
			<div class="title">
			</div>
		</div>
		<script>
			var barData = {
				labels: ["L'Osteria","Kantine","Rohmühle","Der Pate"],
				datasets: [
					{
						label: "Heute",
			            fillColor: "rgba(151,187,205,0.5)",
			            strokeColor: "rgba(151,187,205,0.8)",
			            highlightFill: "rgba(151,187,205,0.75)",
			            highlightStroke: "rgba(151,187,205,1)",
						data: [<?php echo $ortDataString; ?>]
					}           
				]
			};
			
			var options = {
					scaleGridLineColor: "rgba(255,255,255,0.1)"
			}
		
			var todayChartWhere = new Chart($('#today').get(0).getContext("2d")).Bar(barData, options);
			
			var barData = {
					labels: ["12:00","12:15","12:30"],
					datasets: [
						{
							label: "Heute",
				            fillColor: "rgba(151,187,205,0.5)",
				            strokeColor: "rgba(151,187,205,0.8)",
				            highlightFill: "rgba(151,187,205,0.75)",
				            highlightStroke: "rgba(151,187,205,1)",
							data: [<?php echo $zeitDataString; ?>]
						}           
					]
				};
				
				var options = {
						scaleGridLineColor: "rgba(255,255,255,0.1)"
				}
			
				var todayChartWhen = new Chart($('#when').get(0).getContext("2d")).Bar(barData, options);
		</script>
		
		
	</body>
</html>