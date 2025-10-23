<?php

use App\Jobs\CleanOldInventoryJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new CleanOldInventoryJob)->dailyAt('00:00');
