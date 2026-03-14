<?php
class Winner_history_model extends CI_Model {

	public $website_id;
    public $unique_id;
    public $whiteball;
    public $megaball;
    public $is_jackpot;
    public $price_amount;
    public $latest_date;
    public $price_date;
    public $update_date;

    public function get_winner_history_data($condition)
    {
        $query = $this->db->get_where('winner_history', $condition);
        return $query->result();
    }

    public function insert_entry($website,$data)
    {
        $this->website_id    = $website;
        $this->unique_id  = $data['unique_id'];
        $this->whiteball  = $data['whiteball'];
        $this->megaball  = $data['megaball'];
        $this->is_jackpot  = $data['is_jackpot'];
        $this->price_amount  = $data['price_amount'];
        $this->latest_date  = $data['latest_date'];
        $this->db->insert('winner_history', $this);
    }

    public function update_entry($website,$data,$id)
    {
        $this->website_id    = $website;
        $this->unique_id  = $data['unique_id'];
        $this->whiteball  = $data['whiteball'];
        $this->megaball  = $data['megaball'];
        $this->is_jackpot  = $data['is_jackpot'];
        $this->price_amount  = $data['price_amount'];
        $this->latest_date  = $data['latest_date'];
        $this->update_date     = date('Y-m-d H:i:s');
        $this->db->update('winner_history', $this, array('id' => $id));
    }

}