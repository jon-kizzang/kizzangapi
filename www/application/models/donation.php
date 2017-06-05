<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Donation extends MY_Model {

    // set table to DonationOrganizations
    protected $_table = 'DonationOrganizations';

    // set TRUE will not delete record, only set $soft_delete_key field to 1
    protected $soft_delete = TRUE;
    protected $soft_delete_key = 'isDeleted';

    // set validations rules
    protected $validate = array(
        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
            'name',
        );

    /**
     * getAllOrganizationsFromDB get all organizations from database
     * @param  int $limit  
     * @param  int $offset 
     * @return array         
     */
    protected function getAllOrganizationsFromDB( $limit, $offset ) 
    {
        // get all organizations from database by offset and limit
        $organizations = $this->limit( $limit, $offset )->get_all();

        if ( empty( $organizations ) )
            $result = array( 'code' => 1, 'message' => 'Organizations Not Found', 'statusCode' => 404 );        
        else 
            $result = array('code' => 0, 'organizations' => $organizations, 'statusCode' => 200 );        

        return $result;
    } 

    public function getAll( $limit, $offset ) 
    {
        return $this->getAllOrganizationsFromDB( $limit, $offset );        
    }
	
    protected function getByIdFromDb( $organizationId ) 
    {
        // get object donationorganization by if from database
        $organization = $this->get( $organizationId );

        if ( empty($organization) )
            $result = array( 'code' => 1, 'error' => 'Donation organization not found', 'statusCode' => 404 );                     
        else 
            $result = array( 'code' => 0, 'organizationName' => $organization->name, 'statusCode' => 200 );
        
        return $result;        
    }

    public function getById( $organizationId ) 
    {
        // validate the id.
        if ( ! is_numeric( $organizationId ) || $organizationId <= 0 )                     
            return array( 'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );
                   
        return $this->getByIdFromDb( $organizationId );
        
    }   
}