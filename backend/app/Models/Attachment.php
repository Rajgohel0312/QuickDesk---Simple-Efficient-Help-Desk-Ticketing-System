<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['ticket_id', 'comment_id', 'user_id', 'file_path', 'original_name'];

}
