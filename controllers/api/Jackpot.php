<?php

use phpDocumentor\Reflection\Types\Object_;
use Restserver\Libraries\REST_Controller;

include APPPATH . '/libraries/REST_Controller.php';
include APPPATH . '/libraries/Format.php';
class Jackpot extends REST_Controller
{
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
            'Jackpot_model',
            'Setting_model',
            'Users_model'
        ]);
    }

    public function room_post()
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

        $room_data = $this->Jackpot_model->getRoom();
        if ($room_data) {
            $rooms = array();

            foreach ($room_data as $key => $value) {
                $rooms[$key]['id'] = $value->id;
                $rooms[$key]['min_coin'] = $value->min_coin;
                $rooms[$key]['max_coin'] = $value->max_coin;
                $rooms[$key]['added_date'] = $value->added_date;
                $rooms[$key]['updated_date'] = $value->updated_date;
                $rooms[$key]['isDeleted'] = $value->isDeleted;
                $rooms[$key]['online'] = $this->Jackpot_model->getRoomOnline($value->id);
            }

            $data['message'] = 'Success';
            $data['room_data'] = $rooms;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Room Starting Soon';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function get_active_game_post()
    {
        $total_bet_high_card = $this->input->post('total_bet_high_card');
        $total_bet_pair = $this->input->post('total_bet_pair');
        $total_bet_color = $this->input->post('total_bet_color');
        $total_bet_sequence = $this->input->post('total_bet_sequence');
        $total_bet_pure_sequence = $this->input->post('total_bet_pure_sequence');

        if (empty($this->data['user_id']) || empty($this->data['token']) || empty($this->data['room_id'])) {
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

        $room = $this->Jackpot_model->getRoom($this->data['room_id'], $this->data['user_id']);
        if (empty($room)) {
            $data['message'] = 'Invalid Room';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $bot_user = $this->Users_model->AllBotUserList();
        $data['bot_user'] = $bot_user;
        $game_data = $this->Jackpot_model->getActiveGameOnTable($this->data['room_id']);
        if ($game_data) {
            $game_cards = array();
            if ($game_data[0]->status) {
                $game_cards = $this->Jackpot_model->GetGameCards($game_data[0]->id);
            }

            $new_game_data[0]['id'] = $game_data[0]->id;
            $new_game_data[0]['room_id'] = $game_data[0]->room_id;
            // $new_game_data[0]['main_card'] = $game_data[0]->main_card;
            $new_game_data[0]['winning'] = $game_data[0]->winning;
            $new_game_data[0]['status'] = $game_data[0]->status;
            $new_game_data[0]['added_date'] = $game_data[0]->added_date;
            $added_datetime_sec = strtotime($game_data[0]->added_date);
            $new_game_data[0]['time_remaining'] = ($added_datetime_sec+DRAGON_TIME_FOR_BET) - time();
            $new_game_data[0]['end_datetime'] = $game_data[0]->end_datetime;
            $new_game_data[0]['updated_date'] = $game_data[0]->updated_date;

            $data['message'] = 'Success';
            $data['game_data'] = $new_game_data;
            $data['game_cards'] = $game_cards;
            $data['online'] = $this->Jackpot_model->getRoomOnline($this->data['room_id']);
            $data['online_users'] = $this->Jackpot_model->getRoomOnlineUser($this->data['room_id']);
            $HighCardAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, HIGH_CARD);
            $PairAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, PAIR);
            $ColorAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, COLOR);
            $SequenceAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, SEQUENCE);
            $PureSequenceAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, PURE_SEQUENCE);
            $SetAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, SET);

            $data['high_card_amount'] = rand($total_bet_high_card, $total_bet_high_card+10000)+$HighCardAmount;
            $data['pair_amount'] = rand($total_bet_pair, $total_bet_pair+10000)+$PairAmount;
            $data['color_amount'] = rand($total_bet_color, $total_bet_color+10000)+$ColorAmount;
            $data['sequence_amount'] = rand($total_bet_sequence, $total_bet_sequence+10000)+$SequenceAmount;
            $data['pure_sequence_amount'] = rand($total_bet_pure_sequence, $total_bet_pure_sequence+10000)+$PureSequenceAmount;
            $data['set_amount'] = ($SetAmount) ? $SetAmount : 0;

            $data['last_bet'] = $this->Jackpot_model->ViewBet('', $game_data[0]->id, '', '', 1);

            $data['jackpot_amount'] = $this->Setting_model->Setting()->jackpot_coin;

            $data['last_winning'] = $this->Jackpot_model->LastWinningBet($this->data['room_id'], 15);

            $winners = $this->Jackpot_model->getJackpotWinners(1);
            if ($winners) {
                foreach ($winners as $key => $value) {
                    $value->user_data = $this->Jackpot_model->getJackpotBigWinners($value->id);
                }
            }
            $data['big_winner'] = $winners;

            $data['profile'] = $user;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Game Starting Soon';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function get_active_game_socket_post()
    {
        $total_bet_high_card = $this->input->post('total_bet_high_card');
        $total_bet_pair = $this->input->post('total_bet_pair');
        $total_bet_color = $this->input->post('total_bet_color');
        $total_bet_sequence = $this->input->post('total_bet_sequence');
        $total_bet_pure_sequence = $this->input->post('total_bet_pure_sequence');

        // if (empty($this->data['user_id']) || empty($this->data['token']) || empty($this->data['room_id'])) {
        //     $data['message'] = 'Invalid Parameter';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
        //     $data['message'] = 'Invalid User';
        //     $data['code'] = HTTP_INVALID;
        //     $this->response($data, HTTP_OK);
        //     exit();
        // }

        // $user = $this->Users_model->UserProfile($this->data['user_id']);
        // if (empty($user)) {
        //     $data['message'] = 'Invalid User';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        // $room = $this->Jackpot_model->getRoom($this->data['room_id'], $this->data['user_id']);
        // if (empty($room)) {
        //     $data['message'] = 'Invalid Room';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        $bot_user = $this->Users_model->AllBotUserList();
        $data['bot_user'] = $bot_user;
        $game_data = $this->Jackpot_model->getActiveGameOnTable($this->data['room_id']);
        if ($game_data) {
            $game_cards = array();
            if ($game_data[0]->status) {
                $game_cards = $this->Jackpot_model->GetGameCards($game_data[0]->id);
            }

            $new_game_data[0]['id'] = $game_data[0]->id;
            $new_game_data[0]['room_id'] = $game_data[0]->room_id;
            // $new_game_data[0]['main_card'] = $game_data[0]->main_card;
            $new_game_data[0]['winning'] = $game_data[0]->winning;
            $new_game_data[0]['status'] = $game_data[0]->status;
            $new_game_data[0]['added_date'] = $game_data[0]->added_date;
            $added_datetime_sec = strtotime($game_data[0]->added_date);
            $new_game_data[0]['time_remaining'] = ($added_datetime_sec+DRAGON_TIME_FOR_BET) - time();
            $new_game_data[0]['end_datetime'] = $game_data[0]->end_datetime;
            $new_game_data[0]['updated_date'] = $game_data[0]->updated_date;

            $data['message'] = 'Success';
            $data['game_data'] = $new_game_data;
            $data['game_cards'] = $game_cards;
            $data['online'] = $this->Jackpot_model->getRoomOnline($this->data['room_id']);
            $data['online_users'] = $this->Jackpot_model->getRoomOnlineUser($this->data['room_id']);
            $HighCardAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, HIGH_CARD);
            $PairAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, PAIR);
            $ColorAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, COLOR);
            $SequenceAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, SEQUENCE);
            $PureSequenceAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, PURE_SEQUENCE);
            $SetAmount = $this->Jackpot_model->TotalBetAmount($game_data[0]->id, SET);

            $data['high_card_amount'] = rand($total_bet_high_card, $total_bet_high_card+10000)+$HighCardAmount;
            $data['pair_amount'] = rand($total_bet_pair, $total_bet_pair+10000)+$PairAmount;
            $data['color_amount'] = rand($total_bet_color, $total_bet_color+10000)+$ColorAmount;
            $data['sequence_amount'] = rand($total_bet_sequence, $total_bet_sequence+10000)+$SequenceAmount;
            $data['pure_sequence_amount'] = rand($total_bet_pure_sequence, $total_bet_pure_sequence+10000)+$PureSequenceAmount;
            $data['set_amount'] = ($SetAmount) ? $SetAmount : 0;

            $data['last_bet'] = $this->Jackpot_model->ViewBet('', $game_data[0]->id, '', '', 1);

            $data['jackpot_amount'] = $this->Setting_model->Setting()->jackpot_coin;

            $data['last_winning'] = $this->Jackpot_model->LastWinningBet($this->data['room_id'], 15);

            $winners = $this->Jackpot_model->getJackpotWinners(1);
            if ($winners) {
                foreach ($winners as $key => $value) {
                    $value->user_data = $this->Jackpot_model->getJackpotBigWinners($value->id);
                }
            }
            $data['big_winner'] = $winners;

            // $data['profile'] = $user;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Game Starting Soon';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function leave_room_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['token'])) {
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

        $room = $this->Jackpot_model->getRoom($this->data['room_id'], $this->data['user_id']);
        if (empty($room)) {
            $data['message'] = 'Invalid Room';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $leave_room = $this->Jackpot_model->leave_room($this->data['user_id']);
        if ($leave_room) {
            $data['message'] = 'Success';
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Something wents wrong';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function place_bet_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['game_id']) || ($this->data['bet']=="") || empty($this->data['amount'])) {
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

        // if (!in_array($this->data['bet'], array(DRAGON,TIGER,TIE))) {
        //     $data['message'] = 'Invalid Bet';
        //     $data['code'] = HTTP_INVALID;
        //     $this->response($data, HTTP_OK);
        //     exit();
        // }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<100) {
            $data['message'] = 'Required Minimum 100 Coins to Play';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<$this->data['amount']) {
            $data['message'] = 'Insufficient Wallet Amount';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Jackpot_model->View($this->data['game_id']);
        if (!$game) {
            $data['message'] = 'Invalid Game Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($game->status) {
            $data['message'] = 'Can\'t Place Bet, Game Has Been Ended';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $bet = $this->Jackpot_model->ViewBet($this->data['user_id'], $this->data['game_id'], $this->data['bet']);

        // if ($bet) {
        //     $data['message'] = 'One Bet Already Placed';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }

        $bet_data = [
            'jackpot_id' => $this->data['game_id'],
            'user_id' => $this->data['user_id'],
            'bet' => $this->data['bet'],
            'amount' => $this->data['amount'],
            'added_date' => date('Y-m-d H:i:s')

        ];

        $bet_id = $this->Jackpot_model->PlaceBet($bet_data);

        if ($bet_id) {
            if ($this->data['bet']==SET) {
                $amount = '+'.$this->data['amount'];
                $this->Setting_model->update_jackpot_amount($amount);
            }
            $this->Jackpot_model->MinusWallet($this->data['user_id'], $this->data['amount']);
            $data['message'] = 'Success';
            $data['bet_id'] = $bet_id;
            $user_wallet = $this->Users_model->UserProfile($this->data['user_id']);
            $data['wallet'] = $user_wallet[0]->wallet;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Something Wents Wrong';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function repeat_bet_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['game_id'])) {
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

        $game = $this->Jackpot_model->View($this->data['game_id']);
        if (!$game) {
            $data['message'] = 'Invalid Game Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($game->status) {
            $data['message'] = 'Can\'t Place Bet, Game Has Been Ended';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $bet = $this->Jackpot_model->ViewBet($this->data['user_id'], $this->data['game_id']);
        if ($bet) {
            $data['message'] = 'One Bet Already Placed';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $last_bet = $this->Jackpot_model->ViewBet($this->data['user_id']);
        if ($user[0]->wallet<$last_bet[0]->amount) {
            $data['message'] = 'Insufficient Wallet Amount';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // $bet_data = [
        //     'dragon_tiger_id' => $this->data['game_id'],
        //     'user_id' => $this->data['user_id'],
        //     'bet' => $last_bet[0]->bet,
        //     'amount' => $last_bet[0]->amount,
        //     'added_date' => date('Y-m-d H:i:s')

        // ];

        // $bet_id = $this->Jackpot_model->PlaceBet($bet_data);

        // if($bet_id)
        // {
        // $this->Jackpot_model->MinusWallet($this->data['user_id'], $last_bet[0]->amount);
        $data['message'] = 'Success';
        // $data['bet_id'] = $bet_id;
        $data['bet'] = $last_bet[0]->bet;
        $data['amount'] = $last_bet[0]->amount;
        $user_wallet = $this->Users_model->UserProfile($this->data['user_id']);
        $data['wallet'] = $user_wallet[0]->wallet;
        $data['code'] = HTTP_OK;
        $this->response($data, HTTP_OK);
        exit();
        // }
        // else
        // {
        //     $data['message'] = 'Something Wents Wrong';
        //     $data['code'] = HTTP_NOT_ACCEPTABLE;
        //     $this->response($data, 200);
        //     exit();
        // }
    }

    public function double_bet_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['game_id'])) {
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

        $game = $this->Jackpot_model->View($this->data['game_id']);
        if (!$game) {
            $data['message'] = 'Invalid Game Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($game->status) {
            $data['message'] = 'Can\'t Place Bet, Game Has Been Ended';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $bet = $this->Jackpot_model->ViewBet($this->data['user_id'], $this->data['game_id']);
        if ($bet) {
            $data['message'] = 'One Bet Already Placed';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $last_bet = $this->Jackpot_model->ViewBet($this->data['user_id']);
        $amount = $last_bet[0]->amount*2;
        if ($user[0]->wallet<$amount) {
            $data['message'] = 'Insufficient Wallet Amount';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $bet_data = [
            'jackpot_id' => $this->data['game_id'],
            'user_id' => $this->data['user_id'],
            'bet' => $last_bet[0]->bet,
            'amount' => $amount,
            'added_date' => date('Y-m-d H:i:s')
        ];

        $bet_id = $this->Jackpot_model->PlaceBet($bet_data);

        if ($bet_id) {
            $this->Jackpot_model->MinusWallet($this->data['user_id'], $amount);
            $data['message'] = 'Success';
            $data['bet_id'] = $bet_id;
            $data['bet'] = $last_bet[0]->bet;
            $data['amount'] = $amount;
            $user_wallet = $this->Users_model->UserProfile($this->data['user_id']);
            $data['wallet'] = $user_wallet[0]->wallet;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Something Wents Wrong';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function cancel_bet_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['game_id'])) {
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

        $game = $this->Jackpot_model->View($this->data['game_id']);
        if (!$game) {
            $data['message'] = 'Invalid Game Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $bet = $this->Jackpot_model->ViewBet($this->data['user_id'], $this->data['game_id'], '', '');
        if (!$bet) {
            $data['message'] = 'Invalid Bet';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($game->status) {
            $data['message'] = 'Can\'t Cancel Bet, Game Has Been Ended';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($this->Jackpot_model->DeleteBet($bet[0]->id, $this->data['user_id'], $this->data['game_id'])) {
            $this->Jackpot_model->AddWallet($this->data['user_id'], $bet[0]->amount);
            $data['message'] = 'Bet Cancel Successfully';
            $user_wallet = $this->Users_model->UserProfile($this->data['user_id']);
            $data['wallet'] = $user_wallet[0]->wallet;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Something Wents Wrong';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function jackpot_winners_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['token'])) {
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

        $winners = $this->Jackpot_model->getJackpotWinners();
        if ($winners) {
            foreach ($winners as $key => $value) {
                $value->user_data = $this->Jackpot_model->getJackpotBigWinners($value->id);
            }
            $data['winners'] = $winners;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'No Jackpot Till Now';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }

    public function last_winners_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['token'])) {
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

        $winners = $this->Jackpot_model->LastWinningBet($this->data['room_id'], 50);
        if ($winners) {
            $data['winners'] = $winners;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'No Jackpot Till Now';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }
}