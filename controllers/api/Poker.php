<?php

use phpDocumentor\Reflection\Types\Object_;
use Restserver\Libraries\REST_Controller;

include APPPATH . '/libraries/REST_Controller.php';
include APPPATH . '/libraries/Format.php';
class Poker extends REST_Controller
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
            'Poker_model',
            'Users_model',
            'Setting_model'
        ]);
    }

    // public function sendNotification($TableId)
    // {
    //     $userdata = $this->Users_model->FreeUserList();

    //     foreach ($userdata as $value) {
    //         if (!empty($value->fcm)) {
    //             $data['msg'] = "New User Joined Table";
    //             $data['title'] = "Teen Patti";
    //             $data['poker_table_id'] = $TableId;
    //             push_notification_android($value->fcm, $data);
    //         }
    //     }
    // }

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

        if ($user[0]->poker_table_id) {
            $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);
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

        // $tables = $this->Poker_model->getPublicActiveTable();
        // $seat_position = 1;

        // if($tables)
        // {
        //     foreach ($tables as $value) {
        //         if($value->members<5)
        //         {
        //             $TableId = $value->poker_table_id;
        //             $seat_position = $this->Poker_model->GetSeatOnTable($TableId);
        //         }
        //     }
        // }

        // if(empty($TableId))
        // {
        //     $TableId = $this->Poker_model->CreateTable($table_data);
        //     // $this->sendNotification($TableId);

        //     $bot = $this->Users_model->GetFreeBot();

        //     $table_bot_data = [
        //         'poker_table_id' => $TableId,
        //         'user_id' => $bot[0]->id,
        //         'seat_position' => 2,
        //         'added_date' => date('Y-m-d H:i:s'),
        //         'updated_date' => date('Y-m-d H:i:s')
        //     ];

        //     $this->Poker_model->AddTableUser($table_bot_data);
        // }

        // $table_user_data = [
        //     'poker_table_id' => $TableId,
        //     'user_id' => $user[0]->id,
        //     'seat_position' => $seat_position,
        //     'added_date' => date('Y-m-d H:i:s'),
        //     'updated_date' => date('Y-m-d H:i:s')
        // ];

        // $this->Poker_model->AddTableUser($table_user_data);

        $table_data = $this->Poker_model->getTableMaster();

        $data['message'] = 'Success';
        $data['table_data'] = $table_data;
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
        $isMaster = $this->Poker_model->getTableMaster($this->data['boot_value']);
        if (empty($isMaster)) {
            $data['message'] = 'Invalid Boot Value';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }


        if ($user[0]->wallet<$isMaster[0]->boot_value) {
            $data['message'] = 'Required Minimum '.number_format($isMaster[0]->boot_value).' Coins to Play';
            // if ($user[0]->wallet<30) {
        //     $data['message'] = 'Required Minimum 30 Coins to Play';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->poker_table_id) {
            $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);
            $data['message'] = 'You are Already On Table';
            $data['table_data'] = $table_data;
            $data['code'] = HTTP_OK;
            $this->response($data, 200);
            exit();
        }

        $tables = $this->Poker_model->getCustomizeActiveTable($isMaster[0]->boot_value);
        $seat_position = 1;

        if ($tables) {
            foreach ($tables as $value) {
                if ($value->members<5) {
                    $TableId = $value->poker_table_id;
                    $seat_position = $this->Poker_model->GetSeatOnTable($TableId);
                }
            }
        }

        if (empty($TableId)) {
            $table_data = [
                'boot_value' => $isMaster[0]->boot_value,
                'maximum_blind' => 4,
                'chaal_limit' => $isMaster[0]->chaal_limit,
                'pot_limit' => $isMaster[0]->pot_limit,
                'added_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s')
            ];
            $TableId = $this->Poker_model->CreateTable($table_data);
            // $this->sendNotification($TableId);

            // $bot = $this->Users_model->GetFreeBot();

            // if ($bot) {
            //     $table_bot_data = [
            //         'poker_table_id' => $TableId,
            //         'user_id' => $bot[0]->id,
            //         'seat_position' => 2,
            //         'added_date' => date('Y-m-d H:i:s'),
            //         'updated_date' => date('Y-m-d H:i:s')
            //     ];

            //     $this->Poker_model->AddTableUser($table_bot_data);
            // }
        }

        $table_user_data = [
            'poker_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => $seat_position,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Poker_model->AddTableUser($table_user_data);

        $table_data = $this->Poker_model->TableUser($TableId);

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

        if (!$user[0]->poker_table_id) {
            // $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);
            $data['message'] = 'You Are Not On Table';
            // $data['table_data'] = $table_data;
            $data['code'] = HTTP_OK;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if ($game) {
            $this->Poker_model->PackGame($this->data['user_id'], $game->id);
            $game_users = $this->Poker_model->GameUser($game->id);

            if (count($game_users)==1) {
                $comission = $this->Setting_model->Setting()->admin_commission;
                $this->Poker_model->MakeWinner($game->id, $game->amount, $game_users[0]->user_id, $comission);
            }
        }
        $table = $this->Poker_model->isTableAvail($user[0]->poker_table_id);

        $table_amount = $table->boot_value;
        $table_data = [
            'boot_value' => $table_amount,
            'maximum_blind' => 4,
            'chaal_limit' => $table_amount*128,
            'pot_limit' => $table_amount*1024,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $seat_position = 1;
        // $tables = $this->Poker_model->getPublicActiveTable();
        $tables = $this->Poker_model->getCustomizeActiveTable($table_amount);

        if ($tables) {
            foreach ($tables as $value) {
                if ($user[0]->poker_table_id!=$value->poker_table_id) {
                    if ($value->members<5) {
                        $TableId = $value->poker_table_id;
                        $seat_position = $this->Poker_model->GetSeatOnTable($TableId);
                    }
                }
            }
        }

        $table_user_data = [
            'poker_table_id' => $user[0]->poker_table_id,
            'user_id' => $user[0]->id
        ];

        $this->Poker_model->RemoveTableUser($table_user_data);

        if (empty($TableId)) {
            $TableId = $this->Poker_model->CreateTable($table_data);
            // $this->sendNotification($TableId);

            $bot = $this->Users_model->GetFreeBot();

            $table_bot_data = [
                'poker_table_id' => $TableId,
                'user_id' => $bot[0]->id,
                'seat_position' => 2,
                'added_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $this->Poker_model->AddTableUser($table_bot_data);
        }

        $table_user_data = [
            'poker_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => $seat_position,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Poker_model->AddTableUser($table_user_data);

        $table_data = $this->Poker_model->TableUser($TableId);

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

        if ($user[0]->poker_table_id) {
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

        $TableId = $this->Poker_model->CreateTable($table_data);

        $table_user_data = [
            'poker_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => 1,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Poker_model->AddTableUser($table_user_data);

        $table_data = $this->Poker_model->TableUser($TableId);

        $data['message'] = 'Success';
        $data['poker_table_id'] = $TableId;
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

        if ($user[0]->poker_table_id) {
            $data['message'] = 'You are Already On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $tables = $this->Poker_model->getCustomizeActiveTable($this->data['boot_value']);

        $seat_position = 1;
        if ($tables) {
            foreach ($tables as $value) {
                if ($value->members<5) {
                    $TableId = $value->poker_table_id;
                    $seat_position = $this->Poker_model->GetSeatOnTable($TableId);
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

            $TableId = $this->Poker_model->CreateTable($table_data);
            $this->sendNotification($TableId);
        }

        $table_user_data = [
            'poker_table_id' => $TableId,
            'user_id' => $user[0]->id,
            'seat_position' => $seat_position,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Poker_model->AddTableUser($table_user_data);

        $table_data = $this->Poker_model->TableUser($TableId);

        $data['message'] = 'Success';
        $data['poker_table_id'] = $TableId;
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }

    public function join_table_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['poker_table_id'])) {
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

        if ($user[0]->poker_table_id) {
            $data['message'] = 'You are Already On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$this->Poker_model->isTable($this->data['poker_table_id'])) {
            $data['message'] = 'Invalid Table Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $table = $this->Poker_model->isTableAvail($this->data['poker_table_id']);
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

        $table_user_data = [
            'poker_table_id' => $this->data['poker_table_id'],
            'user_id' => $user[0]->id,
            'seat_position' => $this->Poker_model->GetSeatOnTable($this->data['poker_table_id']),
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $this->Poker_model->AddTableUser($table_user_data);

        $table_data = $this->Poker_model->TableUser($this->data['poker_table_id']);

        $data['message'] = 'Success';
        $data['table_data'] = $table_data;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);

        if (count($table_data)<2) {
            $data['message'] = 'Unable to Create Game, Only One User On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }


        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if ($game) {
            $data['message'] = 'Active Game is Going On';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (count($table_data)>2) {
            foreach ($table_data as $key => $value) {
                if ($value->user_type==1) {
                    $table_user_data = [
                        'poker_table_id' => $value->poker_table_id,
                        'user_id' => $value->user_id
                    ];

                    $this->Poker_model->RemoveTableUser($table_user_data);
                    $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);
                }
            }
        }

        // $round_table_data = $this->Poker_model->TableUserRound($user[0]->poker_table_id);
        foreach ($table_data as $ke => $val) {
            switch ($ke) {
                case '0':
                    $role = 1;
                    break;

                case '1':
                    $role = 2;
                    break;

                case '2':
                    $role = 3;
                    break;

                default:
                    $role = 0;
                    break;
            }
            $table_user_data = [
                'role' => $role
            ];

            $this->Poker_model->UpdateTableUser($val->id, $table_user_data);
        }

        $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);

        $table = $this->Poker_model->isTableAvail($user[0]->poker_table_id);
        $amount = $table->boot_value;
        $game_data = [
            'poker_table_id' => $user[0]->poker_table_id,
            'amount' => $amount,
            'added_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        $GameId = $this->Poker_model->Create($game_data);

        $Cards = $this->Poker_model->GetCards((count($table_data)*2));

        $card_count=0;
        foreach ($table_data as $key => $value) {
            switch ($key) {
                case '0':
                    $role = 1;
                    break;

                case '1':
                    $role = 2;
                    break;

                case '2':
                    $role = 3;
                    break;

                default:
                    $role = 0;
                    break;
            }
            $table_user_data = [
                'game_id' => $GameId,
                'user_id' => $value->user_id,
                'card1' => $Cards[$key*2]->cards,
                'card2' => $Cards[($key*2)+1]->cards,
                'role' => $role,
                'added_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $card_count++;
            $card_count++;

            $this->Poker_model->GiveGameCards($table_user_data);

            if ($key==0) {
                $this->Poker_model->MinusWallet($value->user_id, $amount);

                $this->Poker_model->AddGameCount($value->user_id);

                $game_log = [
                    'game_id' => $GameId,
                    'user_id' => $value->user_id,
                    'action' => 0,
                    'round' => 1,
                    'amount' => $amount,
                    'added_date' => date('Y-m-d H:i:s')
                ];

                $this->Poker_model->AddGameLog($game_log);
            }
        }

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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
        $cards = $this->Poker_model->getMyCards($game->id, $this->data['user_id']);
        ;

        $data['message'] = 'Success';
        $data['cards'] = $cards;
        $data['CardValue'] = $this->Poker_model->CardValue($cards[0]->card1, $cards[0]->card2);
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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $table_data = $this->Poker_model->TableUser($user[0]->poker_table_id);

        // foreach ($table_data as $value) {
        // if($value->mobile)
        $table_user_data = [
                'poker_table_id' => $user[0]->poker_table_id,
                'user_id' => $user[0]->id
            ];

        $this->Poker_model->RemoveTableUser($table_user_data);
        // }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if ($game) {
            $this->Poker_model->PackGame($this->data['user_id'], $game->id);
            $game_users = $this->Poker_model->GameUser($game->id);

            if (count($game_users)==1) {
                $comission = $this->Setting_model->Setting()->admin_commission;
                $this->Poker_model->MakeWinner($game->id, $game->amount, $game_users[0]->user_id, $comission);
            }
        }

        $table_users = $this->Poker_model->TableUser($user[0]->poker_table_id);

        if (count($table_users)==1) {
            if ($table_users[0]->mobile=="") {
                $table_user_data = [
                    'poker_table_id' => $table_users[0]->poker_table_id,
                    'user_id' => $table_users[0]->user_id
                ];

                $this->Poker_model->RemoveTableUser($table_user_data);
            }
        }

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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Poker_model->GameLog($game->id, 1);

        $game_users = $this->Poker_model->GameAllUser($game->id);

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
            $this->Poker_model->PackGame($this->data['user_id'], $game->id, $timeout);
            $game_users = $this->Poker_model->GameUser($game->id);

            if (count($game_users)==1) {
                $comission = $this->Setting_model->Setting()->admin_commission;
                $this->Poker_model->MakeWinner($game->id, $game->amount, $game_users[0]->user_id, $comission);
            }

            if ($timeout==1) {
                $table_user_data = [
                    'poker_table_id' => $user[0]->poker_table_id,
                    'user_id' => $user[0]->id
                ];

                $this->Poker_model->RemoveTableUser($table_user_data);
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
        $rule = $this->input->post('rule');
        $rule_value = $this->input->post('value');
        $chaal_type = $this->input->post('chaal_type');
        $raise = $this->input->post('raise');
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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $lastChal = $this->Poker_model->LastChaal($game->id);
        $raise_amount = 0;
        switch ($raise) {
            case '1':
                // HalfPot
                $raise_amount = round($game->amount/2);
                break;

            case '2':
                // FullPot
                $raise_amount = $game->amount;
                break;

            default:
                $raise_amount = $lastChal->amount;
                break;
        }
        $amount = 0;
        if ($chaal_type!='1') {
            $amount = ($chaal_type=='3') ? $raise_amount : $lastChal->amount;
        }

        if ($user[0]->wallet<$amount) {
            $data['message'] = 'Insufficient Coins For '.$amount.' Coins Chaal';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Poker_model->GameLog($game->id, 1);

        $game_users = $this->Poker_model->GameAllUser($game->id);

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
            $table = $this->Poker_model->isTableAvail($user[0]->poker_table_id);

            $round = $lastChal->round;
            $game_users = $this->Poker_model->GameUser($game->id);
            // $chaal_count = count($this->Poker_model->GameLog($game->id, '', $game_users[0]->user_id));
            $middle_card_count = count($this->Poker_model->getTableCards($game->id));
            $active_game_users = $this->Poker_model->GameUser($game->id);

            $is_equal = true;
            $max_amount = 0;
            $increase_round=false;
            foreach ($active_game_users as $key => $value) {
                $user_game_amount = $this->Poker_model->GameTotalAmount($game->id, $value->user_id);
                $max_amount = ($max_amount>$user_game_amount) ? $max_amount : $user_game_amount;
            }

            $user_amount = $this->Poker_model->GameTotalAmount($game->id, $this->data['user_id']);
            // echo $max_amount;
            $diff_amount = $max_amount - $user_amount;
            if ($chaal_type=='2') {
                $lastChal_amount = $this->Poker_model->LastChaalAmount($game->id);
                $amount = ($diff_amount==0) ? $lastChal_amount : $diff_amount;
            }
            // if ($diff_amount>0) {
            $diff_amount = $diff_amount - $amount;
            if ($diff_amount!=0) {
                $is_equal = false;
            }

            if ($is_equal) {
                foreach ($active_game_users as $key => $value) {
                    if ($value->user_id!=$this->data['user_id']) {
                        $user_game_amount = $this->Poker_model->GameTotalAmount($game->id, $value->user_id);
                        $diff_amount = $max_amount - $user_game_amount;
                        if ($diff_amount!=0) {
                            $is_equal = false;
                            break;
                        }
                    }
                }
            }

            // }
            // if ($middle_card_count!=5) {
            switch ($middle_card_count) {
                case 0:
                    $round = 1;
                    break;

                case 3:
                    $round = 2;
                    break;

                case 4:
                    $round = 3;
                    break;

                case 5:
                    $round = 4;
                    break;

                case 6:
                    $round = 5;
                    break;

                default:
                    $round = 1;
                    break;
            }
            $this->Poker_model->Chaal($game->id, $amount, $this->data['user_id'], $round, $rule, $rule_value, $chaal_type);
            // }
            // $round_count = count($this->Poker_model->GameLog($game->id, '', '', $lastChal->round));
            $round_count = count($this->Poker_model->GameLog($game->id, '', '', $round));
            // echo count($active_game_users);
            // echo $round_count;
            if (count($active_game_users)<=($round_count) && $is_equal) {
                // echo 'done';
                // $increase_round = true;
                // $round = (count($active_game_users)==($round_count+1)) ? $lastChal->round : $lastChal->round+1;
                // $round = $lastChal->round+1;
                $increase_round = (count($active_game_users)<=($round_count+1)) ? true : false;
                // echo ($increase_round) ? 'yes' : 'no';
                // exit;
            }
            // echo 'hi';
            // exit;
            // echo ($increase_round) ? '1' : '2';
            // echo $middle_card_count;
            // if (($middle_card_count<5) && (($chaal_count-2)>($middle_card_count-3))) {
            if (($middle_card_count<6) && $increase_round==true) {
                if ($middle_card_count==5) {
                    // $round = $middle_card_count;
                    $this->Poker_model->Show($game->id, $amount, $this->data['user_id'], $round, $rule, $rule_value, $chaal_type);
                    // echo json_encode($active_game_users);
                    $winner = 0;
                    foreach ($active_game_users as $k => $val) {
                        // echo $winner;
                        // $user1 = $this->Poker_model->CardValue($active_game_users[$winner]->card1, $active_game_users[$winner]->card2);
                        // $user2 = $this->Poker_model->CardValue($active_game_users[$k+1]->card1, $active_game_users[$k+1]->card2);
                        $user1 = array($active_game_users[$winner]->rule,$active_game_users[$winner]->value);
                        $user2 = array($active_game_users[$k+1]->rule,$active_game_users[$k+1]->value);
                        $winner_pos = $this->Poker_model->getPotWinnerPosition($user1, $user2);
                        $winner = ($winner_pos==0) ? $winner : $k+1;

                        if (($k+2)==count($active_game_users)) {
                            $user_id = $active_game_users[$winner]->user_id;
                            break;
                        }
                    }
                    $comission = $this->Setting_model->Setting()->admin_commission;
                    $this->Poker_model->MakeWinner($game->id, $game->amount+$amount, $user_id, $comission);
                    $data['message'] = 'Pot Show';
                    $data['winner'] = $user_id;
                    $data['code'] = HTTP_OK;
                    $this->response($data, HTTP_OK);
                    exit();
                }

                $random_card = $this->Poker_model->GetRamdomGameCard($game->id);

                $table_card = [
                    'game_id' => $game->id,
                    'card' => $random_card[0]->cards
                ];

                // $round = $middle_card_count;
                $this->Poker_model->TableCards($table_card);

                // Flop
                if ($middle_card_count==0) {
                    // $round = 1;
                    for ($i=1; $i <= 2; $i++) {
                        $random_card = $this->Poker_model->GetRamdomGameCard($game->id);

                        $table_card = [
                            'game_id' => $game->id,
                            'card' => $random_card[0]->cards
                        ];
                        $this->Poker_model->TableCards($table_card);
                    }
                }
            }
            // elseif ($middle_card_count>=6) {
            //     // $round = $middle_card_count;
            //     $this->Poker_model->Show($game->id, $amount, $this->data['user_id'], $round, $rule, $rule_value, $chaal_type);
            //     // echo json_encode($active_game_users);
            //     $winner = 0;
            //     foreach ($active_game_users as $k => $val) {
            //         // echo $winner;
            //         // $user1 = $this->Poker_model->CardValue($active_game_users[$winner]->card1, $active_game_users[$winner]->card2);
            //         // $user2 = $this->Poker_model->CardValue($active_game_users[$k+1]->card1, $active_game_users[$k+1]->card2);
            //         $user1 = array($active_game_users[$winner]->rule,$active_game_users[$winner]->value);
            //         $user2 = array($active_game_users[$k+1]->rule,$active_game_users[$k+1]->value);
            //         $winner_pos = $this->Poker_model->getPotWinnerPosition($user1, $user2);
            //         $winner = ($winner_pos==0) ? $winner : $k+1;

            //         if (($k+2)==count($active_game_users)) {
            //             $user_id = $active_game_users[$winner]->user_id;
            //             break;
            //         }
            //     }
            //     $comission = $this->Setting_model->Setting()->admin_commission;
            //     $this->Poker_model->MakeWinner($game->id, $game->amount+$amount, $user_id, $comission);
            //     $data['message'] = 'Pot Show';
            //     $data['winner'] = $user_id;
            //     $data['code'] = HTTP_OK;
            //     $this->response($data, HTTP_OK);
            //     exit();
            // }

            // echo $amount;

            // if ($table->pot_limit <= ($game->amount+$amount)) {
            //     $this->Poker_model->Show($game->id, $amount, $this->data['user_id']);
            //     $active_game_users = $this->Poker_model->GameUser($game->id);
            //     $winner = 0;
            //     foreach ($active_game_users as $k => $val) {
            //         // echo $winner;
            //         $user1 = $this->Poker_model->CardValue($active_game_users[$winner]->card1, $active_game_users[$winner]->card2);
            //         $user2 = $this->Poker_model->CardValue($active_game_users[$k+1]->card1, $active_game_users[$k+1]->card2);

            //         $winner_pos = $this->Poker_model->getPotWinnerPosition($user1, $user2);
            //         $winner = ($winner_pos==0) ? $winner : $k+1;

            //         if (($k+2)==count($active_game_users)) {
            //             $user_id = $active_game_users[$winner]->user_id;
            //             break;
            //         }
            //     }

            //     $comission = $this->Setting_model->Setting()->admin_commission;
            //     $this->Poker_model->MakeWinner($game->id, $game->amount+$amount, $user_id, $comission);
            //     $data['message'] = 'Pot Show';
            //     $data['winner'] = $user_id;
            //     $data['code'] = HTTP_OK;
            //     $this->response($data, HTTP_OK);
            //     exit();
            // } else {
            // $this->Poker_model->Chaal($game->id, $amount, $this->data['user_id'], $round, $rule, $rule_value, $chaal_type);
            // if (count($game_users)==2) {
            //     $bot_id = $this->Poker_model->getGameBot($game->id);
            //     if ($bot_id) {
            //         sleep(10);
            //         $this->Poker_model->Chaal($game->id, $amount, $bot_id);
            //     }
            // }
            // }

            $data['message'] = 'Success';
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }


        // $middle_card_count = count($this->Poker_model->getTableCards($game->id));
        // if($chaal_count){
        //     $random_card = $this->Poker_model->GetRamdomGameCard($game->id);

        //     $table_card = [
        //         'game_id' => $game->id,
        //         'card' => $random_card[0]->cards
        //     ];
        //     $this->Poker_model->TableCards($table_card);
        // }

        $data['message'] = 'Invalid Chaal';
        $data['code'] = HTTP_NOT_ACCEPTABLE;
        $this->response($data, 200);
        exit();
    }

    public function show_game_post()
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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $amount = $this->Poker_model->LastChaalAmount($game->id);
        $lastChal = $this->Poker_model->LastChaal($game->id);

        $seen = $lastChal->seen;
        $amount = $lastChal->amount;

        $card_seen = $this->Poker_model->isCardSeen($game->id, $user[0]->id);

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
            $data['message'] = 'Insufficient Coins';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Poker_model->GameLog($game->id, 1);

        $game_users = $this->Poker_model->GameAllUser($game->id);

        $remain_game_users = $this->Poker_model->GameUser($game->id);
        // print_r($remain_game_users);

        if (count($remain_game_users)!=2) {
            $data['message'] = 'Show can be done between 2 users only';
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

        // $game_users = $this->Poker_model->GameUser($game->id);

        if ($chaal==$this->data['user_id']) {
            $user1 = $this->Poker_model->CardValue($remain_game_users[0]->card1, $remain_game_users[0]->card2);
            $user2 = $this->Poker_model->CardValue($remain_game_users[1]->card1, $remain_game_users[1]->card2);

            $winner = $this->Poker_model->getWinnerPosition($user1, $user2);

            if ($winner==2) {
                if ($remain_game_users[0]->user_id==$this->data['user_id']) {
                    $user_id = $remain_game_users[1]->user_id;
                } else {
                    $user_id = $remain_game_users[0]->user_id;
                }
            } else {
                $user_id = $remain_game_users[$winner]->user_id;
            }

            $this->Poker_model->Show($game->id, $amount, $this->data['user_id']);
            $comission = $this->Setting_model->Setting()->admin_commission;
            $this->Poker_model->MakeWinner($game->id, $game->amount+$amount, $user_id, $comission);
            $data['message'] = 'Success';
            $data['winner'] = $user_id;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }

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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $slide = $this->Poker_model->GetSlideShowById($slide_id);

        if ($type==1) {
            $user1 = $this->Poker_model->GameUserCard($game->id, $slide->user_id);
            $user2 = $this->Poker_model->GameUserCard($game->id, $slide->prev_id);
            $remain_game_users[] = $user1;
            $remain_game_users[] = $user2;

            $user1 = $this->Poker_model->CardValue($remain_game_users[0]->card1, $remain_game_users[0]->card2);
            $user2 = $this->Poker_model->CardValue($remain_game_users[1]->card1, $remain_game_users[1]->card2);

            $winner = $this->Poker_model->getWinnerPosition($user1, $user2);

            if ($winner==2) {
                $looser_id = $remain_game_users[0]->user_id;
            } else {
                $looser = ($winner==1) ? 0 : 1;
                $looser_id = $remain_game_users[$looser]->user_id;
            }

            $this->Poker_model->PackGame($looser_id, $game->id);
        }

        $this->Poker_model->UpdateSlideShow($slide_id, $type);

        $lastChal = $this->Poker_model->LastChaal($game->id);

        $seen = $lastChal->seen;
        $amount = $lastChal->amount;

        $card_seen = $this->Poker_model->isCardSeen($game->id, $slide->user_id);

        if ($seen==0 && $card_seen==1) {
            $amount = $amount*2;
        }

        if ($seen==1 && $card_seen==0) {
            $amount = $amount/2;
        }

        $this->Poker_model->Chaal($game->id, $amount, $slide->user_id);

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

        if (!$user[0]->poker_table_id) {
            $data['message'] = 'You Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if (!$prev_user[0]->poker_table_id) {
            $data['message'] = 'Previous Player Are Not On Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->poker_table_id!=$prev_user[0]->poker_table_id) {
            $data['message'] = 'Players Are Not Same Table';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

        if (!$game) {
            $data['message'] = 'Game Not Started';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $amount = $this->Poker_model->LastChaalAmount($game->id);
        if ($user[0]->wallet<$amount) {
            $data['message'] = 'Insufficient Coins';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Poker_model->GameLog($game->id, 1);

        $game_users = $this->Poker_model->GameAllUser($game->id);

        $remain_game_users = $this->Poker_model->GameUser($game->id);
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
            $slide_id = $this->Poker_model->SlideShow($game->id, $this->data['user_id'], $this->data['prev_user_id']);
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

        // if(!$user[0]->poker_table_id)
        // {
        //     $data['message'] = 'You Are Not On Table';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $game = $this->Poker_model->getActiveGameOnTable($user[0]->poker_table_id);

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

            $this->Poker_model->Chat($chat_data);
        }

        $chat_list = $this->Poker_model->ChatList($game->id);
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

        if (!$user[0]->poker_table_id) {
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

        if ($this->Users_model->TipAdmin($this->data['tip'], $this->data['user_id'], $user[0]->poker_table_id, $this->data['gift_id'], $this->data['to_user_id'])) {
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
        $poker_table_id = $this->input->post('poker_table_id');
        $user_id = $this->input->post('user_id');

        if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $table = $this->Poker_model->isTableAvail($poker_table_id);

        if (!$table) {
            $data['message'] = 'Invalid Table';
            $data['code'] = HTTP_BLANK;
            $this->response($data, 200);
            exit();
        }

        if (!empty($poker_table_id)) {
            $table_data = $this->Poker_model->TableUser($poker_table_id);
            // $data['table_users'] = $table_data;

            $table_new_data = array();
            for ($i=0; $i < 5; $i++) {
                $table_new_data[$i]['id'] = 0;
                $table_new_data[$i]['poker_table_id'] = 0;
                $table_new_data[$i]['user_id'] = 0;
                $table_new_data[$i]['seat_position'] = $i+1;
                $table_new_data[$i]['role'] = 0;
                $table_new_data[$i]['added_date'] = 0;
                $table_new_data[$i]['updated_date'] = 0;
                $table_new_data[$i]['isDeleted'] = 0;
                $table_new_data[$i]['user_type'] = 0;
                $table_new_data[$i]['name'] = 0;
                $table_new_data[$i]['mobile'] = 0;
                $table_new_data[$i]['profile_pic'] = 0;
                $table_new_data[$i]['wallet'] = 0;
            }

            foreach ($table_data as $t => $u) {
                $table_new_data[$u->seat_position-1] = $u;
            }

            $data['table_users'] = $table_new_data;

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
            $data['table_detail'] = $table;
            $data['active_game_id'] = 0;
            $data['game_status'] = 0;
            $data['table_amount'] = 50;
            $active_game = $this->Poker_model->getActiveGameOnTable($poker_table_id);
            if ($active_game) {
                $data['active_game_id'] = $active_game->id;
                $data['game_status'] = 1;
            }
        }

        $game_id = $this->input->post('game_id');
        if (empty($game_id)) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Poker_model->View($game_id);
        if (empty($game)) {
            $data['message'] = 'Invalid Game';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_log = $this->Poker_model->GameLog($game_id, 1);

        $game_users = $this->Poker_model->GameAllUser($game_id);

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

        $data['game_log'] = $game_log;
        $data['all_users'] = $table_data;
        if ($game_log[0]->action==3) {
            $data['game_users'] = $this->Poker_model->GameAllUser($game_id);
        } else {
            $data['game_users'] = $this->Poker_model->GameOnlyUser($game_id);
        }
        $data['chaal'] = $chaal;
        $data['game_amount'] = $game->amount;
        $chaalCount = $this->Poker_model->ChaalCount($game->id, $chaal);
        // if ($chaalCount>3) {
        //     $this->Poker_model->getMyCards($game->id, $chaal);
        // }

        if (!empty($user_id)) {
            // $user_card_seen = $this->Poker_model->isCardSeen($game->id, $user_id);
            // $data['cards'] = array();
            // if ($user_card_seen==1) {
            $data['cards'] = $this->Poker_model->getMyCards($game->id, $user_id);
            // }
            $data['middle_card'] = $this->Poker_model->getTableCards($game->id);
        }

        if ($game) {
            $active_game_users = $data['game_users'];
            $lastChal = $this->Poker_model->LastChaal($game->id);
            $amount = $lastChal->amount;

            $max_amount = 0;
            foreach ($active_game_users as $key => $value) {
                $amount = $this->Poker_model->GameTotalAmount($game->id, $value->user_id);
                $max_amount = ($max_amount>$amount) ? $max_amount : $amount;
            }
            $user_amount = $this->Poker_model->GameTotalAmount($game->id, $chaal);
            // echo $max_amount;
            $diff_amount = $max_amount - $user_amount;

            $lastChal_amount = $this->Poker_model->LastChaalAmount($game->id);
            $data['check'] = ($diff_amount==0) ? 1 : 0;
            // $data['table_amount'] = ($diff_amount==0) ? $lastChal_amount : $diff_amount;
            $data['table_amount'] = $diff_amount;
            $data['round'] = $lastChal->round;
            $data['slide_show'] = $this->Poker_model->GetSlideShow($game->id);
        }
        $data['game_gifts'] = $this->Users_model->GiftList($poker_table_id);
        $data['message'] = 'Success';
        if ($game->winner_id>0) {
            $chaal = 0;
            $data['chaal'] = $chaal;
            $data['message'] = 'Game Completed';
            $data['game_status'] = 2;
            $data['winner_user_id'] = $game->winner_id;
        }
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
    }
}