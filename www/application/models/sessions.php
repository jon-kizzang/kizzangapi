<?php

class Sessions extends MY_Model 
{    
    // set table is games
    protected $_table = 'Sessions';

    // set validations rules
    protected $validate = array(
    );

    protected $public_attributes = array(
        'id',
        'device_id',
        'player_data',
        'session_data',        
        'created',
        'updated'
        );

    public function add($id, $device_id, $data = array())
    {        
        $template = array("player_data" => "", "session_data" => "");
        $data['id'] = $id;
        $data['device_id'] = $device_id;
        $rec = array_merge($template, $data);
        $exists = $this->get_by(array('id' => $id));
        if(!$exists)
            $this->insert($rec);
        
    }
    
    public function updateData($id, $field, $data)
    {
        if($field == "device_id")
            $rec = array($field => $data);
        else
        {
            $data['token'] = $id;
            $rec = array($field => json_encode($data));        
        }
        
        $this->update($id, $rec, false);        
        
        //Now update cached
        if($this->db->affected_rows())
        {
            switch ($field)
            {
                case 'player_data': $this->memcacheInstance->set("KEY-Player-Data-$id", $data, 0); break;
                case 'session_data': $this->memcacheInstance->set("KEY-Session-Data-$id", $data, 0); break;
            }
            return 1;
        }
        return 0;
    }
    
    public function destroy($id)
    {
        $this->db->query("Delete from Sessions where id = ?", array($id));
        $this->memcacheInstance->delete("KEY-Player-Data-$id");
        $this->memcacheInstance->delete("KEY-Session-Data-$id");
    }
    
    public function getSessionData($id)
    {
        $key = "KEY-Session-Data-$id";
        $data = $this->memcacheInstance->get($key);
        if($data)
            return $data;
        
        $data = $this->get_by(array("id" => $id));
        if(is_object($data))        
        {
            $this->memcacheInstance->set($key, json_decode($data->session_data, true), 0);        
            return json_decode($data->session_data, true);
        }
        return null;
    }
        
    public function getPlayerData($id)
    {
        $key = "KEY-Player-Data-$id";
        $data = $this->memcacheInstance->get($key);
        if($data)
            return $data;
        
        $data = $this->get_by(array("id" => $id));
        if(is_object($data))
        {
            $this->memcacheInstance->set($key, json_decode($data->player_data, true), 0);
            return json_decode($data->player_data, true);
        }        
        return null;
    }        
}