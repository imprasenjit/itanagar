<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentModel extends Model
{
    // ── Pages ─────────────────────────────────────────────────────────────────

    public function page_detail(string $type)
    {
        return $this->db->table('tbl_pages')->where('type', $type)->get()->getRow();
    }

    public function page_list()
    {
        return $this->db->table('tbl_pages')->get()->getResult();
    }

    // ── FAQ ────────────────────────────────────────────────────────────────────

    public function faq(int $limit = 0)
    {
        $builder = $this->db->table('tbl_faqs')->orderBy('id', 'DESC');
        if ($limit > 0) {
            $builder->limit($limit);
        }
        return $builder->get()->getResult();
    }

    public function get_allfaq(string $searchText = '', string $limit = '')
    {
        $builder = $this->db->table('tbl_faqs')->select('tbl_faqs.*')->orderBy('id', 'DESC');
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('tbl_faqs.question', $searchText)
                ->orLike('tbl_faqs.answer', $searchText)
                ->groupEnd();
        }
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function getfaq(int $id)
    {
        return $this->db->table('tbl_faqs')->where('id', $id)->get()->getRow();
    }

    // ── Email ──────────────────────────────────────────────────────────────────

    public function email_found(string $type)
    {
        return $this->db->table('tbl_emails')->where('type', $type)->get()->getRow();
    }

    // ── Contact ────────────────────────────────────────────────────────────────

    public function count_contact(): int
    {
        return $this->db->table('tbl_contact')->get()->getNumRows();
    }

    public function contact_ls(int $limit, int $offset)
    {
        return $this->db->table('tbl_contact')
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function faq_count(string $search = ''): int
    {
        $builder = $this->db->table('tbl_faqs');
        if (!empty($search)) {
            $builder->groupStart()->like('question', $search)->orLike('answer', $search)->groupEnd();
        }
        return $builder->countAllResults();
    }

    public function faq_list(string $search, int $limit, int $offset)
    {
        $builder = $this->db->table('tbl_faqs')->orderBy('id', 'DESC');
        if (!empty($search)) {
            $builder->groupStart()->like('question', $search)->orLike('answer', $search)->groupEnd();
        }
        return $builder->limit($limit, $offset)->get()->getResult();
    }

    public function contact_count(string $search = ''): int
    {
        $builder = $this->db->table('tbl_contact');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('message', $search)
                ->groupEnd();
        }
        return $builder->countAllResults();
    }

    public function contact_list(string $search, int $limit, int $offset)
    {
        $builder = $this->db->table('tbl_contact')->orderBy('id', 'DESC');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('message', $search)
                ->groupEnd();
        }
        return $builder->limit($limit, $offset)->get()->getResult();
    }

    // ── Generic utilities (used for content-domain tables) ────────────────────

    public function insert_date(string $table, array $data): int
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function editWeb_all(string $table, array $data, int $id): bool
    {
        $this->db->table($table)->where('id', $id)->update($data);
        return true;
    }

    public function deleteWeb(string $table, int $id): int
    {
        $this->db->table($table)->where('id', $id)->delete();
        return $this->db->affectedRows();
    }

    public function getallWebInfo(string $table, int $id)
    {
        return $this->db->table($table)->where('id', $id)->get()->getRow();
    }
}
