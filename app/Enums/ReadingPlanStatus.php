<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Planning = 'planning';
    case Completed = 'completed';
    case Expired = 'expired';
}
