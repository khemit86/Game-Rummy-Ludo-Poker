<?php
class PokerMaster extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['PokerMaster_model']);
    }

    public function index()
    {
        $data = [
            'title' => 'Poker Table Master Management',
            'AllTableMaster' => $this->PokerMaster_model->AllTableMasterList()
        ];
        $data['SideBarbutton'] = ['backend/PokerMaster/add', 'Add Poker Table Master'];
        template('poker_master/index', $data);
    }

    public function add()
    {
        $data = [
            'title' => 'Add Poker Table Master'
        ];

        template('poker_master/add', $data);
    }

    public function edit($id)
    {
        $data = [
            'title' => 'Edit Poker Table Master',
            'PokerMaster' => $this->PokerMaster_model->ViewTableMaster($id)
        ];

        template('poker_master/edit', $data);
    }

    public function delete($id)
    {
        if ($this->PokerMaster_model->Delete($id)) {
            $this->session->set_flashdata('msg', array('message' => 'Poker Table Master Removed Successfully', 'class' => 'success', 'position' => 'top-right'));
        } else {
            $this->session->set_flashdata('msg', array('message' => 'Somthing Went Wrong', 'class' => 'error', 'position' => 'top-right'));
        }
        redirect('backend/PokerMaster');
    }

    public function insert()
    {
        $data = [
            'boot_value' => $this->input->post('boot_value'),
            'maximum_blind' => 4,
            'added_date' => date('Y-m-d H:i:s')
        ];
        $PokerMaster = $this->PokerMaster_model->AddTableMaster($data);
        if ($PokerMaster) {
            $this->session->set_flashdata('msg', array('message' => 'Poker Table Master Added Successfully', 'class' => 'success', 'position' => 'top-right'));
        } else {
            $this->session->set_flashdata('msg', array('message' => 'Somthing Went Wrong', 'class' => 'error', 'position' => 'top-right'));
        }
        redirect('backend/PokerMaster');
    }

    public function update()
    {
        $data = [
            'boot_value' => $this->input->post('boot_value'),
            'maximum_blind' => 4,    
            'updated_date' => date('Y-m-d H:i:s')
        ];
        $PokerMaster = $this->PokerMaster_model->UpdateTableMaster($data, $this->input->post('id'));
        if ($PokerMaster) {
            $this->session->set_flashdata('msg', array('message' => 'Poker Table Master Wallet Updated Successfully', 'class' => 'success', 'position' => 'top-right'));
        } else {
            $this->session->set_flashdata('msg', array('message' => 'Somthing Went Wrong', 'class' => 'error', 'position' => 'top-right'));
        }
        redirect('backend/PokerMaster');
    }

}