<?php

use phpDocumentor\Reflection\Types\Object_;
use Restserver\Libraries\REST_Controller;

include APPPATH . '/libraries/REST_Controller.php';
include APPPATH . '/libraries/Format.php';
class Ludo extends REST_Controller
{
    private $data;
    public function __construct()
    {
        parent::__construct();
        $header = $this->input->request_headers('token');

        if (!isset($header['Token'])) {
            $data['message'] = 'Invalid Request';
            $data['code'] = HTTP_UNAUTHORIZED;
            $this->response($data, HTTP_OK);
            exit();
        }

        if ($header['Token'] != getToken()) {
            $data['message'] = 'Invalid Authorization';
            $data['code'] = HTTP_METHOD_NOT_ALLOWED;
            $this->response($data, HTTP_OK);
            exit();
        }

        $this->data = $this->input->post();
        // print_r($this->data['user_id']);
        $this->load->model([
            'Ludo_model',
            'Users_model',
            'Setting_model'
        ]);
    }

    public function sendNotification($TableId)
    {
        $userdata = $this->Users_model->FreeUserList();

        foreach ($userdata as $value) {
            if (!empty($value->fcm)) {
                $data['msg'] = "New User Joined Table";
                $data['title'] = "Teen Patti";
                $data['ludo_table_id'] = $TableId;
                push_notification_android($value->fcm, $data);
            }
        }
    }

    public function get_table_master_post()
    {
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $joining_amount = $this->Setting_model->Setting()->joining_amount;
        // if ($user[0]->wallet<$joining_amount) {
        //     $data['message'] = 'Required Minimum '.number_format($joining_amount).' Coins to Play';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        if ($user[0]->ludo_table_id) {
            $table_data = $this->Ludo_model->TableUser($user[0]->ludo_table_id);
            $data['message'] = 'You are Already On Table';
            $data['table_data'] = $table_data;
            $data['code'] = 205;
            $this->response($data, 200);
            exit();
        }

        // $table_amount = 500;
        // $table_data = [
        //     'boot_value' => $table_amount,
        //     'maximum_blind' => 4,
        //     'chaal_limit' => $table_amount*128,
        //     'pot_limit' => $table_amount*1024,
        //     'added_date' => date('Y-m-d H:i:s'),
        //     'updated_date' => date('Y-m-d H:i:s')
        // ];

        // $tables = $this->Ludo_model->getPublicActiveTable();
        // $seat_position = 1;

        // if($tables)
        // {
        //     foreach ($tables as $value) {
        //         if($value->members<5)
        //         {
        //             $TableId = $value->ludo_table_id;
        //             $seat_position = $this->Ludo_model->GetSeatOnTable($TableId);
        //         }
        //     }
        // }

        // if(empty($TableId))
        // {
        //     $TableId = $this->Ludo_model->CreateTable($table_data);
        //     // $this->sendNotification($TableId);

        //     $bot = $this->Users_model->GetFreeBot();

        //     $table_bot_data = [
        //         'ludo_table_id' => $TableId,
        //         'user_id' => $bot[0]->id,
        //         'seat_position' => 2,
        //         'added_date' => date('Y-m-d H:i:s'),
        //         'updated_date' => date('Y-m-d H:i:s')
        //     ];

        //     $this->Ludo_model->AddTableUser($table_bot_data);
        // }

        // $table_user_data = [
        //     'ludo_table_id' => $TableId,
        //     'user_id' => $user[0]->id,
        //     'seat_position' => $seat_position,
        //     'added_date' => date('Y-m-d H:i:s'),
        //     'updated_date' => date('Y-m-d H:i:s')
        // ];

        // $this->Ludo_model->AddTableUser($table_user_data);

        $table_data = $this->Ludo_model->getTableMaster();

        $data['message'] = 'Success';
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function update_room_id_post()
    {
        if (empty($this->data['id']) && empty($this->data['room_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $table_data = $this->Ludo_model->UpdateRoomId($this->data['id'], $this->data['room_id']);

        $data['message'] = 'Success';
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function get_table_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['boot_value'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $joining_amount = $this->Setting_model->Setting()->joining_amount;
        $isMaster = $this->Ludo_model->getTableMaster($this->data['boot_value']);
        if (empty($isMaster)) {
            $data['message'] = 'Invalid Boot Value';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<$isMaster[0]->boot_value) {
            $data['message'] = 'Required Minimum '.number_format($isMaster[0]->boot_value).' Coins to Play';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->ludo_table_id) {
            $table_data = $this->Ludo_model->TableUser($user[0]->ludo_table_id);
            $data['message'] = 'You are Already On Table';
            $data['table_data'] = $table_data;
            $data['code'] = HTTP_OK;
            $this->response($data, 200);
            exit();
        }

        // $tables = $this->Ludo_model->getCustomizeActiveTable($isMaster[0]->boot_value);
        $seat_position = 1;

        // if ($tables) {
        //     foreach ($tables as $value) {
        //         if ($value->members<5) {
        //             $TableId = $value->ludo_table_id;
        //             $seat_position = $this->Ludo_model->GetSeatOnTable($TableId);
        //         }
        //     }
        // }

        // if (empty($TableId)) {
        $table_data = [
            'invite_code' => $this->data['invite_code'],
            'boot_value' => $isMaster[0]->boot_value,
            'maximum_blind' => 4,
            'chaal_limit' => $isMaster[0]->chaal_limit,
            'pot_limit' => $isMaster[0]->pot_limit,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];
        $TableId = $this->Ludo_model->CreateTable($table_data);
        // $this->sendNotification($TableId);

        // $bot = $this->Users_model->GetFreeBot();

        // if ($bot) {
            //     $table_bot_data = [
            //         'ludo_table_id' => $TableId,
            //         'user_id' => $bot[0]->id,
            //         'seat_position' => 2,
            //         'added_date' => date('Y-m-d H:i:s'),
            //         'updated_date' => date('Y-m-d H:i:s')
            //     ];

            //     $this->Ludo_model->AddTableUser($table_bot_data);
        // }
        // }

        $table_user_data = [
            'ludo_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => $seat_position,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Ludo_model->AddTableUser($table_user_data);

        $table_data = $this->Ludo_model->TableUser($TableId);

        $data['message'] = 'Success';
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function switch_table_post()
    {
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            // $table_data = $this->Ludo_model->TableUser($user[0]->ludo_table_id);
            $data['message'] = 'You Are Not On Table';
            // $data['table_data'] = $table_data;
            $data['code'] = HTTP_OK;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if ($game) {
            $this->Ludo_model->PackGame($this->data['user_id'], $game->id);
            $game_users = $this->Ludo_model->GameUser($game->id);

            if (count($game_users)==1) {
                $comission = $this->Setting_model->Setting()->admin_commission;
                $this->Ludo_model->MakeWinner($game->id, $game->amount, $game_users[0]->user_id, $comission);
            }
        }

        $table_amount = 500;
        $table_data = [
            'boot_value' => $table_amount,
            'maximum_blind' => 4,
            'chaal_limit' => $table_amount*128,
            'pot_limit' => $table_amount*1024,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $seat_position = 1;
        $tables = $this->Ludo_model->getPublicActiveTable();

        if ($tables) {
            foreach ($tables as $value) {
                if ($user[0]->ludo_table_id!=$value->ludo_table_id) {
                    if ($value->members<5) {
                        $TableId = $value->ludo_table_id;
                        $seat_position = $this->Ludo_model->GetSeatOnTable($TableId);
                    }
                }
            }
        }

        $table_user_data = [
            'ludo_table_id' => $user[0]->ludo_table_id,
            'user_id' => $user[0]->id
        ];

        $this->Ludo_model->RemoveTableUser($table_user_data);

        if (empty($TableId)) {
            $TableId = $this->Ludo_model->CreateTable($table_data);
            // $this->sendNotification($TableId);

            $bot = $this->Users_model->GetFreeBot();

            $table_bot_data = [
                'ludo_table_id' => $TableId,
                'user_id' => $bot[0]->id,
                'seat_position' => 2,
                'added_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $this->Ludo_model->AddTableUser($table_bot_data);
        }

        $table_user_data = [
            'ludo_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => $seat_position,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Ludo_model->AddTableUser($table_user_data);

        $table_data = $this->Ludo_model->TableUser($TableId);

        $data['message'] = 'Success';
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function get_private_table_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['boot_value'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // if ($user[0]->wallet<10000) {
        //     $data['message'] = 'Required Minimum 10,000 Coins to Play';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        if ($user[0]->ludo_table_id) {
            $data['message'] = 'You are Already On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $table_data = [
            'boot_value' => $this->data['boot_value'],
            'maximum_blind' => 4,
            'chaal_limit' => $this->data['boot_value']*128,
            'pot_limit' => $this->data['boot_value']*1024,
            'private' => 2,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $TableId = $this->Ludo_model->CreateTable($table_data);

        $table_user_data = [
            'ludo_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => 1,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Ludo_model->AddTableUser($table_user_data);

        $table_data = $this->Ludo_model->TableUser($TableId);

        $data['message'] = 'Success';
        $data['ludo_table_id'] = $TableId;
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function get_customise_table_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['boot_value'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // if ($user[0]->wallet<10000) {
        //     $data['message'] = 'Required Minimum 10,000 Coins to Play';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        if ($user[0]->ludo_table_id) {
            $data['message'] = 'You are Already On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $tables = $this->Ludo_model->getCustomizeActiveTable($this->data['boot_value']);

        $seat_position = 1;
        if ($tables) {
            foreach ($tables as $value) {
                if ($value->members<5) {
                    $TableId = $value->ludo_table_id;
                    $seat_position = $this->Ludo_model->GetSeatOnTable($TableId);
                }
            }
        }

        if (empty($TableId)) {
            $table_data = [
                'boot_value' => $this->data['boot_value'],
                'maximum_blind' => 4,
                'chaal_limit' => $this->data['boot_value']*128,
                'pot_limit' => $this->data['boot_value']*1024,
                'private' => 2,
                'added_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $TableId = $this->Ludo_model->CreateTable($table_data);
            $this->sendNotification($TableId);
        }

        $table_user_data = [
            'ludo_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => $seat_position,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Ludo_model->AddTableUser($table_user_data);

        $table_data = $this->Ludo_model->TableUser($TableId);

        $data['message'] = 'Success';
        $data['ludo_table_id'] = $TableId;
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function join_table_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['invite_code'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->ludo_table_id) {
            $data['message'] = 'You are Already On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $data['ludo_table_id'] = $user[0]->ludo_table_id;
            $this->response($data, 200);
            exit();
        }

        // if (!$this->Ludo_model->isTable($this->data['ludo_table_id'])) {
        //     $data['message'] = 'Invalid Table Id';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        $table = $this->Ludo_model->isTableAvailInvite($this->data['invite_code']);
        if (!$table) {
            $data['message'] = 'Invalid Table Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<$table->boot_value) {
            $data['message'] = 'Required Minimum '.$table->boot_value.' Coins to Play';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if($seat = $this->Ludo_model->GetSeatOnTable($table->id)>0){
            $table_user_data = [
                'ludo_table_id' => $table->id,
                'user_id' => $user[0]->id,
                'seat_position' => $seat,
                'added_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s')
            ];
            $this->Ludo_model->AddTableUser($table_user_data);

            $table_data = $this->Ludo_model->TableUser($table->id);

            $data['message'] = 'Success';
            $data['table_data'] = $table_data;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }
        else{
            $data['message'] = 'Seat Not Available';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function start_game_post()
    {
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $table_data = $this->Ludo_model->TableUser($user[0]->ludo_table_id);

        if (count($table_data)<2) {
            $data['message'] = 'Unable to Create Game, Only One User On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }


        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if ($game) {
            $data['message'] = 'Active Game is Going On';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $bot_user_id = 0;
        // $cards_not_want = array();
        // if (count($table_data)>2) {
        //     foreach ($table_data as $key => $value) {
        //         if ($value->user_type==1) {
        //             $table_user_data = [
        //                 'ludo_table_id' => $value->ludo_table_id,
        //                 'user_id' => $value->user_id
        //             ];

        //             $this->Ludo_model->RemoveTableUser($table_user_data);
        //             $table_data = $this->Ludo_model->TableUser($user[0]->ludo_table_id);
        //         }
        //     }
        // } else {
        //     foreach ($table_data as $key => $value) {
        //         if ($value->user_type==1) {
        //             $bot_user_id = $value->user_id;
        //         }
        //     }
        // }

        $table = $this->Ludo_model->isTableAvail($user[0]->ludo_table_id);
        $amount = $table->boot_value;
        $game_data = [
            'ludo_table_id' => $user[0]->ludo_table_id,
            'amount' => count($table_data)*$amount,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $GameId = $this->Ludo_model->Create($game_data);

        // $Cards = $this->Ludo_model->GetCards(count($table_data)*3);

        foreach ($table_data as $key => $value) {
            // if ($bot_user_id==$value->user_id) {
            //     $botCards = $this->Ludo_model->GetCards(3, $cards_not_want);
            //     $table_user_data = [
            //         'game_id' => $GameId,
            //         'user_id' => $value->user_id,
            //         'card1' => $botCards[0]->cards,
            //         'card2' => $botCards[1]->cards,
            //         'card3' => $botCards[2]->cards,
            //         'added_date' => date('Y-m-d H:i:s'),
            //         'updated_date' => date('Y-m-d H:i:s')
            //     ];
            // } else {
            //     $cards_not_want = array($Cards[$key*3]->id,$Cards[$key*3+1]->id,$Cards[$key*3+2]->id);

            //     $table_user_data = [
            //         'game_id' => $GameId,
            //         'user_id' => $value->user_id,
            //         'card1' => $Cards[$key*3]->cards,
            //         'card2' => $Cards[($key*3)+1]->cards,
            //         'card3' => $Cards[($key*3)+2]->cards,
            //         'added_date' => date('Y-m-d H:i:s'),
            //         'updated_date' => date('Y-m-d H:i:s')
            //     ];
            // }

            // $this->Ludo_model->GiveGameCards($table_user_data);

            $this->Ludo_model->MinusWallet($value->user_id, $amount);
            $this->Ludo_model->UpdateGameAmount($value->id, $amount);

            // $this->Ludo_model->AddGameCount($value->user_id);

            // $game_log = [
            //     'game_id' => $GameId,
            //     'user_id' => $value->user_id,
            //     'action' => 0,
            //     'amount' => $amount,
            //     'added_date' => date('Y-m-d H:i:s')
            // ];

            // $this->Ludo_model->AddGameLog($game_log);
        }

        // if ($bot_user_id) {
        // }

        $data['message'] = 'Success';
        $data['game_id'] = $GameId;
        $data['table_amount'] = $amount;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function see_card_post()
    {
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
        $cards = $this->Ludo_model->getMyCards($game->id, $this->data['user_id']);
        ;

        $data['message'] = 'Success';
        $data['cards'] = $cards;
        $data['CardValue'] = $this->Ludo_model->CardValue($cards[0]->card1, $cards[0]->card2, $cards[0]->card3);
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function leave_table_post()
    {
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $table_data = $this->Ludo_model->TableUser($user[0]->ludo_table_id);

        // foreach ($table_data as $value) {
        // if($value->mobile)
        $table_user_data = [
                'ludo_table_id' => $user[0]->ludo_table_id,
                'user_id' => $user[0]->id
            ];

        $this->Ludo_model->RemoveTableUser($table_user_data);
        $this->Ludo_model->DeleteTable($user[0]->ludo_table_id);
        // }

        // $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        // if ($game) {
        //     $this->Ludo_model->PackGame($this->data['user_id'], $game->id);
        //     $game_users = $this->Ludo_model->GameUser($game->id);

        //     if (count($game_users)==1) {
        //         $comission = $this->Setting_model->Setting()->admin_commission;
        //         $this->Ludo_model->MakeWinner($game->id, $game->amount, $game_users[0]->user_id, $comission);
        //     }
        // }

        // $table_users = $this->Ludo_model->TableUser($user[0]->ludo_table_id);

        // if (count($table_users)==1) {
        //     if ($table_users[0]->mobile=="") {
        //         $table_user_data = [
        //             'ludo_table_id' => $table_users[0]->ludo_table_id,
        //             'user_id' => $table_users[0]->user_id
        //         ];

        //         $this->Ludo_model->RemoveTableUser($table_user_data);
        //     }
        // }

        $data['message'] = 'Success';
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function pack_game_post()
    {
        $timeout = $this->input->post('timeout');
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Ludo_model->GameLog($game->id, 1);

        $game_users = $this->Ludo_model->GameAllUser($game->id);

        $chaal = 0;
        $element = 0;
        foreach ($game_users as $key => $value) {
            if ($value->user_id==$game_log[0]->user_id) {
                $element = $key;
                break;
            }
        }

        $index = 0;
        foreach ($game_users as $key => $value) {
            $index = ($key+$element)%count($game_users);
            if ($key>0) {
                if (!$game_users[$index]->packed) {
                    $chaal = $game_users[$index]->user_id;
                    break;
                }
            }
        }

        if ($chaal==$this->data['user_id']) {
            $this->Ludo_model->PackGame($this->data['user_id'], $game->id, $timeout);
            $game_users = $this->Ludo_model->GameUser($game->id);

            if (count($game_users)==1) {
                $comission = $this->Setting_model->Setting()->admin_commission;
                $this->Ludo_model->MakeWinner($game->id, $game->amount, $game_users[0]->user_id, $comission);
            }

            if ($timeout==1) {
                $table_user_data = [
                    'ludo_table_id' => $user[0]->ludo_table_id,
                    'user_id' => $user[0]->id
                ];

                $this->Ludo_model->RemoveTableUser($table_user_data);
            }

            $data['message'] = 'Success';
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }

        $data['message'] = 'Invalid Pack';
        $data['code'] = HTTP_NOT_ACCEPTABLE;
        $this->response($data, 200);
        exit();
    }

    public function chaal_post()
    {
        $plus = $this->input->post('plus');
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);

        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $lastChal = $this->Ludo_model->LastChaal($game->id);

        $seen = $lastChal->seen;
        $amount = $lastChal->amount;

        $card_seen = $this->Ludo_model->isCardSeen($game->id, $user[0]->id);

        if ($seen==0 && $card_seen==1) {
            $amount = $amount*2;
        }

        if ($seen==1 && $card_seen==0) {
            $amount = $amount/2;
        }

        if ($plus) {
            $amount = $amount*2;
        }

        if ($user[0]->wallet<$amount) {
            $data['message'] = 'Insufficient Coins For '.$amount.' Coins Chaal';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Ludo_model->GameLog($game->id, 1);

        $game_users = $this->Ludo_model->GameAllUser($game->id);

        $chaal = 0;
        $element = 0;
        foreach ($game_users as $key => $value) {
            if ($value->user_id==$game_log[0]->user_id) {
                $element = $key;
                break;
            }
        }

        $index = 0;
        foreach ($game_users as $key => $value) {
            $index = ($key+$element)%count($game_users);
            if ($key>0) {
                if (!$game_users[$index]->packed) {
                    $chaal = $game_users[$index]->user_id;
                    break;
                }
            }
        }

        if ($chaal==$this->data['user_id']) {
            $table = $this->Ludo_model->isTableAvail($user[0]->ludo_table_id);

            if ($table->pot_limit <= ($game->amount+$amount)) {
                $this->Ludo_model->Show($game->id, $amount, $this->data['user_id']);
                $active_game_users = $this->Ludo_model->GameUser($game->id);
                $winner = 0;
                foreach ($active_game_users as $k => $val) {
                    // echo $winner;
                    $user1 = $this->Ludo_model->CardValue($active_game_users[$winner]->card1, $active_game_users[$winner]->card2, $active_game_users[$winner]->card3);
                    $user2 = $this->Ludo_model->CardValue($active_game_users[$k+1]->card1, $active_game_users[$k+1]->card2, $active_game_users[$k+1]->card3);

                    $winner_pos = $this->Ludo_model->getPotWinnerPosition($user1, $user2);
                    $winner = ($winner_pos==0) ? $winner : $k+1;

                    if (($k+2)==count($active_game_users)) {
                        $user_id = $active_game_users[$winner]->user_id;
                        break;
                    }
                }

                $comission = $this->Setting_model->Setting()->admin_commission;
                $this->Ludo_model->MakeWinner($game->id, $game->amount+$amount, $user_id, $comission);
                $data['message'] = 'Pot Show';
                $data['winner'] = $user_id;
                $data['code'] = HTTP_OK;
                $this->response($data, HTTP_OK);
                exit();
            } else {
                $this->Ludo_model->Chaal($game->id, $amount, $this->data['user_id']);
                if (count($game_users)==2) {
                    $bot_id = $this->Ludo_model->getGameBot($game->id);
                    if ($bot_id) {
                        sleep(10);
                        $this->Ludo_model->Chaal($game->id, $amount, $bot_id);
                    }
                }
            }

            $data['message'] = 'Success';
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }

        $data['message'] = 'Invalid Chaal';
        $data['code'] = HTTP_NOT_ACCEPTABLE;
        $this->response($data, 200);
        exit();
    }

    public function make_winner_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['winner_user_id']) || empty($this->data['table_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $winner_user_id = $this->data['winner_user_id'];
        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // if (!$user[0]->ludo_table_id) {
        //     $data['message'] = 'You Are Not On Table';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        $game = $this->Ludo_model->getActiveGameOnTable($this->data['table_id']);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $amount = $this->Ludo_model->LastChaalAmount($game->id);
        // $lastChal = $this->Ludo_model->LastChaal($game->id);

        // $seen = $lastChal->seen;
        // $amount = $lastChal->amount;

        // $card_seen = $this->Ludo_model->isCardSeen($game->id, $user[0]->id);

        // if ($seen==0 && $card_seen==1) {
        //     $amount = $amount*2;
        // }

        // if ($seen==1 && $card_seen==0) {
        //     $amount = $amount/2;
        // }

        // if ($plus) {
        //     $amount = $amount*2;
        // }

        // if ($user[0]->wallet<$amount) {
        //     $data['message'] = 'Insufficient Coins';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $game_log = $this->Ludo_model->GameLog($game->id, 1);

        // $game_users = $this->Ludo_model->GameAllUser($game->id);

        // $remain_game_users = $this->Ludo_model->GameUser($game->id);
        // // print_r($remain_game_users);

        // if (count($remain_game_users)!=2) {
        //     $data['message'] = 'Show can be done between 2 users only';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $chaal = 0;
        // $element = 0;
        // foreach ($game_users as $key => $value) {
        //     if ($value->user_id==$game_log[0]->user_id) {
        //         $element = $key;
        //         break;
        //     }
        // }

        // $index = 0;
        // foreach ($game_users as $key => $value) {
        //     $index = ($key+$element)%count($game_users);
        //     if ($key>0) {
        //         if (!$game_users[$index]->packed) {
        //             $chaal = $game_users[$index]->user_id;
        //             break;
        //         }
        //     }
        // }

        // // $game_users = $this->Ludo_model->GameUser($game->id);

        // if ($chaal==$this->data['user_id']) {
        //     $user1 = $this->Ludo_model->CardValue($remain_game_users[0]->card1, $remain_game_users[0]->card2, $remain_game_users[0]->card3);
        //     $user2 = $this->Ludo_model->CardValue($remain_game_users[1]->card1, $remain_game_users[1]->card2, $remain_game_users[1]->card3);

        //     $winner = $this->Ludo_model->getWinnerPosition($user1, $user2);

        //     if ($winner==2) {
        //         if ($remain_game_users[0]->user_id==$this->data['user_id']) {
        //             $user_id = $remain_game_users[1]->user_id;
        //         } else {
        //             $user_id = $remain_game_users[0]->user_id;
        //         }
        //     } else {
        //         $user_id = $remain_game_users[$winner]->user_id;
        //     }

        // $this->Ludo_model->Show($game->id, $amount, $this->data['user_id']);
        if ($game->winner_id==0) {
            $comission = $this->Setting_model->Setting()->admin_commission;
            $this->Ludo_model->MakeWinner($game->id, $game->amount, $winner_user_id, $comission);
            $data['message'] = 'Success';
            $data['winner'] = $winner_user_id;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }
        // }

        $data['message'] = 'Invalid Show';
        $data['code'] = HTTP_NOT_ACCEPTABLE;
        $this->response($data, 200);
        exit();
    }

    public function do_slide_show_post()
    {
        $user_id = $this->data['user_id'];
        $slide_id = $this->data['slide_id'];
        $type = $this->data['type']; //1=accept,2=reject
        if (empty($user_id) || empty($slide_id) || empty($type)) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($user_id, $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($user_id);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $slide = $this->Ludo_model->GetSlideShowById($slide_id);

        if ($type==1) {
            $user1 = $this->Ludo_model->GameUserCard($game->id, $slide->user_id);
            $user2 = $this->Ludo_model->GameUserCard($game->id, $slide->prev_id);
            $remain_game_users[] = $user1;
            $remain_game_users[] = $user2;

            $user1 = $this->Ludo_model->CardValue($remain_game_users[0]->card1, $remain_game_users[0]->card2, $remain_game_users[0]->card3);
            $user2 = $this->Ludo_model->CardValue($remain_game_users[1]->card1, $remain_game_users[1]->card2, $remain_game_users[1]->card3);

            $winner = $this->Ludo_model->getWinnerPosition($user1, $user2);

            if ($winner==2) {
                $looser_id = $remain_game_users[0]->user_id;
            } else {
                $looser = ($winner==1) ? 0 : 1;
                $looser_id = $remain_game_users[$looser]->user_id;
            }

            $this->Ludo_model->PackGame($looser_id, $game->id);
        }

        $this->Ludo_model->UpdateSlideShow($slide_id, $type);

        $lastChal = $this->Ludo_model->LastChaal($game->id);

        $seen = $lastChal->seen;
        $amount = $lastChal->amount;

        $card_seen = $this->Ludo_model->isCardSeen($game->id, $slide->user_id);

        if ($seen==0 && $card_seen==1) {
            $amount = $amount*2;
        }

        if ($seen==1 && $card_seen==0) {
            $amount = $amount/2;
        }

        $this->Ludo_model->Chaal($game->id, $amount, $slide->user_id);

        $data['message'] = 'Success';
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function slide_show_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['prev_user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $prev_user = $this->Users_model->UserProfile($this->data['prev_user_id']);
        if (empty($prev_user)) {
            $data['message'] = 'Invalid Previous User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$prev_user[0]->ludo_table_id) {
            $data['message'] = 'Previous Player Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->ludo_table_id!=$prev_user[0]->ludo_table_id) {
            $data['message'] = 'Players Are Not Same Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $amount = $this->Ludo_model->LastChaalAmount($game->id);
        if ($user[0]->wallet<$amount) {
            $data['message'] = 'Insufficient Coins';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Ludo_model->GameLog($game->id, 1);

        $game_users = $this->Ludo_model->GameAllUser($game->id);

        $remain_game_users = $this->Ludo_model->GameUser($game->id);
        // print_r($remain_game_users);

        if (count($remain_game_users)==2) {
            $data['message'] = 'Slide Show can not be done between 2 users only';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $chaal = 0;
        $element = 0;
        foreach ($game_users as $key => $value) {
            if ($value->user_id==$game_log[0]->user_id) {
                $element = $key;
                break;
            }
        }

        $index = 0;
        foreach ($game_users as $key => $value) {
            $index = ($key+$element)%count($game_users);
            if ($key>0) {
                if (!$game_users[$index]->packed) {
                    $chaal = $game_users[$index]->user_id;
                    break;
                }
            }
        }

        if ($chaal==$this->data['user_id']) {
            $slide_id = $this->Ludo_model->SlideShow($game->id, $this->data['user_id'], $this->data['prev_user_id']);
            $data['message'] = 'Success';
            $data['slide_id'] = $slide_id;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }

        $data['message'] = 'Invalid Show';
        $data['code'] = HTTP_NOT_ACCEPTABLE;
        $this->response($data, 200);
        exit();
    }

    public function chat_post()
    {
        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // if(!$user[0]->ludo_table_id)
        // {
        //     $data['message'] = 'You Are Not On Table';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $game = $this->Ludo_model->getActiveGameOnTable($user[0]->ludo_table_id);

        // if(!$game)
        // {
        //     $data['message'] = 'Game Not Started';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        $game['id'] = 1000;
        $game = (object) $game;

        $chat = $this->input->post('chat');

        if (!empty($chat)) {
            $chat_data = [
                'user_id' => $this->data['user_id'],
                'chat' => $chat,
                'game_id' => $game->id
            ];

            $this->Ludo_model->Chat($chat_data);
        }

        $chat_list = $this->Ludo_model->ChatList($game->id);
        $data['message'] = 'Success';
        $data['list'] = $chat_list;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function tip_post()
    {
        if (empty($this->data['user_id'] && $this->data['tip'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$user[0]->ludo_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<$this->data['tip']) {
            $data['message'] = 'Insufficiant Tip Coins';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($this->Users_model->TipAdmin($this->data['tip'], $this->data['user_id'], $user[0]->ludo_table_id, $this->data['gift_id'], $this->data['to_user_id'])) {
            $data['message'] = 'Success';
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }

        $data['message'] = 'Invalid Tip';
        $data['code'] = HTTP_NOT_ACCEPTABLE;
        $this->response($data, 200);
        exit();
    }

    public function status_post()
    {
        $ludo_table_id = $this->input->post('ludo_table_id');
        $user_id = $this->input->post('user_id');

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $table = $this->Ludo_model->isTableAvail($ludo_table_id);

        if (!$table) {
            $data['message'] = 'Invalid Table';
            $data['code'] = HTTP_BLANK;
            $this->response($data, 200);
            exit();
        }

        if (!empty($ludo_table_id)) {
            $table_data = $this->Ludo_model->TableUser($ludo_table_id);
            $data['table_users'] = $table_data;

            // $table_new_data = array();
            // for ($i=0; $i < 5; $i++) {
            //     $table_new_data[$i]['id'] = 0;
            //     $table_new_data[$i]['ludo_table_id'] = 0;
            //     $table_new_data[$i]['user_id'] = 0;
            //     $table_new_data[$i]['seat_position'] = $i+1;
            //     $table_new_data[$i]['added_date'] = 0;
            //     $table_new_data[$i]['updated_date'] = 0;
            //     $table_new_data[$i]['isDeleted'] = 0;
            //     $table_new_data[$i]['name'] = 0;
            //     $table_new_data[$i]['mobile'] = 0;
            //     $table_new_data[$i]['profile_pic'] = 0;
            //     $table_new_data[$i]['wallet'] = 0;
            // }

            // foreach ($table_data as $t => $u) {
            //     $table_new_data[$u->seat_position-1] = $u;
            // }

            // $data['table_users'] = $table_new_data;

            // $table_updated_users = $table_data;
            // // foreach ($table_data as $key => $value) {
            // //     $table_updated_users[] = $value;
            // // }

            // $found = false;
            // $plus_position = 0;
            // foreach($table_updated_users as $k=>$v)
            // {
            //     $found = $found || $v->user_id===$user_id;
            //     if(!$found)
            //     {
            //         $plus_position=$k+1;
            //         unset($table_updated_users[$k]);
            //         $table_updated_users[] = $v;
            //     }
            //     //else break can be added for performance issues
            // }
            // // echo $plus_position;
            // $i=0;
            // foreach ($table_updated_users as $ke => $va) {
            //     $table_final_users[$i] = $va;
            //     $table_final_users[$i]->seat_position = ($va->seat_position+$plus_position)%5;
            //     $i++;
            // }

            // $data['table_final_users'] = $table_final_users;
            // $data['table_detail'] = $table;
            // $data['active_game_id'] = 0;
            // $data['game_status'] = 0;
            // $data['table_amount'] = 50;
            // $active_game = $this->Ludo_model->getActiveGameOnTable($ludo_table_id);
            // if ($active_game) {
            //     $data['active_game_id'] = $active_game->id;
            //     $data['game_status'] = 1;
            // }
        }

        // $game_id = $this->input->post('game_id');
        // if (empty($game_id)) {
        //     $data['message'] = 'Invalid Parameter';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $game = $this->Ludo_model->View($game_id);
        // if (empty($game)) {
        //     $data['message'] = 'Invalid Game';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $game_log = $this->Ludo_model->GameLog($game_id, 1);

        // $game_users = $this->Ludo_model->GameAllUser($game_id);

        // $chaal = 0;
        // $element = 0;
        // foreach ($game_users as $key => $value) {
        //     if ($value->user_id==$game_log[0]->user_id) {
        //         $element = $key;
        //         break;
        //     }
        // }

        // $index = 0;
        // foreach ($game_users as $key => $value) {
        //     $index = ($key+$element)%count($game_users);

        //     if ($key>0) {
        //         if (!$game_users[$index]->packed) {
        //             $chaal = $game_users[$index]->user_id;
        //             break;
        //         }
        //     }
        // }

        // $data['game_log'] = $game_log;
        // $data['all_users'] = $table_data;
        // if ($game_log[0]->action==3) {
        //     $data['game_users'] = $this->Ludo_model->GameAllUser($game_id);
        // } else {
        //     $data['game_users'] = $this->Ludo_model->GameOnlyUser($game_id);
        // }
        // $data['chaal'] = $chaal;
        // $data['game_amount'] = $game->amount;
        // $chaalCount = $this->Ludo_model->ChaalCount($game->id, $chaal);
        // if ($chaalCount>3) {
        //     $this->Ludo_model->getMyCards($game->id, $chaal);
        // }

        // if (!empty($user_id)) {
        //     $user_card_seen = $this->Ludo_model->isCardSeen($game->id, $user_id);
        //     $data['cards'] = array();
        //     if ($user_card_seen==1) {
        //         $data['cards'] = $this->Ludo_model->getMyCards($game->id, $user_id);
        //     }
        // }

        // if ($game) {
        //     $lastChal = $this->Ludo_model->LastChaal($game->id);

        //     $seen = $lastChal->seen;
        //     $amount = $lastChal->amount;

        //     $card_seen = $this->Ludo_model->isCardSeen($game->id, $chaal);

        //     if ($seen==0 && $card_seen==1) {
        //         $amount = $amount*2;
        //     }

        //     if ($seen==1 && $card_seen==0) {
        //         $amount = $amount/2;
        //     }

        //     $data['table_amount'] = $amount;
        //     $data['slide_show'] = $this->Ludo_model->GetSlideShow($game->id);
        // }
        // $data['game_gifts'] = $this->Users_model->GiftList($ludo_table_id);
        // $data['message'] = 'Success';
        // if ($game->winner_id>0) {
        //     $chaal = 0;
        //     $data['chaal'] = $chaal;
        //     $data['message'] = 'Game Completed';
        //     $data['game_status'] = 2;
        //     $data['winner_user_id'] = $game->winner_id;
        // }
        $data['code'] = HTTP_OK;
        $data['message'] = 'Table Users';
        $this->response($data, HTTP_OK);
        exit();
    }
}