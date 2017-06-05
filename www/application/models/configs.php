<?php

class Configs extends MY_Model 
{    
    // set table is games
    protected $_table = 'Configs';

    // set validations rules
    protected $validate = array(
    );

    protected $public_attributes = array(
        'id',
        'main_type',
        'sub_type',
        'data_type',
        'info',
        'created',
        'updated'
        );

    public function getConfig()
    {
        $key = "KEY-Config-" . date("Y-m-d H:i");
        $config = $this->memcacheInstance->get($key);
        if($config)
            return $config;
        
        $config = $this->getConfigDb();
        $this->memcacheInstance->set($key, $config, 3600);
        return $config;
    }
    
    public function getConfigDb()
    {
        $temp = $this->order_by(array("main_type" => "ASC", "sub_type" => "ASC", "info" => "ASC"))->get_all();
        $config = array();
        foreach($temp as $row)
        {
            switch($row->data_type)
            {
                case 'Numeric': $info = (int) $row->info; break;
                case 'Text': $info = $row->info; break;
                case 'JSON': $info = json_decode($row->info, true); break;
                case 'Serialize': $info = unserialize($row->info); break;
                default: $info = $row->info; 
            }            
            $config[$row->main_type][$row->sub_type][] = $info;
        }        
        return $config;
    }
    
    public function getConfigElement($main_type, $sub_type)
    {
        $key = "Key-Config-$main_type-$sub_type";
        $config = $this->memcacheInstance->get($key);
        if($config)
            return $config;
        
        $rs = $this->db->query("Select * from Configs where main_type = ? and sub_type = ? limit 1", array($main_type, $sub_type));
        if($rs->num_rows())
        {
            $config = $rs->row();
            $this->memcacheInstance->set($key, $config, 3600);
            return $config;
        }
        return NULL;
    }
        
    
    public function getFile($type)
    {
        $rs = $this->db->query("Select * from Configs where main_type = 'File' and sub_type = ? limit 1", array($type));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'File does not exist', 'statusCode' => 200);
        
        $file = $rs->row();
        return array('code' => 0, 'file' => json_decode($file->info), 'statusCode' => 200);
    }
}