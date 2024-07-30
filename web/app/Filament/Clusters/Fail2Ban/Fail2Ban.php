<?php

namespace App\Filament\Clusters\Fail2Ban;

use Filament\Clusters\Cluster;

class Fail2Ban extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Fail2Ban';

    protected static ?string $slug = 'fail2ban';

    protected static ?string $title = 'Fail2Ban';

    protected static ?int $navigationSort = 10;
}
