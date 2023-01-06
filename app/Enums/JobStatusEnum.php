<?php
namespace App\Enums;

enum JobStatusEnum:string {
    case Pending = 'inactive';
    case Active = 'active';
    case Completed = 'completed';
    case Canceled = 'cancelled';
}
