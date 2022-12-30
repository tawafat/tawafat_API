<?php
namespace App\Enums;

enum JobStatusEnum:string {
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Canceled = 'canceled';
}
