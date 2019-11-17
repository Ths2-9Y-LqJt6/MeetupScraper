<?php

/**
 * Class MeetupEvents
 *
 * A scraper class for getting a multi-dimensional array of events for a given group.
 * This is likely very fragile, as all HTML scrapers are ;) If you want more stability, Meetup
 * has an API: https://www.meetup.com/meetup_api/
 *
 * This plugin requires php-xml: https://www.php.net/manual/en/dom.installation.php
 */
class MeetupEvents {

    var $cachePath;
    var $cacheAge;
    var $meetupBase;
    var $futureUri;
    var $pastUri;

    /**
     * MeetupEvents constructor.
     * @param string $cacheAge argument to pass to strtotime() - defaults to '-1 hour'
     * @param string $cachePath path to where cache files will be kept - defaults to '/tmp/'
     */
    public function __construct($cacheAge = null, $cachePath = null) {
        if ($cachePath != null){
            $this->cachePath = $cachePath;
        } else {
            $this->cachePath = '/tmp/';
        }
        if($cacheAge !== null){
            $this->cacheAge = strtotime($cacheAge);
        } else {
            $this->cacheAge = strtotime('-1 hour');
        }

        $this->meetupBase = 'https://www.meetup.com'; // no traling slash
        $this->futureUri = '/events/'; // leading and trailing slash
        $this->pastUri = '/events/past'; // leading and trailing slash
    }

    /**
     * Accepts an RSS feed URL, an age of cache and a path to store cache,
     * returns SimpleXMLElement from simplexml_load_string()
     * @param string $url
     * @param int $cacheAge in the form of epoch. to use 1 hour, do: strtotime('-1 hour')
     * @return string or boolean false
     */
    function getAndCacheUrl($url, $cacheAge, $cachePath) {
        $cacheFile = $cachePath . 'MeetupEvents_cache_' .  md5($url);

        if (!is_file($cacheFile) || filectime($cacheFile) < $cacheAge) {
            // todo - switch this to use cURL call and check for 200s and such
            $result = file_get_contents($url);
            if (is_writable($cachePath)) {
                $save_result = file_put_contents($cacheFile, serialize($result));
            } else {
                $save_result = false;
            }
            if ($result === false){
                error_log("getAndCacheUrl() can't fetch from $url - this is bad!");
            }
            if ($save_result === false) {
                error_log("getAndCacheUrl() can't write to $cacheFile - this is bad!");
            }
        } else {
            $fetched = file_get_contents($cacheFile);
            if ($fetched === false) {
                error_log("getAndCacheUrl() can't retrieve data from $cacheFile - this is bad!");
            } else {
                $result = unserialize($fetched);
            }
        }
        return $result;
    }

    /**
     * Likely a very fragile method to get future events in a multi-dimensional array. uses getAndCacheUrl()
     * @param string $group meetup group name from URL
     * @return array $events with each member being an array with keys of link, title, epoch, human_date and description
     */
    function get_future_events($group)
    {
        $url = $this->meetupBase . '/' . $group . $this->futureUri;
        $html = $this->getAndCacheUrl($url, $this->cacheAge, $this->cachePath);
        return $this->parseHtmlToArray($html);
    }
    /**
     * Likely a very fragile method to get future events in a multi-dimensional array. uses getAndCacheUrl()
     * @param $group
     * @return array $events with each member being an array with keys of link, title, epoch, human_date and description
     */
    function get_past_events($group)
    {
        $url = $this->meetupBase . '/' . $group . $this->pastUri;
        $html = $this->getAndCacheUrl($url, $this->cacheAge, $this->cachePath);
        return $this->parseHtmlToArray($html);
    }

    /**
     * Likely a very fragile method to get past events in a multi-dimensional array. uses getAndCacheUrl()
     * @param string $group meetup group name from URL
     * @return array $events with each member being an array with keys of link, title, epoch, human_date and description
     */
    function parseHtmlToArray($html)
    {
        $events = array();
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $finder = new DomXPath($dom);
        $classname = "eventCard";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

        foreach ($nodes as $node) {
            $event = array();
            foreach ($node->getElementsByTagName('a') as $link) {
            if ($link->getAttribute('class') == 'eventCardHead--title') {
            $event['link'] = $this->meetupBase . $link->getAttribute('href');
            $event['title'] = $link->nodeValue;
            break;
            }
            }
            foreach ($node->getElementsByTagName('time') as $link) {
                if ($link->getAttribute('datetime')) {
                    $event['epoch'] = $link->getAttribute('datetime');
                    $event['human_date'] = $link->nodeValue;
                }
            }
            foreach ($node->getElementsByTagName('p') as $link) {
                if ($link->getAttribute('class') == 'text--small padding--top margin--halfBottom' &&
                    trim($link->getAttribute('class')) != '' &&
                    stristr($link->getAttribute('style'), "visibility:hidden") === false
                ) {
                    $event['description'] = $link->nodeValue;
                } else {
                    $event['description'] = null;
                }
            }
            $events[$event['epoch'] . '-' . rand(100000, 888888)] = $event;
        }
        ksort($events);
        return $events;

    }
}