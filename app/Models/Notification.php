<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_chat_id', 'message_id', 'sending_type', 'with_keyboard','created_at', 'updated_at'
    ];
}
