<?php

class Notification
{
    private $title;
    private $topic;
    private $message;

    function __construct($title, $topic, $message)
    {
        $this->title = $title;
        $this->topic = $topic;
        $this->message = $message;
    }

    public function getNotification()
    {
        $res = array();
        $res['Notification']['title'] = $this->title;
        $res['Notification']['topic'] = $this->topic;
        $res['Notification']['message'] = $this->message;
        return $res;
    }

}