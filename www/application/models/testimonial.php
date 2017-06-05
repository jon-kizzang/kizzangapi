<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Testimonial extends MY_Model 
{

    // set table is Sport Schedule
    protected $_table = 'Testimonials';

    protected $public_attributes = array(
            'image',
            'description',
            'winDate',
            'created',
            'updated'
        );

   public function getAll($limit)
   {
       $colors = array('84BD00','C6579A','FFB81C','FC4C02','41B6E6','4C8C2B','80276C');
       
       $ret = array('code' => 0);
       $rs = $this->db->query("Select name, state, image, description, testimonial, winDate as date from Testimonials order by winDate DESC limit " . $limit);
       $testimonials = $rs->result();
       foreach($testimonials as $index => &$row)
           $row->color = $colors[$index % 7];
       $ret['testimonials'] = $testimonials;
       $ret['statusCode'] = 200;
       return $ret;
   }
}