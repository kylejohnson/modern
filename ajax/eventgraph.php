<?php
$definterval = array(
	'event'=>1,
	'5min'=>300,
	'1hr'=>3600,
	'1day'=>86400,
);
$defeventtype = array(
	'frames'=>array('Frames','E.Frames'),
	'score'=>array('TotScore','E.TotScore'),
	'duration'=>array('Length','E.Length'),
);
$defgraphlayout = array('xy'=>1,'yx'=>1);

if(isset($_POST['monitorid'],$_POST['smktime'],$_POST['emktime'],$_POST['interval'],
		 $_POST['eventtype'],$_POST['yaxistype'],$_POST['ystart'],$_POST['yend']) 
&& !empty($_POST['monitorid']) && ctype_digit($_POST['monitorid'])
&& !empty($_POST['smktime']) && ctype_digit($_POST['smktime'])
&& !empty($_POST['emktime']) && ctype_digit($_POST['emktime'])
&& !empty($definterval[$_POST['interval']])
&& !empty($defeventtype[$_POST['eventtype']][0])
&& (	 empty($_POST['yaxistype'])
	||	($_POST['yaxistype']=='><' && !empty($_POST['ystart']) && ctype_digit($_POST['ystart']) && !empty($_POST['yend']) && ctype_digit($_POST['yend']))
	||	($_POST['yaxistype']=='>' && !empty($_POST['ystart']) && ctype_digit($_POST['ystart']))
	||	($_POST['yaxistype']=='<' && !empty($_POST['ystart']) && ctype_digit($_POST['ystart']))
	||	($_POST['yaxistype']=='=' && !empty($_POST['ystart']) && ctype_digit($_POST['ystart']))
	)
&& !empty($defgraphlayout[$_POST['graphlayout']])

) {
		
	$monitorid = (int)$_POST['monitorid'];
	$interval = $_POST['interval'];
	$eventtype = $_POST['eventtype'];
	$smktime = (int)$_POST['smktime'];
	$emktime = (int)$_POST['emktime'];
	$yaxistype = $_POST['yaxistype'];
	$ystart = (int)$_POST['ystart'];
	$yend = (int)$_POST['yend'];
	$graphlayout = $_POST['graphlayout'];
	
	if($smktime>$emktime) $emktime=$smktime;
	elseif(($emktime-$smktime)>86400) $interval = '1day';
	
	//yaxis criteria
	$where = '';
	if(	!empty($yaxistype) ) {
		if($yaxistype=='><') {
			if($ystart > $yend) list($ystart, $yend) = array($yend, $ystart);
			$where = ' AND '.$defeventtype[$eventtype][1].' BETWEEN '.$ystart.' AND '.$yend;
		}
		elseif($yaxistype=='>') $where = ' AND '.$defeventtype[$eventtype][1].'>'.$ystart;
		elseif($yaxistype=='<') $where = ' AND '.$defeventtype[$eventtype][1].'<'.$ystart;
		elseif($yaxistype=='=') $where = ' AND '.$defeventtype[$eventtype][1].'='.$ystart;
	}
	
//echo "$sdate $edate $stime $etime $interval $smktime $emktime";
	if($interval == 'event')
		$sql = "SELECT 'single' as type, E.StartTime,E.Frames,E.Length,E.TotScore,E.Id as eventid,E.Width,E.Height,M.DefaultScale,
					 unix_timestamp(E.StartTime)*1000 AS jstime
				  FROM Monitors M 
				 INNER JOIN Events E ON (M.Id = E.MonitorId)
				 WHERE M.Id=$monitorid
				   AND unix_timestamp(E.StartTime) BETWEEN $smktime AND $emktime $where
				 ORDER BY jstime";
	else {
		$divider = $definterval[$interval];

		$sql = "SELECT 'block' as type,E.StartTime,COUNT(E.Id) AS EventCount,group_concat(E.Id) as eventid,
					 group_concat(E.Width) as Width,group_concat(E.Height) as Height,M.DefaultScale,
					 SUM(E.Frames) as Frames,SUM(E.Length) as Length,SUM(E.TotScore) as TotScore,
					 FLOOR(unix_timestamp(E.StartTime)/($divider)) AS block
				  FROM Monitors M 
				 INNER JOIN Events E ON (M.Id = E.MonitorId)
				 WHERE M.Id=$monitorid $where
				 GROUP BY block";
	}
	$eventdetail = $daterange = $data = $datapoint = '';
	foreach( dbFetchAll( $sql ) as $event ) {
		if(!isset($event['jstime'])) $event['jstime'] = $event['block']*$divider*1000;
		$event['mktime'] = $event['jstime']/1000;
		if($graphlayout=='xy')
			$data .= '['.$event['jstime'].', '.$event[$defeventtype[$eventtype][0]].'],';
		else 
			$data .= '['.$event[$defeventtype[$eventtype][0]].', '.$event['jstime'].'],';
		
		if($event['type']=='single') {
			$datapoint .= '['.$event['jstime'].', '.$event[$defeventtype[$eventtype][0]].'],';
			$scale = max( reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE ), SCALE_BASE );
			$eventdetail .= '['.$event['eventid'].','.reScale( $event['Width'], $scale ).','.reScale( $event['Height'], $scale ).'],';
		} else {
			$e_ids = explode(',',$event['eventid']);
			$e_hs = explode(',',$event['Height']);
			$e_ws = explode(',',$event['Width']);
			foreach($e_ids as $k=>$id) {
				$datapoint .= '['.$event['jstime'].', '.$event[$defeventtype[$eventtype][0]].'],';
				$scale = max( reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE ), SCALE_BASE );
				$eventdetail .= '['.$id.','.reScale( $e_ws[$k], $scale ).','.reScale( $e_hs[$k], $scale ).'],';
			}
			$mydate = date('Y-m-d',$event['mktime']);
			switch($interval) {
				case '1day': {
					$myinterval = '1hr';
					$mystime = $event['mktime'];
					$myetime = $event['mktime']+86399;
					break;
				}
				case '1hr': {
					$myinterval = 'event';
					$mystime = $event['mktime'];
					$myetime = $event['mktime']+3599;
					break;
				}
				case '5min': {
					$myinterval = 'event';
					$mystime = strtotime($mydate.' '.date('H',$event['mktime']).':00');
					$myetime = strtotime($mydate.' '.date('H',$event['mktime']).':59:59');
					break;
				}
			}
			$daterange .= '["'.$myinterval.'","'.$mystime.'","'.$myetime.'"],';
		}
	}
	$eventdetail = '['.substr($eventdetail,0,-1).']';
	$daterange = '['.substr($daterange,0,-1).']';
	$datapoint = '['.substr($datapoint,0,-1).']';
	$data = '['.substr($data,0,-1).']';
	
	
	//$axismin = ($smktime-$definterval[$interval])*1000;
	//$axismax = ($emktime+$definterval[$interval])*1000;
	$axismin = ($smktime-(($emktime-$smktime)/60))*1000;
	$axismax = ($emktime+(($emktime-$smktime)/60))*1000;
	$type = $interval=='event' ? 'monitor' : 'graph';
	$color = $interval=='event' ? 'blue' : 'red';
	list($ticksize,$format,$jsformat) = tickformatter($smktime,$emktime);


	
	
	
	
	
	
	
	
	echo '
	{
		"griddata": {
			//"label": "test (EU27)",
			"data": '.$data.',
			"color": "'.$color.'",
			// user defined
			"customdefined": {
				"details" : {
					"stime": "'.$smktime.'",
					"etime": "'.$emktime.'",
					"orientation": "'.$graphlayout.'",
					"interval": "'.$interval.'",
					"type": "'.$type.'",
					"event_type": "'.$eventtype.'",
					"event_operator": "'.$yaxistype.'",
					"event_start": "'.$ystart.'",
					"event_end": "'.$yend.'",
					"axismin": "'.$axismin.'",
					"axismax": "'.$axismax.'"
				},
				"eventdetail": '.$eventdetail.',
				"datapoint": '.$datapoint.',
				"daterange": '.$daterange.'
			}
		},
		"options": {
			"'.($graphlayout=='xy' ? 'xaxis' : 'yaxis').'": { 
				"mode":"time", 
				"tickSize": ['.$ticksize.'],
				"min": "'.$axismin.'",
				"max": "'.$axismax.'"
				//,"tickFormatter": "'.$format.'"
				,"tickFormatter": function (val, axis) {

//alert(val);
                                                //m = new Date();
//alert(m.getTimezoneOffset());
                                                //val = val + m.getTimezoneOffset() * 6000;
                                                //val += -8*3600*1000;
						var d = new Date();
						localTime = d.getTime();
						localOffset = d.getTimezoneOffset() * 60000;
						val += '.date('Z').'*1000 + localOffset;

//alert(val);
						var d = new Date(val);
						return d.format("'.$jsformat.'");
						//return d.getUTCDate() + "/" + (d.getUTCMonth() + 1);
 					}


			}
			,"'.($graphlayout=='xy' ? 'yaxis' : 'xaxis').'" : { "mode":null }
			,"grid": { "hoverable": true, "clickable": true }
			,"selection": { "mode": "'.($graphlayout=='xy' ? 'x' : 'y').'" }
			 ,"series": {
				"lines": { "show": false },
				"points": { "show": true },
				"bars": {
					"show": true,
					"lineWidth": 2,
					"fill": true,
					"horizontal": '.($graphlayout=='xy' ? 'false' : 'true').'
				}
			}
		}
	}
	';
	exit;

}


function tickformatter($stime,$etime) {
	$diff = $etime - $stime;
	
	if($diff <= 3600) { //1 hour
		$ticksize = '5,"minute"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 10800) { //3 hrs
		$ticksize = '15,"minute"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 21600) { //6 hrs
		$ticksize = '30,"minute"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 43200) { //12 hrs
		$ticksize = '1,"hour"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 86400) { //24 hrs
		$ticksize = '2,"hour"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 172800) { //48 hrs
		$ticksize = '4,"hour"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 345600) { //96 hrs
		$ticksize = '8,"hour"';
		$format = '%H:%M';
		$jsformat = 'H:m';
	} elseif($diff <= 691200) { //8 days
		$ticksize = '16,"hour"';
		$format = '%m/%d<br>%H:%M';
		$jsformat = 'M/d<br>H:m';
	} elseif($diff <= 1382400) { //16 days
		$ticksize = '1,"day"';
		$format = '%y-%m-%d';
		$jsformat = 'y-M-d';
	} else {
		$ticksize =  ceil($diff/24/3600/16).',"day"';
		$format = '%y-%m-%d';
		$jsformat = 'y-M-d';
	}
	
	return array($ticksize,$format,$jsformat);
	
	//$timeformat = $interval=='1day' ? '%y-%m-%d' : '%H:%M';
	//$timeformat = $interval=='1day' ? '%m/%d<br>%H:%M' : '%H:%M';
	//$timeformat = '%m/%d<br>%H:%M';
	
	$type = $interval=='event' ? 'monitor' : 'graph';
	$color = $interval=='event' ? 'blue' : 'red';
	
	//$ticksize = (ceil(($emktime-$smktime)/3600)*5).',"minute"';
	//$ticksize = (ceil(($xmax-$xmin)/3600000)*5).',"minute"';
	$diff = $emktime-$smktime;
	//if($diff <= 14400) $ticksize = '15,"minute"'; //4 hour
	//elseif($diff <= 86400) $ticksize = '1,"hour"'; //1 day
	//elseif($diff <= 5616000) $ticksize = '1,"day"'; //31 days
	//else $ticksize = '1,"month"';
	
	if($diff <= 3600) $ticksize = '1,"minute"';
	else $ticksize = ($diff/62/3600).',"hour"';

}

