<?php

class Ticketcompressed extends MY_Model {
    
    // set table is Tickets
    protected $_table = 'TicketCompressed';

    // set TRUE will not delete record, only set $soft_delete_key field to 1
    protected $soft_delete = TRUE;
    protected $soft_delete_key = 'isDeleted';

    // set validations rules
    protected $validate = array(
        'playerId' => array(
            'field' => 'playerId',
            'label' => 'playerId',
            'rules' => 'required'
        ),
        'gameToken' => array(
            'field' => 'gameToken',
            'label' => 'Game Token',
            'rules' => 'required'
        ),
         'isIssued' => array(
            'field' => 'isIssued',
            'label' => 'isIssued',
            'rules' => 'required'
        )
    );

    protected $public_attributes = array(
        'playerId',
        'gameToken',
        'isIssued',
        'count'        
        );              

    /**
    * add a ticket
    * @param array $data form post
    */
    public function add( $playerId, $count, $gameToken) 
    {        
        $this->load->model("ticketaggregate");        

        //  Add to compressed ticket Table        
        $this->insert(array('playerId' => $playerId, 'gameToken' => $gameToken, 'count' => $count, 'sweekstakesId' => 0), true);        
        
        if($this->ticketaggregate->add($playerId, 0, $count))
            $result = array( 'code' => 0, 'message' => null, 'statusCode' => 200 );
        else
            $result = array( 'code' => 2, 'message' => 'Aggregate Table insertion fail', 'statusCode' => 400 );

        // return object ticket when create new ticket successful
        return $result;
    }
   
    /**
     * enterTicketIntoDB insert, udpate ticket, sweepstake, sweepstakeTiket
     * @param  int $playerId      
     * @param  int $sweepstakeId  
     * @param  int $numberTickets 
     * @param  int $ratioTicket   
     * @return $result
     */
    protected function enterTicketIntoDB($playerId, $sweepstakeId, $numberTickets) 
    {    
        $this->load->model('ticketaggregate');

        $rs = $this->db->query("Select * from TicketCompressed where playerId = ? and sweepstakesId = 0", array($playerId));
        $tempTickets = $numberTickets;
        $gameTokens = array();
        if($rs->num_rows())
        {
            foreach($rs->result() as $row)
            {
                if($row->count <= $tempTickets) //Then update record
                {
                    $this->db->query("Insert into TicketCompressed (playerId, gameToken, sweepstakesId, count) 
                        values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE count = count + VALUES(count)", array($playerId, $row->gameToken, $sweepstakeId, $row->count)); 
                    $gameTokens[] = $row->gameToken;                    
                }
                else //If not, then split the record into 2
                {
                    $this->db->query("Insert into TicketCompressed (playerId, gameToken, sweepstakesId, count) 
                        values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE count = count + VALUES(count)", array($playerId, $row->gameToken, $sweepstakeId, $tempTickets));
                    $this->db->query("Update TicketCompressed set count = ? where playerId = ? and sweepstakesId = 0 and gameToken = ?", array(($row->count - $tempTickets),$playerId, $row->gameToken));
                }
                $tempTickets -= $row->count;
                if($tempTickets <= 0)
                    break;
            }
        }
        
        if($gameTokens)
        {
            $query = sprintf("Delete from TicketCompressed where gameToken in ('%s') and sweepstakesId = 0", implode("','", $gameTokens));
            $this->db->query($query);
        }
        
        $result = array('code' => 0, 'message' => "Tickets has been created successfully", 'statusCode' => 200);
        
        $temp = $this->ticketaggregate->processTickets($playerId, $sweepstakeId, $numberTickets);
        if($temp['code'])
            $result = $temp;
        
        return $result;
    }
    /**
     * enterTicket Enter number ticket into sweeptakes
     * 
     * @param  int $playerId     
     * @param  int $sweekstakeId 
     * @param  $data         
     * @return $result        
     */
    public function enterTicket( $sweepstakeId, $data ) 
    {        
        // validate id 
        $this->load->model("ticketaggregate");
        if ( ! is_numeric($sweepstakeId) || $sweepstakeId <= 0 )         
            return array( 'code' => 1, 'message' => 'Sweeptakes Id must is a numeric and greater than zero', 'statusCode' => 400 );                 
        
        if ( empty( $data ) ) 
        {
            // return error when data requires is invalid/ miss
            $error = array( 'code' => 2, 'message' => 'Please the required enter data', 'statusCode' => 400 );
            // return error
            return $error;
        }
        else 
        {
            $validate = array(
                // verify wheelId must be is required
                'playerId' => array( 
                    'field' => 'playerId', 
                    'label' => 'playerId',
                    'rules' => 'required|greater_than[0]'
                ),
                'numberTickets' => array( 
                    'field' => 'numberTickets', 
                    'label' => 'numberTickets',
                    'rules' => 'required|greater_than[0]'
                ),
            );

            // reset form validation
            $this->form_validation->reset_validation();

            // set form data to validate
            $this->form_validation->set_params( $data );

            // set rule validation
            $this->form_validation->set_rules( $validate );

            if ( $this->form_validation->run() === FALSE ) 
            {
                $error = $this->form_validation->validation_errors();
                $errors = array( 'code' => 3, 'message' => $error, 'statusCode' => 400 );

                return $errors;
            }

            // check authentication
            $isValid = $this->player->checkActionOwner( $data['playerId'] );

            // in the case error
            if ( is_array( $isValid ) ) 
                return $isValid;            

            $this->load->model('sweepstake');

            $isValidSweepstake = $this->sweepstake->getById($sweepstakeId);

            if ( is_object( $isValidSweepstake ) ) 
            {
                $playerId       = (int)$data['playerId'];
                $numberTickets  = (int)$data['numberTickets'];
                $sweepstakeType = $isValidSweepstake->sweepstakeType;
                $maxEntrants    = (int)$isValidSweepstake->maxEntrants;
                $entryCount     = (int)$isValidSweepstake->entryCount;
                $ratioTicket    = (int)$isValidSweepstake->ratioTicket;
                
                $count = $this->ticketaggregate->getCounts($playerId);                
                
                if ( $count['available'] >= $ratioTicket ) 
               {
                    if ( $sweepstakeType === "closed" ) 
                    {
                        $errors = array('code' => 5, 'message' => 'Sweepstakes ended. Cannot enter a sweepstakes ticket to a sweepstakes that has ended.', 'statusCode' => 400);
                        return $errors;                       
                    } 
                    else 
                    {
                            // get result when enter tickets into sweepstake
                            $result = $this->enterTicketIntoDB( $playerId, $sweepstakeId, $numberTickets);
                            return $result;                       
                    }
                } 
                else 
                {
                    return array('code' => 6, 'message' => 'Tickets not found', 'statusCode' => 400);                    
                }
            } 
            else 
            {
                return array('code' => 7, 'message' => "Sweepstake not found or had been deleted", 'statusCode' => 400);                
            }
        }
    }
    
    protected function getCount( $playerId ) 
    {
        $this->load->model("ticketaggregate");
        $tickets = $this->ticketaggregate->getCounts($playerId);

        $results = array( 'issuedTicketCount' => (int) $tickets['used'], 'totalTicketCount' => $tickets['used'] + $tickets['available'], 'statusCode' => 200 );

        return $results;
    }

    /**
     * status get count tickets and count tickets had used
     * @param  int 
     * @return array
     */
    public function status( $playerId ) 
    {
        // check authentication
        $isValid = $this->player->checkActionOwner( $playerId );

        // in the case error
        if ( is_array( $isValid ) )
            return $isValid;        
        
        // if not enabled caching, just return the data form database.
        return $this->getCounts( $playerId );
    }
    
    protected function getCounts( $playerId ) 
    {
        $this->load->model("ticketaggregate");
        $tickets = $this->ticketaggregate->getCounts($playerId);

        $results = array( 'issuedTicketCount' => (int) $tickets['used'], 'totalTicketCount' => $tickets['used'] + $tickets['available'], 'statusCode' => 200 );

        return $results;
    }
    
}   