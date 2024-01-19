 <?php

class RummyPool extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['RummyPool_model','Users_model']);
    }

    public function index()
    {
        $AllGames = $this->RummyPool_model->AllGames();

        $data = [
            'title' => 'Pool Rummy History',
            'AllGames' => $AllGames
        ];
        template('rummy_pool/index', $data);
    }
} 