<?php

class Push
{
    private $id_user;
    private $id_room;
    private $message;

    function __construct($id_user, $id_room,  $message)
    {
        $this->id_user = $id_user;
        $this->id_room = $id_room;
        $this->message = $message;
    }

    public function getUserMessage()
    {
        $res = array();
        $res['data']['id_user'] = $this->id_user;
        $res['data']['id_room'] = $this->id_room;
        $res['data']['message'] = $this->message;
        return $res;
    }

    public function getDoctorMessage()
    {
        $res = array();
        $res['data']['id_doctor'] = $this->id_user;
        $res['data']['id_room'] = $this->id_room;
        $res['data']['message'] = $this->message;
        return $res;
    }
}