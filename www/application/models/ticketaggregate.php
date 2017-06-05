<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TicketAggregate extends MY_Model {

    public $_table = 'TicketAggregate';
    
    public function add($playerId, $sweepstakesId, $count)
    {
        $rs = $this->db->query(sprintf("Insert into TicketAggregate (playerId, sweepstakesId, ticketCount) 
            values (%d, %d, %d) ON DUPLICATE KEY UPDATE ticketCount = ticketCount + %d", $playerId, $sweepstakesId, $count, $count));
        
        return $rs;
    }
    
    public function getAll($playerId)
    {        
        $rs = $this->db->query(sprintf("Select s.name, s.endDate, s.startDate, t.ticketCount as ticketsEntered from TicketAggregate t
            Inner join Sweepstakes s on t.sweepstakesId = s.id
            where playerId = %d and s.endDate > convert_tz(now(), 'GMT', 'US/Pacific')
            Order by s.endDate DESC", $playerId));
        return $rs->result();        
    }
    
    public function getCounts($playerId)
    {
        $ret = array();
        $rs = $this->db->query(sprintf("Select sum(ticketCount) as cnt from TicketAggregate where playerId = %d and sweepstakesId = 0", $playerId));
        $ret['available'] = $rs->row()->cnt;
        $rs = $this->db->query(sprintf("Select sum(ticketCount) as cnt from TicketAggregate where playerId = %d and sweepstakesId <> 0", $playerId));
        $ret['used'] = $rs->row()->cnt;
        return $ret;
    }
    
    public function processTickets($playerId, $sweepstakesId, $count)
    {
        $rs = $this->db->query(sprintf("Select ticketCount from TicketAggregate where playerId = %d and sweepstakesId = 0", $playerId));
        if(!$rs->num_rows())
            return array("code" => 1, "message" => "No Available Tickets", "statusCode" => 400);
        
        if($rs->row()->ticketCount < $count)
            return array("code" => 2, "message" => "Not Enough Available Tickets", "statusCode" => 400);
        
        $this->db->query(sprintf("Update TicketAggregate set ticketCount = ticketCount - %d where playerId = %d and sweepstakesId = 0", $count, $playerId));
        $this->add($playerId, $sweepstakesId, $count);
        
        return array("code" => 0, "message" => NULL, "statusCode" => 200);
    }
}
