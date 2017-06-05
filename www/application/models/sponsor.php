<?php

class Sponsor extends MY_Model {
    
    // set table is Tickets
    protected $_table = 'Sponsors';

    /**
     * getSponsorByIdFromDb get sponsor by Id
     * @param  int $sponsorID 
     * @return object
     */
    public function getSponsorByIdFromDb ( $sponsorID ) {
        $sponsors = $this->db->select( 'Sponsor_Campaigns.wheelSlicePosition,
                    Sponsor_Campaigns.artAssetURL,
                    Sponsors.name,
                    Sponsors.hexColor,
                    Sponsor_Campaigns.weightingWheel' )
                ->join( 'Sponsors', 'Sponsors.id = Sponsor_Campaigns.sponsorID' )
                ->where( 'Sponsor_Campaigns.type = 2' )
                ->where( 'Sponsor_Campaigns.sponsorID', $sponsorID )
                ->where( 'Active = 1' )
                ->where( 'isDeleted = 0' )
                ->order_by( 'wheelSlicePosition' )
                ->get( 'Sponsor_Campaigns' )
                ->result();

        return $sponsors;
    }



    /**
     * get sponsor from database
     * @param  int $slices
     * @param  int $gender
     * @param  int $ageMin
     * @param  int $ageMax
     * @return object        
     */
    public function getSponsorFromDb( $slices, $gender, $ageMin, $ageMax ) {

        $sponsors = $this->db->select( 'Sponsor_Campaigns.wheelSlicePosition,
                    Sponsor_Campaigns.artAssetURL,
                    Sponsors.name,
                    Sponsors.hexColor,
                    Sponsor_Campaigns.weightingWheel' )
                ->join( 'Sponsors', 'Sponsors.id = Sponsor_Campaigns.sponsorID' )
                ->where( 'Sponsor_Campaigns.type = 2' )
                ->where( 'gender', $gender )
                ->where( 'ageMin <=', $ageMin )
                ->where( 'ageMax >=', $ageMax )
                ->where( 'Active = 1' )
                ->where( 'isDeleted = 0' )
                ->order_by( 'wheelSlicePosition' )
                ->limit( $slices )
                ->get( 'Sponsor_Campaigns' )
                ->result();

        return $sponsors;
    }

    /**
     * get sponsor wheel data by Id
     * @param  int $ageMax
     * @return array
     */
    public function getSponsorWheelDataById( $sponsorID ) {

        if ( $this->memcacheEnable ) {
            
            $key = "KEY-Sponsor-Wheel-Data-$sponsorID";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( $result ) {

                return $result;
            }
        }

        $result = array();

        $sponsors = $this->getSponsorByIdFromDb( $sponsorID );
        
        $numRows = count( $sponsors );
           
        $index = 0;
        foreach ( $sponsors as $key => $sponsor ) {
            
            // new instance object
            $row = new stdClass();

            $row->wedgeId               = $index++;
            $row->weightingWheel        = ( !empty($sponsor->weightingWheel) ?  $sponsor->weightingWheel : 1);
            $row->sponsorName           = $sponsor->name;
            $row->imageURL              = $sponsor->artAssetURL;
            $row->backgroundHexColor    = $sponsor->hexColor;

            array_push( $result, $row );
        }

        
        // if the sponsor not found
        if ( empty( $result ) ) {

            $result = array( 'code' => 1, 'message' => 'Sponsor Not Found', 'statusCode' => 404 );
        }
        else {

            $result = array('code' => 0, 'sponsors' => $result, 'count' => count( $result ), 'statusCode' => 200 );
        }

        if ( $this->memcacheEnable ) {

            $this->user->updateMemcache( $key, $result );
        }

        return $result;
    }
    /**
     * get sponsor wheel data
     * @param  int $slices
     * @param  int $gender
     * @param  int $ageMin
     * @param  int $ageMax
     * @return array
     */
    public function getSponsorWheelData( $slices, $gender, $ageMin, $ageMax ) {

        if ( $this->memcacheEnable ) {
            
            $key = "KEY-Sponsor-$slices-$gender-$ageMin-$ageMax";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( $result ) {

                return $result;
            }
        }

        $result = array();

        $sponsors = $this->getSponsorFromDb( $slices, $gender, $ageMin, $ageMax );
        $numRows = count( $sponsors );

        if ( $numRows === $slices ) {

            foreach ( $sponsors as $key => $sponsor ) {
                
                // new instance object
                $row = new stdClass();
                $row->wedgeId               = $sponsor->wheelSlicePosition - 1;
                $row->sponsorName           = $sponsor->name;
                $row->imageURL              = $sponsor->artAssetURL;
                $row->backgroundHexColor    = $sponsor->hexColor;

                array_push( $result, $row );
            }
        }
        else {

            $index = 0;
            foreach ( $sponsors as $key => $sponsor ) {
                
                // new instance object
                $row = new stdClass();

                $row->wedgeId               = $index++;
                $row->sponsorName           = $sponsor->name;
                $row->imageURL              = $sponsor->artAssetURL;
                $row->backgroundHexColor    = $sponsor->hexColor;

                array_push( $result, $row );
            }

            $neededSpots = $slices - $numRows;

            // select sponsor with $neededSpots limit and gender equal 3
            $sponsors = $this->getSponsorFromDb( $neededSpots, 3, $ageMin, $ageMax );

            if ( count( $sponsors ) === $neededSpots ) {

                $index = $numRows;
                foreach ( $sponsors as $key => $sponsor ) {
                    
                    // new instance object
                    $row = new stdClass();

                    $row->wedgeId               = $index++;
                    $row->sponsorName           = $sponsor->name;
                    $row->imageURL              = $sponsor->artAssetURL;
                    $row->backgroundHexColor    = $sponsor->hexColor;

                    array_push( $result, $row );
                }
            }
        }
        
        // if the sponsor not found
        if ( empty( $result ) ) {

            $result = array( 'code' => 1, 'message' => 'Sponsor Not Found', 'statusCode' => 404 );
        }
        else {

            $result = array( 'code' => 0, 'updated' =>strtotime('now') , 'sponsors' => $result, 'count' => count( $result ), 'statusCode' => 200 );
        }

        if ( $this->memcacheEnable ) {

            $this->user->updateMemcache( $key, $result );
        }

        return $result;
    }

    /**
     * get sponsor map data
     * @param  int $map
     * @param  int $gender
     * @param  int $ageMin
     * @param  int $ageMax
     * @return array
     */
    public function getSponsorMapData( $map, $gender, $ageMin, $ageMax, $panelX, $panelY ) {

        // if enable memcache
        if ( $this->memcacheEnable ) {
            
            $key = "KEY-Sponsor-$map-$gender-$ageMin-$ageMax-$panelX-$panelY";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( $result ) {

                return $result;
            }
        }

        $stateArray = array();
        $maps = array();
        $result = array( 'version' => array( 'major' => '1', 'minor' => '0.0' ) );

        // execute query
        $sponsors = $this->db->select( 'Sponsor_Campaigns.id, Sponsor_Campaigns.name AS campaignName,Sponsor_Campaigns.artAssetUrl,
                Sponsor_Campaigns.stateID, Sponsor_Campaigns.xPos , Sponsor_Campaigns.yPos, MapStates.name mapStatesName,
                Sponsor_Campaigns.offerMessage, Sponsor_Campaigns.type, MapStates.panelColumn as panelX, MapStates.panelRow as panelY' )
            ->join( 'MapStates', 'MapStates.Abbreviation = Sponsor_Campaigns.stateID' )
            ->where('panelColumn >=', $panelX)
            ->where('panelColumn <', $panelX + 2)
            ->where( 'type IN ( 1, 4 )' )
            ->where( 'Active = 1' )
            ->where( 'isDeleted = 0' )
            ->where( 'mapID', $map )
            ->where( 'ageMin <=', $ageMin )
            ->where( 'ageMax >=', $ageMax )
            ->order_by( 'stateID ASC , type DESC' )
            ->get( 'Sponsor_Campaigns' )
            ->result();

        if ( ! empty( $sponsors ) ) {

            foreach ( $sponsors as $row  ) {

                // if mapStatesName not exists in stateArray then to push it
                if ( ! array_key_exists( $row->mapStatesName , $stateArray ) ) {

                    $stateArray[ $row->mapStatesName ] = array();
                }

                array_push( $stateArray[ $row->mapStatesName ] , $row );
            }

            // each stateArray array
            foreach( $stateArray as $key => $state ) {

                // convert all character mapStatesName to lowercase
                $mapStatesName = strtolower( (string)trim( preg_replace( '/\s+/' , '' , $key ) ) );

                $index = 1;

                $stateRow = array();

                foreach ( $state as $data ) {
                    
                    $row = array();

                    if ( (int)$data->type === 1 ) {

                        $row['name']    = $data->campaignName;
                        $row['id']      = 'location' . $index++;
                        $row['img']     = $data->artAssetUrl;
                        $row['posx']    = $data->xPos + (($data->panelX - $panelX) * 960);
                        $row['posy']    = $data->yPos + (($data->panelY - $panelY) * 720);

                        $stateRow['image'][] = $row;
                    }
                    else if ( (int)$data->type === 4 ) {

                        $row['name']    = $data->campaignName;
                        $row['id']      = $data->id;
                        $row['img']     = $data->artAssetUrl;
                        $row['posx']    = $data->xPos;
                        $row['posy']    = $data->yPos;
                        $row['message'] = $data->offerMessage;

                        $stateRow['egg'] = $row;
                    }                    
                }

                $map_data = array($mapStatesName, $stateRow);
                array_push($maps, $map_data);
            }

            $result['map'] = $maps;
            $result['message'] = "";
            $result['statusCode'] = 200;

        }
        else {

            $result = array( 'code' => 1, 'message' => 'Sponsor Map Not Found', 'statusCode' => 200 );
        
        }

        // if enable memcache then update cache
        if ( $this->memcacheEnable ) {

            $this->user->updateMemcache( $key, $result );
        }

        return $result;   
    }

    /**
    * getRandomWeightedElement return id 
    * @param  array  $weightedValues 
    * @return int id
    */
    protected function getRandomWeightedElement( $weightedValues ) {

        $rand = mt_rand( 1, (int) array_sum( $weightedValues ) );

        foreach ( $weightedValues as $key => $value ) {
            $rand -= $value;

            if ($rand <= 0) {
            
                return $key;
            }
        }
    }

    /**
     * randomWheelSponsors get a sponsor return when call randomWheelSponsors
     * @return [type] [description]
     */
    protected function randomWheelSponsors( $sponsorID ) {

        $id = NULL;
        $winSponsor = NULL;

        // get all list sponsors
        $sponsors = $this->getSponsorWheelDataById( $sponsorID );

        // check if isset sponsors return
        if ( isset( $sponsors['statusCode'] ) && $sponsors['statusCode'] === 200 ) {

            $weightRand = array();

            // get weightingValue from list sponsors
            foreach ( $sponsors['sponsors'] as $key => $sponsor ) {
                
                $weightRand["$key"] = (int)$sponsor->weightingWheel;
            }

            // get win sponsor
            $id = $this->getRandomWeightedElement( $weightRand );

            foreach ( $sponsors['sponsors'] as $key => $sponsor ) {
                
                if ( $key === $id ) {
                    // return object sponsor win
                    $winSponsor = $sponsor;
                }
            }
        }

        // get result sponsor
        $result = $winSponsor;
        
        // return object sponsor win
        return $result;
    }

    /**
     * sponsorSpin get spin sponsors
     * @return [type] [description]
     */
    public function sponsorSpin( $sponsorID ) {

        // random wheel wedges
        $sponsor = $this->randomWheelSponsors( $sponsorID );

        if ( $sponsor ) {

            $result = $sponsor;
            $result->statusCode = 200;
        } 
        else {

            $result = array('code' => 1, 'message' => "Not Found.", 'statusCode' => 400);
        }

        // return result object sponsor        
        return $result;
    }
}   