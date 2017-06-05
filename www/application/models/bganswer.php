<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BGAnswer extends MY_Model {

    // set table is wheel
    protected $_table = 'BGAnswers';

    protected $belongs_to = array( 
        'question' => array( 'model' => 'BGQuestion', 'primary_key' => 'id' )
    );

    protected $public_attributes = array(
            'id',
            'questionId',
            'answer',
        );

    /**
     * add a question
     * @param int  $questionId
     * @param array $data
     */
    public function add( $questionId, $data ) {

        if ( empty( $data ) ) {

            $error = array( 'code' => 1, 'message' => 'Please enter at least two answers', 'statusCode' => 400 );

            return $error;
        }
        
        // validate data insert 
        if ( ! is_numeric( $questionId ) || $questionId <= 0 ) {

            // return log error when data miss/ invalid
            $error = array( 'cod' => 1, 'message' => 'Question Id must to be numeric or greater than zero', 'statusCode' => 400 );

            return $error;
        } 
        else {

            $answers = array();

            foreach ( $data as $key => $value ) {
                
                $answer = array();
                $answer['questionId'] = $questionId;
                $answer['answer'] = $value;

                array_push( $answers, $answer );
            }

            if ( ! empty( $answers ) ) {

                $insertId = $this->db->insert_batch( $this->_table, $answers );

                if ( $insertId ) {
                    
                    return TRUE;
                } 
                else {

                    // get and log error message
                    $errorMessage = $this->db->_error_message();
                    log_message( 'error', 'Insert answer: ' . $errorMessage );

                    $result = array( 'code' => 2, 'message' => $errorMessage, 'statusCode' => 400 );
                }
            }
            else {

                $result = array( 'code' => 1, 'message' => 'Please enter at least two answers', 'statusCode' => 400 );
            }
        }

        // return answer object
        return $result;
    }

    /**
     * edit answer by questionId
     * @param  int $questionId
     * @param  array $data
     * @return 
     */
    public function edit( $questionId, $data ) {

        // delete answer by question id
        $this->delete_by( 'questionId', $questionId );

        $result = $this->add( $questionId, $data );

        return $result;
    }
}