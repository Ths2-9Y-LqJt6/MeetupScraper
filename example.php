<?php

print "<h2>future</h2>";
require_once('MeetupEvents.php');
$meetup = new MeetupEvents();
$events = $meetup->get_future_events('synshop');
$count = 1;
foreach ( $events as $event){
        print "<div class='date'>{$event['human_date']}</div>";
        print "<div class='event'>{$event['title']}</div>";
        $count++;
        if ($count > 6) break;
}

print "<h2>past</h2>";
$events = $meetup->get_past_events('synshop');
$count = 1;
foreach ( $events as $event){
        print "<div class='date'>{$event['human_date']}</div>";
        print "<div class='event'>{$event['title']}</div>";
        $count++;
        if ($count > 6) break;
}

