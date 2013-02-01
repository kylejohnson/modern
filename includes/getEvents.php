<?php
require_once '../includes/config.php';

$is_graph_mode = empty($_REQUEST['is_graph_mode']) ? false : true;

if($is_graph_mode) require 'getEventsGraph.php';
else require 'getEventsThumbs.php';