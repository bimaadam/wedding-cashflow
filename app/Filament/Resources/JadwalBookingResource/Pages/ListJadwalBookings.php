<?php

namespace App\Filament\Resources\JadwalBookingResource\Pages;

use App\Filament\Resources\JadwalBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJadwalBookings extends ListRecords
{
    protected static string $resource = JadwalBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
