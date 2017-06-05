<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EventNotificationLog extends MY_Model {

    // set table is Sport Schedule
    protected $_table = 'EventNotificationsLog';

    protected $public_attributes = array(
            'id',
            'eventNotificationId',
            'type',
            'data',
            'playerId',
            'playerActionTaken',
            'updated',
        );

    public function add( $data ) {

        $logData = array(
            'eventNotificationId'   => $data->id,
            'type'                  => $data->type,
            'playerId'             => $data->playerId,
            'data'                  => $data->data,
            'playerActionTaken'     => $data->pending,
            'updated'               => date( 'Y-m-d H:i:s' ),
        );

        $this->insert( $logData, TRUE );
    }
}