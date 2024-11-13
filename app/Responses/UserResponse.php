<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class UserResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "User created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Users get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "User updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "User deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "User get successfully";
    }

}