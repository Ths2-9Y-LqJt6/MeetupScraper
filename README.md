# MeetupEvents.php

A PHP scraper class for getting a multi-dimensional array of events for a given group.
This is likely very fragile, as all HTML scrapers are ;) If you want more stability, Meetup
has an API: https://www.meetup.com/meetup_api/
 
This plugin requires php-xml: https://www.php.net/manual/en/dom.installation.php


## Sample Code

Here we pass in `synshop` into the `get_future_meetup_events()` method to get the events future from the [SYN Shop](https://www.meetup.com/synshop/events/) group and then loop through the resulting `$events` array printing out the `human_date` and `title` and then stop after 6 events:

```php
<?php
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
```

## Event Elements

The script returns and array of events. Each event has the following key value pairs:

* link
* title
* epoch
* human_date
* description

Here's a `var_dump()` of a sample event:

```
Array
(
    [link] => https://www.meetup.com/synshop/events/bfhxflyzmbvb/
    [title] => Paid Members Only:  Danger Room Tools and Shop Orientation
    [epoch] => 1568685600000
    [human_date] => Mon, Sep 16, 7:00 PM
    [description] => Tonight we cover the major rules in the Danger room, and the Major Rules for safety in the danger room.
)
```