<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Planning = 'planning';
    case Reading = 'reading';
    case Completed = 'completed';
    case Expired = 'expired';
}
