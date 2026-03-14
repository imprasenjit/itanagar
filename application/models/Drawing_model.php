<?php
class Drawing_model extends CI_Model {

	public $website_id;
    public $whiteball1;
    public $whiteball2;
    public $whiteball3;
    public $whiteball4;
    public $whiteball5;
    public $megaball;
    public $megaball1;
    public $latest_date;
    public $latest_price;
    public $latest_cash_value;
    public $next_date;
    public $next_price;
    public $next_cash_value;
    public $update_date;

    public function get_drawing_data($condition)
    {
        $query = $this->db->get_where('drawing', $condition);
        return $query->result();
    }

    public function insert_entry($website,$data)
    {
        $this->website_id    = $website;
        $this->whiteball1  = $data['whiteball1'];
        $this->whiteball2  = $data['whiteball2'];
        $this->whiteball3  = $data['whiteball3'];
        $this->whiteball4  = $data['whiteball4'];
        $this->whiteball5  = $data['whiteball5'];
        $this->megaball  = $data['megaball'];
        $this->megaball1  = $data['megaball1'];
        $this->latest_date  = $data['latest_date'];
        $this->latest_price  = NULL;
        $this->latest_cash_value  = $data['latest_cash_value'];
        $this->next_date  = $data['next_date'];
        $this->next_price  = $data['next_price'];
        $this->next_cash_value  = $data['next_cash_value'];
        $this->db->insert('drawing', $this);
    }

    public function update_entry($website,$data,$id)
    {
        $this->website_id    = $website;
        $this->whiteball1  = $data['whiteball1'];
        $this->whiteball2  = $data['whiteball2'];
        $this->whiteball3  = $data['whiteball3'];
        $this->whiteball4  = $data['whiteball4'];
        $this->whiteball5  = $data['whiteball5'];
        $this->megaball  = $data['megaball'];
        $this->megaball1  = $data['megaball1'];
        $this->latest_date  = $data['latest_date'];
        $this->latest_price  = $data['latest_price'];
        $this->latest_cash_value  = $data['latest_cash_value'];
        $this->next_date  = $data['next_date'];
        $this->next_price  = $data['next_price'];
        $this->next_cash_value  = $data['next_cash_value'];
        $this->update_date     = date('Y-m-d H:i:s');
        $this->db->update('drawing', $this, array('id' => $id));
    }

}