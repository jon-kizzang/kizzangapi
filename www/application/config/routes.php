<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = 'welcome';

//status checks
$route['ok'] = 'api/operations/healthCheck';
$route['api/game/rules/cat/(:any)'] = 'api/gamerules/getGameRulesCat/$1';
$route['api/game/rules/cat/(:any)/(:num)/(:num)'] = 'api/gamerules/getGameRulesCat/$1/$2/$3';
$route['api/marketing/impression'] = 'api/marketingemails/impression';

//
// ROUTES USED BY THE FLASH PLAYER WITHIN A WEB BROWSER
// 1 - GET
// 2 - PUT
// 3 - DELETE

//Generic Calls
$route['api/date'] = "api/debug/date";
$route['api/emailstatus'] = 'api/debug/email_action';
$route['api/1/players/(:num)'] 	= 'api/players/getOne/$1';
$route['api/players'] = 'api/players/add' ;
$route['api/1/players/emailConfirm/(:any)'] 	= 'api/players/emailVerified/$1';
$route['api/players/phoneConfirm'] 				= 'api/players/phoneVerified';
$route['api/players/phoneResendCode']			= 'api/players/phoneResendCode';
$route['api/players/resendEmail']                            = 'api/players/resendConfirmationEmail';
$route['api/1/players/optout/(:any)'] 			= 'api/players/ignoreEmail/$1';
$route['api/1/players/logout'] 				= 'api/players/logout';
$route['api/players/loginfb']				= 'api/players/loginFacebook';
$route['api/players/linkfb']				= 'api/players/link';
$route['api/players/loginguest']				= 'api/players/loginGuest';
$route['api/players/loginall']				= 'api/players/loginAll';
$route['api/1/players/(:num)/playerdata'] 		= 'api/players/getCurrentData/$1';
$route['api/players/resetPassword/(:any)']		= 'api/players/changePassword/$1';
$route['api/2/players/updateUserFlow']			= 'api/players/setNewUserFlow';
$route['api/1/players/(:num)/payInfo'] 		= 'api/payments/payInfo/$1';
$route['api/2/eventnotification/(:num)']		= 'api/eventnotifications/update/$1';
$route['api/2/players/(:num)'] 	= 'api/players/update/$1';
$route['api/tickets/(:num)'] = 'api/ticketscompressed/enter/$1';
$route['api/players/transactions'] 	= 'api/players/transactions';

//Bracket Challenge
$route['api/brackets/get/(:num:)'] = 'api/brackets/get/$1';
$route['api/brackets/save'] = 'api/brackets/save';
$route['api/brackets/email'] = "api/brackets/email";
$route['api/brackets/cards/(:num)'] = "api/brackets/cards/$1";
$route['api/brackets/card/(:num)'] = "api/brackets/card/$1";
$route['api/brackets/image/(:num)'] = "api/brackets/image/$1";
$route['api/brackets/button'] = "api/brackets/button";
$route['api/brackets/celebrities/(:num)'] = "api/brackets/celebrities/$1";

$route['api/roal/get/(:num)'] = 'api/roals/getById/$1';
$route['api/roal/save'] = 'api/roals/save';

$route['api/analytics'] = 'api/debug/analytics';

//Affiliates
$route['api/affiliates/check/(:any)'] = 'api/affiliates/getStatus/$1';
$route['api/affiliates/get/(:any)/(:any)'] = 'api/affiliates/getCampaigns/$1/$2';

//Bingo
$route['api/bingo/initPlayer'] = 'api/bingos/initPlayer';
$route['api/bingo/bingo'] = 'api/bingos/checkBingo';

//Lottery
$route['api/lottery/get'] = 'api/lotteryconfigs/getCurrent';
$route['api/lottery/card/add'] = 'api/lotterycards/add';
$route['api/lottery/random/(:num)'] = 'api/lotteryconfigs/getRandom/$1';

//New Login Flow
$route['api/players/email/create'] = 'api/players/emailCreate';
$route['api/players/phone/create'] = 'api/players/phoneCreate';
$route['api/players/account/verify'] = 'api/players/accountVerify';
$route['api/players/phone/verify'] = 'api/players/accountVerify';
$route['api/players/verify/resend'] = 'api/players/verifyResend';

//File Server
$route['api/file/(:any)'] = 'api/configcontroller/getFile/$1';

// resource playperiod
$route['api/2/players/updatePassword']  = 'api/players/updatePassword';
$route['api/2/players/updateEmail']         = 'api/players/updateEmail';

// resource sweepstakes
$route['api/1/sweepstakes/active']		= 'api/sweepstakes/getAllActive';
$route['api/1/sweepstakes/list']                       =  'api/sweepstakes/getListAll';
$route['api/sweepstakes/submit/(:num)']      = 'api/tickets/add/$1';

// resource positions
$route['api/1/players/(:num)/positions'] 					= 'api/positions/getAll/$1';
$route['api/2/players/positions/(:num)/ack']			= 'api/positions/setAck/$1';

// available games for player
$route['api/1/players/(:num)/games'] = 'api/games/getAll/$1';
$route['api/getCurrentGame/(:any)'] = 'api/players/getCurrentGame/$1';

//For Ad count
$route['api/ad'] = 'api/debug/addAd';

// language localization strings
$route['api/1/localizations/(:num)'] 		= 'api/localizations/getAll/$1';

$route['api/1/players/(:num)/gamecounts'] 			= 'api/gamecounts/getByPlayerId/$1';
$route['api/players/(:num)/gamecounts'] 			= 'api/gamecounts/add/$1';

$route['api/1/wheels/1/spin/(:any)/(:any)']                 =  'api/wedges/sponsors/$1/$2';
$route['api/1/wheels/(:num)/spin/(:any)/(:any)']        =  'api/wedges/spin/$1/$2/$3';
$route['api/wheels/(:num)/addSpinEvent']                =  'api/wedges/addSpinEvent/$1';
$route['api/eventnotification/chedda/add']                = 'api/cheddas/addEN';
$route['api/eventnotification/chedda']                       = 'api/cheddas/getEN';

// resource leaderboard
$route['api/1/leaderboards/(:num)'] = 'api/leaderboards/getById/$1';

$route['api/1/leaderboards'] 						= 'api/leaderboards/getAll';
$route['api/1/leaderboards/menu'] 					= 'api/leaderboards/menu';
$route['api/1/leaderboards/(:any)/(:any)'] 				= 'api/leaderboards/get/$1/$2';
$route['api/1/leaderboards/(:any)'] 					= 'api/leaderboards/get/$1';

$route['api/1/maps/sponsors/(:num)/(:num)/(:num)/(:num)'] 	= 'api/sponsors/getSponsorMapData/$1/$2/$3/$4';
$route['api/1/maps/sponsors/(:num)/(:num)/(:num)/(:num)/(:num)/(:num)'] 	= 'api/sponsors/getSponsorMapData/$1/$2/$3/$4/$5/$6';

// Rules
$route['api/1/rules'] = 'api/rules/getAll';
$route['api/maintenance'] = 'api/debug/maintenance';

// resource game 
$route['api/1/game/(:any)/rules']           = 'api/gamerules/getGameRules/$1';

// resource map states
//$route['api/1/mapstates'] 						= 'api/maps/panels';
$route['api/1/mapstates/(:num)/(:num)/(:num)'] 	= 'api/maps/panels/$1/$2/$3';
$route['api/1/mapstates/retention/(:num)'] 		= 'api/maps/retentionDays/$1';

// resource win odometer
$route['api/1/winodometer/(:num)'] = 'api/winodometers/getOne/$1';

$route['api/1/players/(:num)/payInfo'] 		= 'api/payments/payInfo/$1';
$route['api/2/players/(:num)/payUpdate'] 	= 'api/payments/payUpdate/$1';

// resource profile
$route['api/1/players/(:num)/profile/(:num)/(:num)'] = 'api/players/getProfile/$1/$2/$3';

// Chedda Functions
$route['api/1/chedda/status']                           = 'api/cheddas/getStatus';

// resource organizations for donations
$route['api/1/donations/(:num)/(:num)']                     = 'api/donations/getAll/$1/$2';
$route['api/1/tickets/(:num)/status']                           = 'api/ticketscompressed/status/$1';
$route['api/1/winner/(:num)/(:num)']                         = 'api/winners/getAll/$1/$2';
$route['api/1/winner/instant']                         = 'api/winners/getInstantWinner';

$route['api/1/scratchcards']                                         = 'api/scratchcards/getAll';
$route['api/1/scratchcards/killkey/(:any)'] 			= 'api/scratchcards/killKey/$1';
$route['api/1/scratchcards/card']		 			= 'api/scratchcards/card';

$route['api/1/parlay/(:num)']       		= 'api/parlaycards/getById/$1';
$route['api/1/parlay/(:any)/(:num)']    		= 'api/parlaycards/getAll/$1/$2';
$route['api/parlay/card']             		= 'api/parlaycards/add';
$route['api/parlay/save'] 				= 'api/parlayplayercards/add';
$route['api/parlay/get/(:num)'] 			= 'api/parlayplayercards/getOne/$1';

// resource email notifictions
$route['api/emailnotifications'] 			= 'api/emailnotifications/add';

// resource debug
$route['api/debug/clear/player/(:num)'] = 'api/debug/clearCacheByPlayerId/$1';
$route['api/debug/addGameToken/(:num)/(:num)'] = 'api/debug/addGameToken/$1/$2';
$route['api/debug/recoverEvents'] = 'api/debug/recoverEventNotifications';
$route['api/debug/verifyMemCacheKey/(:any)'] = 'api/debug/verifyMemCacheKey/$1';
$route['api/w2getinfo/(:any)'] = 'api/debug/w2check/$1';
$route['api/w2updateinfo/(:any)'] = 'api/debug/w2update/$1';
$route['api/passthru'] = 'api/debug/passthru';
$route['api/passthru/image'] = 'api/debug/passthruimage';
$route['api/paypal/ipn'] = 'api/debug/ipn';
$route['api/paypal/status'] = 'api/debug/ppStatus';

// resource lobby
$route['api/1/lobby/(:num)']	= 'api/lobbys/getById/$1';

// resource eventnotifications
$route['api/eventnotification']                         = 'api/eventnotifications/add';
$route['api/eventnotification/ack/(:num)']                         = 'api/eventnotifications/ack/$1';
$route['api/1/players/notifications']			= 'api/eventnotifications/getByPlayerId';

$route['api/1/players/facebookInvites/(:num)/(:num)']   = 'api/facebookinvites/getFriendList/$1/$2';
$route['api/2/players/facebookInvites']                 = 'api/facebookinvites/add';

//map analytics
$route['api/mapimpression']                             = 'api/mapimpressions/add';
$route['404_override'] = '';

// email tracking events
$route['api/1/tracking/opens/(:num)/(:num)']			= 'api/trackemails/emailOpened/$1/$2';
$route['api/playerinvites/addFriend/'] = 'api/playerinvites/addFriend';

$route['api/campaign/(:any)']       = 'api/campaigns/getById/$1';

$route['api/testimonials']   = 'api/testimonials/getAll';
$route['api/testimonials/(:num)']   = 'api/testimonials/getAll/$1';

$route['api/storeitems']   = 'api/storeitems/getAll';
$route['api/storeitems/(:num)']   = 'api/storeitems/getAll/$1';
$route['api/storeitems/buy/(:num)']   = 'api/storeitems/buy/$1';

//Biggame 30 calls
$route['api/1/biggame30/questions'] 		= 'api/bgquestions/getAll';
$route['api/biggame30/save'] 			= 'api/bgplayercards/add';

//Final 3 calls
$route['api/1/final3/(:any)'] = 'api/finalconfigs/getOne/$1';
$route['api/1/final3/save'] = 'api/finalconfigs/save';

/* End of file routes.php */
/* Location: ./application/config/routes.php */

