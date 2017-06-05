<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BGPick extends MY_Model {

	// Use for fetching values from the db and updating memcache instead of
	// using memcache directly if a key already exists. Helpful for testing.
	private $testing = FALSE;

    // set table is Sport Schedule
    protected $_table = 'BGPlayerPicks';

    // set validations rules
    protected $validate = array(
        'questionId' => array( 
            'field' => 'questionId', 
            'label' => 'question Id',
            'rules' => 'required|greater_than[0]'
        ),
        'playerPicksWinner' => array( 
            'field' => 'playerPicksWinner', 
            'label' => 'playerPicksWinner',
            'rules' => 'greater_than[0]'
        ),
        'evaluation' => array( 
            'field' => 'evaluation', 
            'label' => 'evaluation',
            'rules' => 'greater_than[0]'
        ),
    );

    protected $public_attributes = array(
            'questionId',
            'playerPicksWinner',
            'evaluation',
        );

    /**
     * get all results for a specific card that players used to enter picks
     * @param  int  $bgPlayerCardId
     * @return array
     */
    public function getAll( $bgPlayerCardId ) 
    {
        if ( empty( $bgPlayerCardId ) )
            return array( 'code' => 1, 'message' => 'Please enter a valid big game card id', 'statusCode' => 400 );
       
        // get list games by date
        $results = $this->get_many_by( 'bgPlayerCardId', $bgPlayerCardId );

        // if not found any game
        if ( empty( $results) )
            $result = array( 'code' => 2, 'message' => 'Picks Not Found for big game card id: ' . $bgPlayerCardId, 'statusCode' => 404 );        
        else
            $result = array( 'code' => 0, 'results' => $results, 'count' => count( $results ), 'statusCode' => 200 );

        return $result;
    }

    /**
     * add big game pick
     * @param array $data
     * @param array $bgQuestions list question by category id
     */
    public function add( $data, $bgQuestions ) {

        $picksArray = $data['picksHashArray'];
        $dataPlayerPicks = array();

        // if found sport parlay card
        if ( $bgQuestions['code'] == 0 ) {

            $picksData          = array();
            $row                = array();
            $answerIds          = $bgQuestions['answerIds'];
            $questionAnswerIds  = $bgQuestions['questionAnswerIds'];

            $row['bgPlayerCardId']      = $data['bgPlayerCardId'];

            foreach ( $picksArray as $pick ) {
                
                // TODO set question Id
                $row['bgQuestionId']        = $questionAnswerIds[$pick];
                $row['playerPicksWinner']   = $pick;
                array_push( $picksData, $row );
            }

            $isError = null;

            if ( ! empty( $picksData ) ) {
            
                $isInserted = $this->db->insert_batch( $this->_table, $picksData );
                $isError = $this->db->_error_message();
            }

            if ( ! $isError ) {

                return TRUE;
            }
            else {

                // get and log error message
                log_message( 'error', 'Insert BGPlayerPicks: ' . $isError );

                $result = array( 'code' => 2, 'message' => $isError, 'statusCode' => 400  );

                return $result;
            }
        }
        else {

            return $bgQuestions;
        }
    }

    public function evaluation( $date ) {

        $winners = array();

        $this->load->model( 'bgplayercard' );
        $this->load->model( 'bgresult' );
        $this->load->model( 'parlaypick' );
        
        //get all Big Game Player Card by date
        $bgPlayerCards = $this->bgplayercard->getAllByDate( $date );

        if ( (int)$bgPlayerCards['code'] !== 0 ) {

            return $bgPlayerCards;
        }
        else {

            foreach ( $bgPlayerCards['playerCards'] as $playerCard ) {

                $picksArray = explode( ':', $playerCard->picksHash );
                $playerPicks = $this->getAll( $playerCard->id );

                if ( (int)$playerPicks['code'] === 0 ) {
                    
                    $wins = 0;
                    $losses = 0;

                    foreach ( $playerPicks['results'] as $playerPick ) {

                        // set $onlyGetAnswer = TRUE
                        $answerRight = $this->bgresult->getByQuestionId( $playerPick->bgQuestionId, TRUE );

                        if ( ! empty( $answerRight ) ) {

                            // if player pick answer has answerRight array
                            if ( in_array( $playerPick->playerPicksWinner, $answerRight ) ) {

                                // set to win
                                $evaluation = 1;
                                $wins++;
                            }
                            else {

                                // set to loss
                                $evaluation = 0;
                                $losses++;
                            }

                            $this->update( $playerPick->id, array( 'evaluation' => $evaluation ), TRUE );
                        }
                        else {

                            $losses++;
                        }
                    } // end foreach $playerPicks['results']

                    if ( $wins || $losses ) {

                        $rank = ( $wins + $losses ) - $wins + 1;
                        $this->bgplayercard->update( $playerCard->id, array( 'wins' => $wins, 'losses' => $losses, 'rank' => $rank ), TRUE );
                    }

                } // else $playerPicks['code'] === 0
                else {

                    return $playerPicks;
                }
            } // end foreach

            $winners = $this->getWinners( $date );
        }

        // get list winner or winners
        $result = array( 'code' => 0, 'winners' => $winners, 'statusCode' => 200 );

        return $result;
    }

    protected function getWinners( $date ) {

        $this->load->model( 'finalpick' );
        $winners        = array();
        $playerCardIds  = array();

        //get all Player Card by date
        $bgPlayerCards = $this->bgplayercard->limit( 3 )
                ->order_by( 'rank, dateTime')
                ->get_many_by( array( 'date_format(dateTime,"%m-%d-%Y")' => array( 'isRaw' => "'$date'" ) ) );

        if ( empty( $bgPlayerCards ) ) {

            return NULL;
        }
        else {

            foreach ( $bgPlayerCards as $playerCard ) {

                if ( (int)$playerCard->losses === 0 ) {

                    array_push( $playerCardIds, $playerCard->id );

                    if ( isset( $player->accountData ) ) {
                            
                        $player->accountData = array_filter( $player->accountData );
                    }

                    $winners[] = (array)$playerCard;
                }
                else if ( (int)$playerCard->losses === 1 ) {

                    $lossCount = $this->count_by( array( 'bgPlayerCardId' => $playerCard->id, 'evaluation' => -1 ) );

                    if ( $lossCount === 1 ) {

                        array_push( $playerCardIds, $playerCard->id );
                        
                        if ( isset( $player->accountData ) ) {

                            $player->accountData = array_filter( $player->accountData );
                        }

                        $winners[] = (array)$playerCard;
                    }
                }
            }
        }

        if ( ! empty( $playerCardIds ) ) {

            // set winner to 1 with player card id
            $this->db->set( 'winner', 1 )
                    ->where_in( 'id', $playerCardIds )
                    ->update( 'BGPlayerCards' );
        }

        $bgWinners = $this->finalpick->sortWinner( $winners );

        return $bgWinners;
    }
}