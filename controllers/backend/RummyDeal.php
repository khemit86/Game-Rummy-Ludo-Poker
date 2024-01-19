<?php

class RummyDeal extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['RummyDeal_model','Users_model']);
    }

    public function index()
    {
        $AllGames = $this->RummyDeal_model->AllGames();

        $data = [
            'title' => 'Deal Rummy History',
            'AllGames' => $AllGames
        ];
        template('rummy_deal/index', $data);
    }
}