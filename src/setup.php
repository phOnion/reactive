<?php

use Onion\Framework\Reactive\Scheduler;

\Rx\Scheduler::setDefaultFactory([Scheduler::class, 'factory']);
