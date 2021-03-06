<?php

namespace Meetingroom\Model\User;

class UserModel extends \Meetingroom\Model\AbstractCRUDModel
{
    protected $table = 'users';
    
    protected $fields = ['id', 'name', 'phone', 'position', 'nickname'];
    
    public function getIdByUsername($username)
    {
        $result = $this->db->query("SELECT id FROM users WHERE nickname = ? LIMIT 1", [$username]);
        return $result->numRows() === 0 ? false : (int) $result->fetch()->id;
    }
}
