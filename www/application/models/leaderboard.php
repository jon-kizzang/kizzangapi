<?php

class LeaderBoard extends MY_Model {
    
    protected $_table = 'LeaderBoards';
    
    // set validations rules
    protected $validate = array(
        'leaderboardId' => array( 
            'field' => 'leaderboardId', 
            'label' => 'leaderboardId',
            'rules' => 'required|greater_than[0]'
        ),
        'imageURL' => array( 
            'field' => 'imageURL', 
            'label' => 'imageURL',
            'rules' => 'required'
        ),
        'location' => array( 
            'field' => 'location', 
            'label' => 'location',
            'rules' => 'required'
        ),
        'screenName' => array( 
            'field' => 'screenName', 
            'label' => 'screenName',
            'rules' => 'required'
        ),
        'prize' => array( 
            'field' => 'prize', 
            'label' => 'prize',
            'rules' => 'required'
        ),
    );
    
    public function getMenus()
    {
        $menus = array();
        $rs = $this->db->query("Select game_type, game_sub_type, max(endDate) as endDate 
            from GameLeaderBoards group by game_type, game_sub_type order by game_type, game_sub_type");
        $count = $rs->num_rows();
        if($rs->num_rows())        
            foreach($rs->result() as $row)            
                $menus[$row->game_type][] = $row->game_sub_type . "," . date("m/d/y", strtotime($row->endDate));                
            
        $code = 0;
        $statusCode = 200;
        return compact('code','count','menus','statusCode');
    }

    public function getByType($type, $sub_type, $playerId)
    {
        $ret = array();
        
        if($sub_type)
        {
            if($sub_type == "ROAL")
                $direction = "DESC";
            else
                $direction = "ASC";
            
            $person = $this->db->query("Select * from GameLeaderBoards where game_type = ? and game_sub_type = ? and player_id = ? order by place $direction limit 1", array($type, $sub_type, $playerId));
            $rs = $this->db->query("Select * from GameLeaderBoards where game_type = ? and game_sub_type = ? order by place $direction limit 100", array($type, $sub_type));
        }
        else
        {
            $person = $this->db->query("Select * from GameLeaderBoards where game_type = ? and player_id = ? order by game_sub_type, place limit 1", array($type, $playerId));
            $rs = $this->db->query("Select * from GameLeaderBoards where game_type = ? order by game_sub_type, place limit 100", array($type));
        }
        if($rs->num_rows())
        {
            $ret['code'] = 0;
            if($person->num_rows())
            {
                $ret['count'] = $rs->num_rows() + 1;
                $ret['places'][] = $person->row();
                foreach($rs->result() as $row)
                    $ret['places'][] = $row;
            }
            else
            {
                $ret['count'] = $rs->num_rows();
                $ret['places'] = $rs->result();
            }
            $ret['statusCode'] = 200;
        }
        
        if(count($ret))
            return $ret;
        
        return array('code' => 1, 'message' => "Leaderboard has no records or isn't supported.", 'statusCode' => 200);
    }
    
    /**
     * create leaderboard
     * @param int $numberOfWinners
     */
    public function add( $numberOfWinners ) {

        $result = array( 'code' => 1, 'message' => 'Cannot create Leader Board', 'statusCode' => 400 );

        if ( (int)$numberOfWinners <= 1 ) {

            $error = array( 'code' => 2, 'message' => 'Number Of Winners must is a numeric and greater than zero', 'statusCode' => 400 );

            return $error;
        }

        $leaderBoards = array();

        for ( $index = 1; $index  <= $numberOfWinners; $index ++ ) { 
            
            $leaderBoard['prize'] = NULL;

            array_push( $leaderBoards, $leaderBoard );
        }

        if ( ! empty( $leaderBoards ) ) {

            $this->db->truncate( $this->_table );

            $insertId = $this->db->insert_batch( $this->_table, $leaderBoards );
            $errorMessage = $this->db->_error_message();

            if ( ! $errorMessage ) {

                $result = $this->getAll( $numberOfWinners );
            }
            else {

                log_message( 'message', 'Insert leader Board ' . $errorMessage );
                $result = array( 'code' => 3, 'message' => $errorMessage, 'statusCode' => 400 );
            }
        }

        return $result;
    }

    /**
     * update leaderboard by id
     * @param  int $id
     * @param  array $data
     * @return object or array
     */
    public function edit( $id, $data ) {

        if ( ! is_numeric($id) || $id <= 0 ) {

            $error = array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );

            return $error;
        }

        if ( empty( $data ) ) {

            // return log errors when data miss/ invalid
            $errors =  array( 'code' => 2, 'message' => 'Please the required enter data', 'statusCode' => 400 );

            return $errors;
        } 
        else {

            // reset errors messages
            $this->form_validation->reset_validation();

            // set data for all field to validation
            $this->form_validation->set_params( $data );

            // set rules validation
            $this->form_validation->set_rules( $this->validate );

            if ( $this->form_validation->run() === FALSE ) {

                $errors = $this->form_validation->validation_errors();

                if ( isset( $errors[0] ) && ! $errors[0] ) {
                    $result = array( 'code' => 5, 'message' => 'Please enter the required data', 'statusCode' => 400 );
                }
                else {
                    // return result errors log
                    $result = array( 'code' => 3, 'message' => $errors, 'statusCode' => 400 );
                }

                return $result;
            }
            else {

                $isUpdated = $this->update( $id, $data );

                if ( $isUpdated ) {

                    $result = $this->get( $id );
                    $result->code = 0;
                    $result->statusCode = 200;

                    if ( $this->memcacheEnable ) {

                        $key = 'KEY-LeaderBoard' . md5( "getById_LeaderBoard-$id" );
                        $this->user->updateMemcache( $key, $result );
                    }
                }
                else {

                    $errorMessage = $this->db->_error_message();
                    log_message( 'message', 'Update Leader Board ' . $errorMessage );

                    $result = array( 'code' => 4, 'message' => $errorMessage, 'statusCode' => 400 );
                }

                return $result;
            }
        }
    }

    public function getAll( $limit, $offset = 0 ) 
    {
        $leaderBoards = $this->limit( $limit, $offset )->get_all();

        if ( ! empty( $leaderBoards ) )
            $result = array( 'code' => 0, 'leaderBoards' => $leaderBoards, 'statusCode' => 200 );
        else
            $result = array( 'message' => 'LeaderBoard Not Found', 'statusCode' => 404 );        

        return $result;
    }

    public function getById( $id ) 
    {       
        $result = $this->get( $id );

        if ( ! empty( $result ) ) 
        {
            $result->code = 0;
            $result->statusCode = 200;
        }
        else 
        {
            $result = array( 'code' => 1, 'message' => 'LeaderBoard Not Found', 'statusCode' => 404 );
        }

        return $result;
    }

    public function getByLeaderboardId( $leaderBoardId, $limit = null, $offset = 0 ) 
    {        
        if ( $limit )
            $leaderBoards = $this->limit( $limit, $offset )->get_many_by( 'leaderboardId', $leaderBoardId );
        else
            $leaderBoards = $this->get_many_by( 'leaderboardId', $leaderBoardId );

        if ( empty( $leaderBoards ) )
            $result = array( 'code' => 1, 'message' => 'Leader Board Not Found', 'statusCode' => 404 );
        else
            $result = array( 'code' => 0, 'leaderBoards' => $leaderBoards, 'statusCode' => 200 );

        return $result;
    }
}   